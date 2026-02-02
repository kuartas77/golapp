<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;
use App\Traits\ErrorTrait;
use App\Repositories\InscriptionRepository;
use App\Models\User;
use App\Models\School;
use App\Mail\PreinscriptionsProvitional;

class VerifyInscriptionStatus extends Command
{
    use ErrorTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inscription:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send notification to administrators about inscriptions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private InscriptionRepository $inscriptionRepository)
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
        $schools = School::with(['settingsValues'])->where('is_enable', true)->get();

        foreach ($schools as $school) {

            $inscriptions = $this->inscriptionRepository->getPreinscriptionsOrProvicionalGroup($school->id)->get();

            if($inscriptions->isNotEmpty()) {
                $users = User::query()->where('school_id', $school->id)->role('school')->get();

                if($users->isNotEmpty()) {
                    Mail::to($users)->send((new PreinscriptionsProvitional($school->name, $inscriptions)));
                }
            }
        }

        return 1;
    }
}
