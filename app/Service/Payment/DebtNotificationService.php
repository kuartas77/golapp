<?php

declare(strict_types=1);

namespace App\Service\Payment;

use App\Models\Inscription;
use App\Models\Payment;
use App\Models\Player;
use App\Models\School;
use App\Notifications\PaymentNotification;
use DateInterval;
use DateTimeInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class DebtNotificationService
{
    public function hasValidRecipient(Payment $payment): bool
    {
        return $this->hasValidEmail($this->playerFor($payment)->email);
    }

    public function datatableRows(Request $request): JsonResponse
    {
        $school = getSchool($request->user());
        $month = (string) $request->input('month');
        $query = $this->debtorsQuery($request, (int) $school->id, $month);

        return datatables()->of($query)
            ->editColumn('payment_id', fn ($row) => (int) $row->payment_id)
            ->editColumn('status', fn ($row) => (int) $row->status)
            ->editColumn('amount', fn ($row) => (int) $row->amount)
            ->addColumn('status_label', fn ($row) => $this->statusLabel((int) $row->status))
            ->addColumn('can_notify', fn ($row) => $this->hasValidEmail($row->email))
            ->removeColumn('email')
            ->toJson();
    }

    public function send(School $school, string $month, array $paymentIds): array
    {
        $normalizedIds = collect($paymentIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $payments = Payment::query()
            ->with(['inscription.player'])
            ->where('school_id', $school->id)
            ->where('year', now()->year)
            ->whereIn($month, $this->debtStatuses())
            ->whereIn('id', $normalizedIds)
            ->whereHas('inscription.player')
            ->get();

        $this->validateSelectedPayments($payments, $normalizedIds);

        $queuedCount = $payments->filter(
            fn (Payment $payment) => $this->notifyOnceDaily($payment, $school)
        )->count();

        return [
            'queued_count' => $queuedCount,
            'skipped_count' => $payments->count() - $queuedCount,
        ];
    }

    public function notifyOnceDaily(
        Payment $payment,
        School $school,
        DateTimeInterface|DateInterval|int|null $delay = null,
    ): bool {
        $cacheKey = $this->dailyCacheKey((int) $school->id, (int) $payment->id);
        $expiresAt = now()->addDay()->startOfDay();

        if (! Cache::add($cacheKey, true, $expiresAt)) {
            return false;
        }

        try {
            $notification = new PaymentNotification($payment, $school);

            if ($delay !== null) {
                $notification->delay($delay);
            }

            $this->playerFor($payment)->notify($notification);
        } catch (Throwable $throwable) {
            Cache::forget($cacheKey);

            throw $throwable;
        }

        return true;
    }

    private function debtorsQuery(Request $request, int $schoolId, string $month): Builder
    {
        $amountField = Payment::amountFieldFor($month);
        $search = trim((string) $request->input('search', ''));
        $category = trim((string) $request->input('category', ''));
        $trainingGroupId = (int) $request->input('training_group_id', 0);

        $query = DB::table('payments as p')
            ->join('inscriptions as i', 'i.id', '=', 'p.inscription_id')
            ->join('players as pl', 'pl.id', '=', 'i.player_id')
            ->leftJoin('training_groups as tg', 'tg.id', '=', 'p.training_group_id')
            ->where('p.school_id', $schoolId)
            ->where('p.year', now()->year)
            ->whereNull('p.deleted_at')
            ->whereNull('pl.deleted_at')
            ->whereIn("p.{$month}", $this->debtStatuses())
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $playerQuery) use ($search) {
                    $playerQuery
                        ->where('pl.names', 'like', "%{$search}%")
                        ->orWhere('pl.last_names', 'like', "%{$search}%")
                        ->orWhereRaw("CONCAT_WS(' ', pl.names, pl.last_names) LIKE ?", ["%{$search}%"])
                        ->orWhere('p.unique_code', 'like', "%{$search}%");
                });
            })
            ->when($category !== '', fn (Builder $query) => $query->where('i.category', $category))
            ->when($trainingGroupId !== 0, fn (Builder $query) => $query->where('p.training_group_id', $trainingGroupId));

        $sentPaymentIds = $this->sentTodayPaymentIds(
            (clone $query)->pluck('p.id'),
            $schoolId,
        );

        return $query
            ->when(
                $sentPaymentIds->isNotEmpty(),
                fn (Builder $query) => $query->whereNotIn('p.id', $sentPaymentIds),
            )
            ->selectRaw(
                "p.id as payment_id,
                p.unique_code,
                TRIM(CONCAT_WS(' ', pl.names, pl.last_names)) as player_name,
                i.category,
                tg.name as training_group,
                p.{$month} as status,
                p.{$amountField} as amount,
                pl.email"
            );
    }

    private function sentTodayPaymentIds(Collection $paymentIds, int $schoolId): Collection
    {
        $cacheKeys = $paymentIds->mapWithKeys(fn ($paymentId) => [
            (int) $paymentId => $this->dailyCacheKey($schoolId, (int) $paymentId),
        ]);

        if ($cacheKeys->isEmpty()) {
            return collect();
        }

        $cachedValues = Cache::many($cacheKeys->values()->all());

        return $cacheKeys
            ->filter(fn (string $cacheKey) => (bool) ($cachedValues[$cacheKey] ?? false))
            ->keys()
            ->values();
    }

    private function validateSelectedPayments(Collection $payments, Collection $selectedIds): void
    {
        if ($payments->count() !== $selectedIds->count()) {
            throw ValidationException::withMessages([
                'payment_ids' => 'La selección cambió o contiene deportistas que no pertenecen a la escuela. Actualiza el listado e inténtalo nuevamente.',
            ]);
        }

        if ($payments->contains(fn (Payment $payment) => ! $this->hasValidRecipient($payment))) {
            throw ValidationException::withMessages([
                'payment_ids' => 'Uno o más deportistas seleccionados no tienen un correo válido. Actualiza el listado e inténtalo nuevamente.',
            ]);
        }
    }

    private function debtStatuses(): array
    {
        return [Payment::$debt, Payment::$paid_];
    }

    private function hasValidEmail(?string $email): bool
    {
        return $email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function statusLabel(int $status): string
    {
        return (string) config("variables.KEY_PAYMENTS_SELECT.{$status}", $status);
    }

    private function dailyCacheKey(int $schoolId, int $paymentId): string
    {
        return "debt-notification.sent.{$schoolId}.{$paymentId}.".now()->toDateString();
    }

    private function playerFor(Payment $payment): Player
    {
        /** @var Inscription $inscription */
        $inscription = $payment->inscription;
        /** @var Player $player */
        $player = $inscription->player;

        return $player;
    }
}
