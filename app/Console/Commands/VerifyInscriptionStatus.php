<?php

namespace App\Console\Commands;

use App\Mail\PreinscriptionsProvitional;
use App\Models\School;
use App\Models\User;
use App\Repositories\InscriptionRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class VerifyInscriptionStatus extends Command
{
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
        $schools = School::with(['settingsValues', 'trainingGroups'])->where('is_enable', true)->get();
        /** @var \App\Models\School $school */
        foreach ($schools as $school) {
            try {
                $trainingGroupId = $school->trainingGroups->sortBy('id')->first()?->id;

                if (is_null($trainingGroupId)) {
                    continue;
                }

                $inscriptions = $this->inscriptionRepository->getPreinscriptionsOrProvicionalGroup(
                    schoolId: $school->id, trainingGroupId: $trainingGroupId
                )->get();

                if ($inscriptions->isEmpty()) {
                    continue;
                }

                foreach ($this->schoolAdminEmails($school) as $email) {
                    try {
                        Mail::to($email)->send(new PreinscriptionsProvitional($school->name, $inscriptions));
                    } catch (Throwable $exception) {
                        logger()->warning('No se pudo enviar el correo de preinscripciones.', [
                            'school_id' => $school->id,
                            'email' => $email,
                            'error' => $exception->getMessage(),
                        ]);
                    }
                }
            } catch (Throwable $exception) {
                report($exception);
                logger()->warning('No se pudo procesar el reporte de preinscripciones.', [
                    'school_id' => $school->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }

    private function schoolAdminEmails(School $school): array
    {
        return User::query()
            ->where('school_id', $school->id)
            ->role('school')
            ->pluck('email')
            ->map(fn ($email) => trim((string) $email))
            ->filter(fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }
}
