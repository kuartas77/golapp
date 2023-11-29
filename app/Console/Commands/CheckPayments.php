<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\School;
use App\Notifications\PaymentNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CheckPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check payments up to current month.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): Int
    {
        $months = $this->getAndCheckMonths(collect(config('variables.KEY_INDEX_MONTHS')));

        $schools = School::with(['settingsValues'])->where('is_enable', true)->get();

        $now = now();

        foreach ($schools as $school) {
            $day = data_get($school, 'settings.NOTIFY_PAYMENT_DAY', 15);

            if ($now->month == 2 && $day > 28) {
                $day = $now->lastOfMonth()->lastOfMonth()->day;
            }

            if ($now->day != $day) {
                continue;
            }

            $query = $this->makePaymentsQuery($months, $school->id);

            $count = $query->count();

            if ($count == 0) {
                continue;
            }

            $chunkCount = $count >= 100 ? 5 : 10;

            $query->chunkById($chunkCount, function ($payments) use ($school, $now) {
                $iteration = 1;
                foreach ($payments as $payment) {
                    $delaySeconds = $iteration * 10;
                    $player = $payment->inscription->player;
                    if ($player->email && filter_var($player->email, FILTER_VALIDATE_EMAIL)) {
                        $player->notify(
                            (new PaymentNotification($payment, $school))->delay($now->addMinute()->addSeconds($delaySeconds))->onQueue('emails')
                        );
                    }
                    $iteration++;
                }
            }, 'id');
        }

        return 1;
    }

    private function getAndCheckMonths(Collection $months): Collection
    {
        return $months->filter(fn($_, $key) => $key <= now()->month);
    }

    private function getDebts(): array
    {
        return [
            // 0, //=> "Pendiente"
            2, //=> "Debe"
            3 //=> "Abonó"
        ];
    }

    private function makePaymentsQuery(Collection $months, int $school_id): Builder
    {
        $paymentsQuery = Payment::query()->with(['inscription.player'])
            ->whereHas('inscription', fn($query) => $query->where('year', now()->year))
            ->where('year', now()->year)
            ->where('school_id', $school_id);

        $paymentsQuery->where(function ($q) use ($months) {
            foreach ($months as $month) {
                $q->orWhereIn($month, $this->getDebts());
            }
        });

        return $paymentsQuery;
    }
}
