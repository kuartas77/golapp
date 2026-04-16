<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\People;
use App\Service\Portal\GuardianInvitationService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class PortalGuardiansBackfill extends Command
{
    protected $signature = 'portal:guardians-backfill
        {--send : Envia invitaciones a los acudientes elegibles sin contraseña}
        {--dry-run : Muestra el diagnóstico sin enviar invitaciones}';

    protected $description = 'Diagnostica e invita acudientes elegibles para el portal del año actual.';

    public function handle(GuardianInvitationService $guardianInvitationService): int
    {
        $eligibleTutors = $this->eligibleTutors();
        $withEmail = $eligibleTutors->filter(fn (People $guardian) => checkEmail($guardian->email))->values();
        $duplicateEmailGroups = $withEmail
            ->groupBy(fn (People $guardian) => mb_strtolower(trim((string) $guardian->email)))
            ->filter(fn (Collection $group) => $group->count() > 1);

        $duplicateIds = $duplicateEmailGroups
            ->flatten(1)
            ->pluck('id')
            ->unique()
            ->all();

        $uniqueCandidates = $withEmail
            ->reject(fn (People $guardian) => in_array($guardian->id, $duplicateIds, true))
            ->values();

        $alreadyConfigured = $uniqueCandidates
            ->reject(fn (People $guardian) => blank($guardian->password))
            ->values();

        $invitable = $uniqueCandidates
            ->filter(fn (People $guardian) => blank($guardian->password))
            ->values();

        $shouldSend = (bool) $this->option('send');

        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Tutores elegibles', $eligibleTutors->count()],
                ['Con correo válido', $withEmail->count()],
                ['Excluidos por correo duplicado', count($duplicateIds)],
                ['Ya configurados', $alreadyConfigured->count()],
                ['Invitables', $invitable->count()],
            ]
        );

        if ($duplicateEmailGroups->isNotEmpty()) {
            $this->newLine();
            $this->warn('Conflictos por correo duplicado detectados:');
            $this->table(
                ['Correo', 'Registros'],
                $duplicateEmailGroups->map(
                    fn (Collection $group, string $email) => [
                        $email,
                        $group->map(fn (People $guardian) => "{$guardian->id}:{$guardian->identification_card}")->implode(', '),
                    ]
                )->values()->all()
            );
        }

        if (!$shouldSend) {
            $this->info('Diagnóstico completado. Usa --send para enviar invitaciones a los acudientes invitables.');
            return self::SUCCESS;
        }

        $sent = 0;
        $failed = 0;

        if ($invitable->isEmpty()) {
            $this->info('No hay acudientes pendientes por invitar.');
            return self::SUCCESS;
        }

        $this->withProgressBar($invitable, function (People $guardian) use ($guardianInvitationService, &$sent, &$failed) {
            $invited = $guardianInvitationService->invite($guardian);

            if ($invited) {
                $sent++;
                return;
            }

            $failed++;
        });

        $this->newLine(2);
        $this->table(
            ['Resultado', 'Cantidad'],
            [
                ['Invitaciones enviadas', $sent],
                ['Invitaciones omitidas/fallidas', $failed],
            ]
        );

        return self::SUCCESS;
    }

    private function eligibleTutors(): Collection
    {
        return People::query()
            ->select('peoples.*')
            ->where('tutor', true)
            ->whereHas('players.inscriptions', function ($query) {
                $query->where('year', now()->year)
                    ->whereHas('school', fn ($schoolQuery) => $schoolQuery
                        ->where('is_enable', true)
                        ->where('tutor_platform', true));
            })
            ->distinct()
            ->orderBy('names')
            ->get();
    }
}
