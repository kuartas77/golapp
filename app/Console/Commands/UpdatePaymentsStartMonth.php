<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\School;
use App\Service\PaymentAmountResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class UpdatePaymentsStartMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update payment to debt in current month.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private PaymentAmountResolver $paymentAmountResolver)
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
        $currentDate = now();

        if ($currentDate->isLastOfMonth()) {
            $targetDate = $currentDate->copy()->addDay();
            $targetYear = $targetDate->year;
            $month = $this->getMonth(
                collect(config('variables.KEY_INDEX_MONTHS')),
                $targetDate->month
            );

            School::query()
                ->with(['settingsValues'])
                ->where('is_enable', true)
                ->chunkById(10, function ($schools) use ($month, $targetYear): void {

                    foreach ($schools as $school) {
                        $amountColumn = "{$month}_amount";

                        Payment::query()
                            ->with(['inscription.school.settingsValues'])
                            ->whereHas(
                                'inscription',
                                fn($query) => $query
                                    ->where('year', $targetYear)
                                    ->where('school_id', $school->id)
                            )
                            ->where('year', $targetYear)
                            ->where('school_id', $school->id)
                            ->where($month, Payment::$pending)
                            ->chunkById(100, function ($payments) use ($month, $amountColumn): void {
                                foreach ($payments as $payment) {
                                    $payment->{$month} = Payment::$debt;

                                    if ((int) $payment->{$amountColumn} === 0) {
                                        $payment->{$amountColumn} = $this->paymentAmountResolver->monthlyAmountForPayment($payment);
                                    }

                                    $payment->save();
                                }
                            });
                    }
                });
        }

        return self::SUCCESS;
    }

    private function getMonth(Collection $months, int $month): string
    {
        return $months->first(fn($_, $key) => $key === $month);
    }
}
