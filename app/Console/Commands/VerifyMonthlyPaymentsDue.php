<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;
use App\Repositories\PaymentRepository;
use App\Models\User;
use App\Models\School;
use App\Mail\PreinscriptionsProvitional;
use App\Mail\DuePayments;

class VerifyMonthlyPaymentsDue extends Command
{
    protected $signature = 'payments:monthly';

    protected $description = 'Send notification to administrators about payments due on the last day of month';

    public function __construct(private PaymentRepository $paymentRepository)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $currentDate = now();

        if (! $currentDate->isLastOfMonth()) {
            return self::SUCCESS;
        }

        $year = (int) $currentDate->year;
        $monthNumber = (int) $currentDate->month;
        $monthLabel = config('variables.KEY_MONTHS_INDEX')[$monthNumber] ?? $currentDate->translatedFormat('F');
        $reportDate = $currentDate->format('d-m-Y');

        School::query()
            ->select(['id', 'name'])
            ->where('is_enable', true)
            ->chunkById(100, function ($schools) use ($year, $monthNumber, $monthLabel, $reportDate): void {
                foreach ($schools as $school) {
                    try {
                        $duePayments = $this->paymentRepository
                            ->queryPaymentsDueByMonth($school->id, $year, $monthNumber, true)
                            ->get();

                        if ($duePayments->isEmpty()) {
                            continue;
                        }

                        $emails = User::query()
                            ->where('school_id', $school->id)
                            ->role('school')
                            ->whereNotNull('email')
                            ->pluck('email')
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();

                        if (empty($emails)) {
                            continue;
                        }

                        Mail::to($emails)->queue(
                            new DuePayments(
                                $school->name,
                                $monthLabel,
                                $duePayments,
                                $reportDate
                            )
                        );

                        $this->info("Correo encolado para escuela #{$school->id} - {$school->name}");
                    } catch (\Throwable $e) {
                        report($e);
                        $this->error("Error procesando escuela #{$school->id}: {$e->getMessage()}");
                    }
                }
            });

        return self::SUCCESS;
    }
}
