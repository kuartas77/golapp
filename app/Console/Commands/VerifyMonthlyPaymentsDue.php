<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;
use App\Traits\ErrorTrait;
use App\Repositories\PaymentRepository;
use App\Models\User;
use App\Models\School;
use App\Mail\PreinscriptionsProvitional;
use App\Mail\DuePayments;

class VerifyMonthlyPaymentsDue extends Command
{
    use ErrorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send notification to administrators about payments due on the last day of month';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private PaymentRepository $paymentRepository)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $currentDate = now();

        if ($currentDate->isLastOfMonth()) {

            $schools = School::with(['settingsValues'])->where('is_enable', true)->get();

            $month = config('variables.KEY_MONTHS_INDEX')[$currentDate->month];
            foreach ($schools as $school) {

                $query = $this->paymentRepository->queryPaymentsDueByMonth($school->id, $currentDate->year, $currentDate->month);

                $duePayments = $query->get();

                if($duePayments->isNotEmpty()) {
                    $users = User::query()->where('school_id', $school->id)->role('school')->get();
                    if($users->isNotEmpty()) {
                        Mail::to($users)->send((new DuePayments($school->name, $month, $duePayments)));
                    }
                }
            }
        }

        return 1;
    }
}
