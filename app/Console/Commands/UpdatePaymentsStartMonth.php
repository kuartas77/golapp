<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\School;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
                        $monthlyPayment = (float) data_get($school, 'settings.MONTHLY_PAYMENT', 50000);
                        $amountColumn = "{$month}_amount";

                        Payment::query()
                            ->whereHas(
                                'inscription',
                                fn($query) => $query
                                    ->where('year', $targetYear)
                                    ->where('school_id', $school->id)
                            )
                            ->where('year', $targetYear)
                            ->where('school_id', $school->id)
                            ->where($month, Payment::$pending)
                            ->update([
                                $month => Payment::$debt,
                                $amountColumn => DB::raw("
                                    CASE
                                        WHEN {$amountColumn} = 0.00 THEN {$monthlyPayment}
                                        ELSE {$amountColumn}
                                    END
                                "),
                            ]);
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
