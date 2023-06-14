<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use App\Notifications\PaymentNotification;

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
    public function handle()
    {
        $months = $this->getAndCheckMonths(collect(config('variables.KEY_INDEX_MONTHS')));

        $schools = School::with(['settingsValues'])->where('is_enable', true)->get();

        foreach ($schools as $school) {

            $day = $school->settings['NOTIFY_PAYMENT_DAY'] ?? 15;
            if(now()->month == 2){
                $day = now()->lastOfMonth()->lastOfMonth()->day;
            }

            if(now()->day == $day){
                $this->makePaymentsQuery($months, $school->id)->with(['inscription.player'])
                    ->whereHas('inscription', fn ($query) => $query->where('year', now()->year))
                    ->chunkById(50, function ($payments) use($school){
                        foreach ($payments as $payment) {
                            if($payment->inscription->player->email && filter_var($payment->inscription->player->email, FILTER_VALIDATE_EMAIL)){
                                $payment->inscription->player->notify(new PaymentNotification($payment, $school));
                            }
                        }
                    }, 'inscription_id');
            }

        }

        return 0;
    }

    private function getAndCheckMonths(Collection $months): Collection
    {
        return $months->filter(function ($_, $key) {
            return $key <= now()->month;
        });
    }

    private function makePaymentsQuery(Collection $months, int $school_id): Builder
    {
        $debts = [
            // 0, //=> "Pendiente"
            2, //=> "Debe"
            3 //=> "Abonó"
        ];

        $paymentsQuery = Payment::query()->where('year', now()->year)->where('school_id', $school_id);

        $paymentsQuery->where(function ($q) use ($months, $debts) {
            foreach ($months as $month) {
                $q->orWhereIn($month, $debts);
            }
        });

        return $paymentsQuery;
    }
}
