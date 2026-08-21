<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Models\School;
use App\Service\Payment\DebtNotificationService;
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
     */
    public function handle(DebtNotificationService $debtNotifications): int
    {
        $months = $this->getAndCheckMonths(collect(config('variables.KEY_INDEX_MONTHS')));

        $now = now();

        School::query()
            ->where('is_enable', true)
            ->where('send_debt_notifications', true)
            ->where('id', '<>', 1)
            ->chunkById(10, function ($schools) use ($now, $months, $debtNotifications) {

                foreach ($schools as $school) {
                    $school->load(['settingsValues']);
                    $day = data_get($school, 'settings.NOTIFY_PAYMENT_DAY', 15);

                    if ($now->month == 2 && $day > 28) {
                        $day = $now->lastOfMonth()->day;
                    }

                    if ($now->day != $day || $now->month == 1) {
                        continue;
                    }

                    $query = $this->makePaymentsQuery($months, $school->id);

                    $count = $query->count();

                    if ($count == 0) {
                        continue;
                    }

                    $chunkCount = $count >= 100 ? 5 : 10;

                    $query->chunkById($chunkCount, function ($payments) use ($school, $now, $debtNotifications) {
                        $iteration = 1;
                        foreach ($payments as $payment) {
                            $delaySeconds = $iteration * 10;
                            if ($debtNotifications->hasValidRecipient($payment)) {
                                $debtNotifications->notifyOnceDaily(
                                    $payment,
                                    $school,
                                    $now->addMinute()->addSeconds($delaySeconds)
                                );
                            }
                            $iteration++;
                        }
                    }, 'id');
                }
            });

        return self::SUCCESS;
    }

    private function getAndCheckMonths(Collection $months): Collection
    {
        return $months->filter(fn ($_, $key) => $key <= now()->month);
    }

    private function getDebts(): array
    {
        return [
            // 0, //=> "Pendiente"
            2, // => "Debe"
            3, // => "Abonó"
        ];
    }

    /**
     * @return Builder<Payment>
     */
    private function makePaymentsQuery(Collection $months, int $school_id): Builder
    {
        $paymentsQuery = Payment::query()
            ->withWhereHas('inscription', fn ($query) => $query->with(['player'])->where('year', now()->year)->where('school_id', $school_id))
            ->where('year', now()->year)
            ->where('school_id', $school_id);

        $paymentsQuery->where(function ($q) use ($months) {
            foreach ($months as $month) {
                $q->orWhere($month, $this->getDebts());
            }
        });

        return $paymentsQuery;
    }
}
