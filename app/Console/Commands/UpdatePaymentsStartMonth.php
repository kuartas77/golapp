<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\School;
use App\Notifications\PaymentNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
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
            $currentDate->addDay();
            $month = $this->getMonth(collect(config('variables.KEY_INDEX_MONTHS')), $currentDate->month);

            School::query()->where('is_enable', true)->chunkById(10, function ($schools) use ($month): void {

                foreach ($schools as $school) {
                    $school->load(['settingsValues']);
                    $monthlyPayment = data_get($school, 'settings.MONTHLY_PAYMENT', 50000);

                    Payment::query()
                        ->withWhereHas('inscription', fn($query) => $query->with(['player'])->where('year', now()->year)->where('school_id', $school->id))
                        ->where('year', now()->year)
                        ->where('school_id', $school->id)
                        ->where($month, Payment::$pending)
                        ->update([
                            $month => Payment::$debt,
                            "{$month}_amount" => $monthlyPayment
                        ]);
                }
            });
        }

        return 1;
    }

    private function getMonth(Collection $months, int $month): string
    {
        return $months->first(fn($_, $key) => $key === $month);
    }
}
