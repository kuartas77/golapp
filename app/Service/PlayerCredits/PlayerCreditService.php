<?php

declare(strict_types=1);

namespace App\Service\PlayerCredits;

use App\Models\Payment;
use App\Models\Player;
use App\Models\PlayerCreditMovement;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlayerCreditService
{
    public const INSUFFICIENT_BALANCE_MESSAGE = 'El deportista no cuenta con saldo a favor suficiente para realizar esta operación.';

    public function balanceForPlayer(int $schoolId, int $playerId): int
    {
        return (int) PlayerCreditMovement::query()
            ->where('school_id', $schoolId)
            ->where('player_id', $playerId)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END), 0) as balance")
            ->value('balance');
    }

    public function list(int $schoolId, array $filters = []): array
    {
        $search = trim((string) data_get($filters, 'search', ''));

        $players = Player::query()
            ->where('school_id', $schoolId)
            ->when($search === '', fn ($query) => $query->whereHas(
                'creditMovements',
                fn ($movementQuery) => $movementQuery->where('school_id', $schoolId)
            ))
            ->when($search !== '', function ($query) use ($search): void {
                $terms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

                foreach ($terms as $term) {
                    $query->where(function ($q) use ($term): void {
                        $q->where('names', 'like', "%{$term}%")
                            ->orWhere('last_names', 'like', "%{$term}%")
                            ->orWhere('unique_code', 'like', "%{$term}%");
                    });
                }
            })
            ->with(['inscription.trainingGroup'])
            ->withSum(['creditMovements as credit_total' => fn ($query) => $query
                ->where('school_id', $schoolId)
                ->where('type', PlayerCreditMovement::TYPE_CREDIT)], 'amount')
            ->withSum(['creditMovements as debit_total' => fn ($query) => $query
                ->where('school_id', $schoolId)
                ->where('type', PlayerCreditMovement::TYPE_DEBIT)], 'amount')
            ->orderBy('names')
            ->limit(200)
            ->get()
            ->map(fn (Player $player) => $this->serializePlayerBalance($player));

        return [
            'rows' => $players,
            'summary' => $this->summary($schoolId),
        ];
    }

    public function datatable(int $schoolId, array $params = []): array
    {
        $draw = (int) data_get($params, 'draw', 1);
        $start = max(0, (int) data_get($params, 'start', 0));
        $length = (int) data_get($params, 'length', 10);
        $length = $length > 0 ? min($length, 100) : 10;
        $search = trim((string) data_get($params, 'search.value', ''));
        $columns = data_get($params, 'columns', []);
        $order = data_get($params, 'order.0', []);
        $orderColumn = data_get($columns, data_get($order, 'column', 4).'.data', 'balance');
        $orderDirection = strtolower((string) data_get($order, 'dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $base = $this->playerBalanceQuery($schoolId, $search);
        $recordsTotal = (clone $this->playerBalanceQuery($schoolId, ''))->count();
        $recordsFiltered = (clone $base)->count();

        $rows = $this->applyDatatableOrder($base, (string) $orderColumn, $orderDirection)
            ->skip($start)
            ->take($length)
            ->get()
            ->map(fn (Player $player) => $this->serializePlayerBalance($player))
            ->values();

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ];
    }

    public function summary(int $schoolId): array
    {
        $balances = PlayerCreditMovement::query()
            ->where('school_id', $schoolId)
            ->select('player_id')
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END), 0) as balance")
            ->groupBy('player_id')
            ->get();

        return [
            'total_balance' => (int) $balances->sum('balance'),
            'players_with_balance' => $balances->where('balance', '>', 0)->count(),
        ];
    }

    public function detail(Player $player, int $schoolId): array
    {
        $this->authorizePlayer($player, $schoolId);

        return [
            'player' => $this->serializePlayerBalance($player),
            'movements' => $player->creditMovements()
                ->where('school_id', $schoolId)
                ->with('creator:id,name')
                ->latest('movement_date')
                ->latest('id')
                ->get(),
            'balance' => $this->balanceForPlayer($schoolId, (int) $player->id),
        ];
    }

    public function createManualMovement(Player $player, array $data, int $schoolId, int $userId): PlayerCreditMovement
    {
        $this->authorizePlayer($player, $schoolId);

        return DB::transaction(function () use ($player, $data, $schoolId, $userId): PlayerCreditMovement {
            if ($data['type'] === PlayerCreditMovement::TYPE_DEBIT) {
                $this->assertSufficientBalance($schoolId, (int) $player->id, (int) $data['amount']);
            }

            return PlayerCreditMovement::query()->create($data + [
                'school_id' => $schoolId,
                'player_id' => $player->id,
                'created_by' => $userId,
            ]);
        });
    }

    public function applyPaymentDebit(Payment $payment, string $field, int $amount, int $previousStatus, int $userId): PlayerCreditMovement
    {
        $player = $payment->inscription?->player;
        abort_unless($player, Response::HTTP_UNPROCESSABLE_ENTITY, 'No se encontró el deportista asociado a la mensualidad.');

        $this->assertSufficientBalance((int) $payment->school_id, (int) $player->id, $amount);

        return PlayerCreditMovement::query()->create([
            'school_id' => $payment->school_id,
            'player_id' => $player->id,
            'type' => PlayerCreditMovement::TYPE_DEBIT,
            'amount' => $amount,
            'movement_date' => now()->toDateString(),
            'concept' => $this->paymentConcept($payment, $field),
            'notes' => 'Descuento automático desde mensualidades.',
            'payment_id' => $payment->id,
            'payment_field' => $field,
            'previous_payment_status' => $previousStatus,
            'created_by' => $userId,
        ]);
    }

    public function compensatePaymentDebit(Payment $payment, string $field, int $userId): void
    {
        $movement = PlayerCreditMovement::query()
            ->where('school_id', $payment->school_id)
            ->where('payment_id', $payment->id)
            ->where('payment_field', $field)
            ->where('type', PlayerCreditMovement::TYPE_DEBIT)
            ->latest('id')
            ->first();

        if (! $movement) {
            return;
        }

        PlayerCreditMovement::query()->create([
            'school_id' => $movement->school_id,
            'player_id' => $movement->player_id,
            'type' => PlayerCreditMovement::TYPE_CREDIT,
            'amount' => $movement->amount,
            'movement_date' => now()->toDateString(),
            'concept' => 'Reversión de '.$movement->concept,
            'notes' => 'Compensación automática por cambio de estado en mensualidades.',
            'payment_id' => $payment->id,
            'payment_field' => $field,
            'previous_payment_status' => $movement->previous_payment_status,
            'created_by' => $userId,
        ]);
    }

    public function assertSufficientBalance(int $schoolId, int $playerId, int $amount): void
    {
        abort_if($this->balanceForPlayer($schoolId, $playerId) < $amount, Response::HTTP_UNPROCESSABLE_ENTITY, self::INSUFFICIENT_BALANCE_MESSAGE);
    }

    public function authorizePlayer(Player $player, int $schoolId): void
    {
        abort_unless((int) $player->school_id === $schoolId, Response::HTTP_NOT_FOUND);
    }

    private function serializePlayerBalance(Player $player): array
    {
        $credit = (int) ($player->credit_total ?? 0);
        $debit = (int) ($player->debit_total ?? 0);

        return [
            'id' => $player->id,
            'unique_code' => $player->unique_code,
            'full_names' => $player->full_names,
            'photo_url' => $player->photo_url,
            'category' => $player->category,
            'training_group' => $player->inscription?->trainingGroup?->full_group,
            'credit_total' => $credit,
            'debit_total' => $debit,
            'balance' => $credit - $debit,
        ];
    }

    private function playerBalanceQuery(int $schoolId, string $search)
    {
        return Player::query()
            ->where('school_id', $schoolId)
            ->when($search === '', fn ($query) => $query->whereHas(
                'creditMovements',
                fn ($movementQuery) => $movementQuery->where('school_id', $schoolId)
            ))
            ->when($search !== '', function ($query) use ($search): void {
                $terms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];

                foreach ($terms as $term) {
                    $query->where(function ($q) use ($term): void {
                        $q->where('names', 'like', "%{$term}%")
                            ->orWhere('last_names', 'like', "%{$term}%")
                            ->orWhere('unique_code', 'like', "%{$term}%");
                    });
                }
            })
            ->with(['inscription.trainingGroup'])
            ->withSum(['creditMovements as credit_total' => fn ($query) => $query
                ->where('school_id', $schoolId)
                ->where('type', PlayerCreditMovement::TYPE_CREDIT)], 'amount')
            ->withSum(['creditMovements as debit_total' => fn ($query) => $query
                ->where('school_id', $schoolId)
                ->where('type', PlayerCreditMovement::TYPE_DEBIT)], 'amount');
    }

    private function applyDatatableOrder($query, string $column, string $direction)
    {
        return match ($column) {
            'full_names' => $query->orderBy('names', $direction)->orderBy('last_names', $direction),
            'unique_code' => $query->orderBy('unique_code', $direction),
            'training_group' => $query->orderBy('names'),
            'credit_total' => $query->orderBy('credit_total', $direction),
            'debit_total' => $query->orderBy('debit_total', $direction),
            'balance' => $query->orderByRaw('(COALESCE(credit_total, 0) - COALESCE(debit_total, 0)) '.$direction),
            default => $query->orderByRaw('(COALESCE(credit_total, 0) - COALESCE(debit_total, 0)) DESC')->orderBy('names'),
        };
    }

    private function paymentConcept(Payment $payment, string $field): string
    {
        $label = $field === 'enrollment'
            ? 'Matrícula'
            : config("variables.KEY_INDEX_MONTHS_LABEL.{$field}", ucfirst($field));

        return sprintf('Pago %s %s', strtolower((string) $label), $payment->year);
    }
}
