<?php

namespace App\Service\SchoolDataExport;

use App\Models\School;
use App\Models\SchoolDataExport;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

class SchoolDataExportGenerator
{
    private const DISK_EXPORT = 'export';
    private const DISK_LOCAL = 'local';
    private const DISK_PUBLIC = 'public';

    private const SECRET_COLUMNS = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'current_access_token',
    ];

    public function generate(SchoolDataExport $schoolDataExport): void
    {
        $schoolDataExport->loadMissing(['school', 'requester']);
        $school = $schoolDataExport->school;

        $schoolDataExport->forceFill([
            'status' => SchoolDataExport::STATUS_PROCESSING,
            'error_message' => null,
        ])->save();

        $temporaryDirectory = "tmp/school-data-exports/{$schoolDataExport->id}";
        Storage::disk(self::DISK_LOCAL)->deleteDirectory($temporaryDirectory);
        Storage::disk(self::DISK_LOCAL)->makeDirectory($temporaryDirectory);

        try {
            $context = $this->context($school);
            $manifest = $this->initialManifest($schoolDataExport, $context);
            $zipPath = $this->zipPath($schoolDataExport, $school);
            $absoluteZipPath = Storage::disk(self::DISK_EXPORT)->path($zipPath);

            Storage::disk(self::DISK_EXPORT)->makeDirectory(dirname($zipPath));

            $zip = new ZipArchive();
            if ($zip->open($absoluteZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('No fue posible crear el archivo ZIP de exportacion.');
            }

            $zip->addFromString('README.txt', $this->readme($school));

            foreach ($this->domains($context) as $domain) {
                foreach ($domain['sheets'] as $sheet) {
                    $relativeCsvPath = "{$temporaryDirectory}/{$domain['directory']}/{$sheet['key']}.csv";
                    $rowCount = $this->writeCsv($sheet['table'], $sheet['scope'], $relativeCsvPath);

                    $zip->addFile(
                        Storage::disk(self::DISK_LOCAL)->path($relativeCsvPath),
                        "datos/{$domain['directory']}/{$sheet['key']}.csv"
                    );

                    $manifest['tables'][$sheet['key']] = $rowCount;
                }
            }

            foreach ($this->referencedFiles($context) as $file) {
                $this->addReferencedFile($zip, $file, $manifest);
            }

            $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $zip->close();

            Storage::disk(self::DISK_LOCAL)->deleteDirectory($temporaryDirectory);

            $schoolDataExport->forceFill([
                'status' => SchoolDataExport::STATUS_READY,
                'disk' => self::DISK_EXPORT,
                'path' => $zipPath,
                'filename' => basename($zipPath),
                'size_bytes' => Storage::disk(self::DISK_EXPORT)->size($zipPath),
                'manifest' => $manifest,
                'completed_at' => now(),
                'expires_at' => now()->addDays(7),
            ])->save();
        } catch (Throwable $exception) {
            Storage::disk(self::DISK_LOCAL)->deleteDirectory($temporaryDirectory);

            $schoolDataExport->forceFill([
                'status' => SchoolDataExport::STATUS_FAILED,
                'error_message' => Str::limit($exception->getMessage(), 1000, ''),
            ])->save();

            report($exception);
        }
    }

    private function context(School $school): array
    {
        $schoolId = (int) $school->id;
        $playerIds = $this->ids('players', fn ($query) => $query->where('school_id', $schoolId));
        $inscriptionIds = $this->ids('inscriptions', fn ($query) => $query->where('school_id', $schoolId));
        $trainingGroupIds = $this->ids('training_groups', fn ($query) => $query->where('school_id', $schoolId));
        $competitionGroupIds = $this->ids('competition_groups', fn ($query) => $query->where('school_id', $schoolId));
        $tournamentIds = $this->ids('tournaments', fn ($query) => $query->where('school_id', $schoolId));
        $gameIds = $this->ids('games', function ($query) use ($competitionGroupIds, $tournamentIds): void {
            $query->whereIn('competition_group_id', $competitionGroupIds ?: [0])
                ->orWhereIn('tournament_id', $tournamentIds ?: [0]);
        });
        $invoiceIds = $this->ids('invoices', fn ($query) => $query->where('school_id', $schoolId));
        $paymentRequestIds = $this->ids('payment_request', fn ($query) => $query->where('school_id', $schoolId));
        $topicNotificationIds = $this->ids('topic_notifications', fn ($query) => $query->where('school_id', $schoolId));
        $peopleIds = $this->ids('peoples_players', fn ($query) => $query->whereIn('player_id', $playerIds ?: [0]), 'people_id');
        $evaluationTemplateIds = $this->ids('evaluation_templates', fn ($query) => $query->where('school_id', $schoolId));
        $playerEvaluationIds = $this->ids('player_evaluations', fn ($query) => $query->where('school_id', $schoolId));
        $outingIds = $this->ids('school_outings', fn ($query) => $query->where('school_id', $schoolId));
        $outingParticipantIds = $this->ids('school_outing_participants', fn ($query) => $query->where('school_id', $schoolId));
        $outingActivityIds = $this->ids('school_outing_activities', fn ($query) => $query->where('school_id', $schoolId));
        $userIds = collect()
            ->merge($this->ids('users', fn ($query) => $query->where('school_id', $schoolId)))
            ->merge($this->ids('schools_user', fn ($query) => $query->where('school_id', $schoolId), 'user_id'))
            ->unique()
            ->values()
            ->all();

        return compact(
            'school',
            'schoolId',
            'playerIds',
            'inscriptionIds',
            'trainingGroupIds',
            'competitionGroupIds',
            'tournamentIds',
            'gameIds',
            'invoiceIds',
            'paymentRequestIds',
            'topicNotificationIds',
            'peopleIds',
            'evaluationTemplateIds',
            'playerEvaluationIds',
            'outingIds',
            'outingParticipantIds',
            'outingActivityIds',
            'userIds'
        );
    }

    private function domains(array $context): array
    {
        $schoolId = $context['schoolId'];

        return [
            [
                'directory' => '01_escuela',
                'sheets' => [
                    $this->sheet('schools', 'escuela', 'schools', fn ($query) => $query->where('id', $schoolId)),
                    $this->sheet('setting_values', 'configuracion', 'setting_values', fn ($query) => $query->where('school_id', $schoolId)),
                ],
            ],
            [
                'directory' => '02_usuarios',
                'sheets' => [
                    $this->sheet('users', 'usuarios', 'users', fn ($query) => $query->whereIn('id', $context['userIds'] ?: [0])),
                    $this->sheet('profiles', 'perfiles', 'profiles', fn ($query) => $query->whereIn('user_id', $context['userIds'] ?: [0])),
                    $this->sheet('schools_user', 'escuelas_usuario', 'schools_user', fn ($query) => $query->where('school_id', $schoolId)),
                ],
            ],
            [
                'directory' => '03_deportistas_acudientes',
                'sheets' => [
                    $this->sheet('players', 'deportistas', 'players', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('peoples', 'acudientes', 'peoples', fn ($query) => $query->whereIn('id', $context['peopleIds'] ?: [0])),
                    $this->sheet('peoples_players', 'acudiente_deportista', 'peoples_players', fn ($query) => $query->whereIn('player_id', $context['playerIds'] ?: [0])),
                ],
            ],
            [
                'directory' => '04_inscripciones',
                'sheets' => [
                    $this->sheet('inscriptions', 'inscripciones', 'inscriptions', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('inscription_custom_charges', 'cargos_personalizados', 'inscription_custom_charges', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('competition_group_inscription', 'competencia_inscripcion', 'competition_group_inscription', fn ($query) => $query->whereIn('inscription_id', $context['inscriptionIds'] ?: [0])),
                ],
            ],
            [
                'directory' => '05_grupos_torneos',
                'sheets' => [
                    $this->sheet('training_groups', 'grupos_entrenamiento', 'training_groups', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('competition_groups', 'grupos_competencia', 'competition_groups', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('tournaments', 'torneos', 'tournaments', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('schedules', 'horarios', 'schedules', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('training_group_user', 'instructores_grupo', 'training_group_user', fn ($query) => $query->whereIn('training_group_id', $context['trainingGroupIds'] ?: [0])),
                ],
            ],
            [
                'directory' => '06_asistencias_sesiones',
                'sheets' => [
                    $this->sheet('assists', 'asistencias', 'assists', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('training_sessions', 'sesiones', 'training_sessions', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('training_session_details', 'detalle_sesiones', 'training_session_details', fn ($query) => $query->whereIn('training_session_id', $this->ids('training_sessions', fn ($q) => $q->where('school_id', $schoolId)) ?: [0])),
                    $this->sheet('methodology_records', 'metodologia', 'methodology_records', fn ($query) => $query->where('school_id', $schoolId)),
                ],
            ],
            [
                'directory' => '07_partidos_rendimiento',
                'sheets' => [
                    $this->sheet('games', 'partidos', 'games', fn ($query) => $query->whereIn('id', $context['gameIds'] ?: [0])),
                    $this->sheet('skills_control', 'rendimiento', 'skills_control', fn ($query) => $query->where(fn ($subQuery) => $subQuery
                        ->whereIn('inscription_id', $context['inscriptionIds'] ?: [0])
                        ->orWhereIn('game_id', $context['gameIds'] ?: [0]))),
                ],
            ],
            [
                'directory' => '08_facturacion_pagos',
                'sheets' => [
                    $this->sheet('payments', 'pagos_mensuales', 'payments', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('invoices', 'facturas', 'invoices', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('invoice_items', 'items_factura', 'invoice_items', fn ($query) => $query->whereIn('invoice_id', $context['invoiceIds'] ?: [0])),
                    $this->sheet('payments_received', 'pagos_recibidos', 'payments_received', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('payment_request', 'solicitudes_pago', 'payment_request', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('invoice_custom_items', 'items_personalizados', 'invoice_custom_items', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('tournament_payouts', 'pagos_torneos', 'tournament_payouts', fn ($query) => $query->where('school_id', $schoolId)),
                ],
            ],
            [
                'directory' => '09_evaluaciones',
                'sheets' => [
                    $this->sheet('evaluation_periods', 'periodos', 'evaluation_periods', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('evaluation_templates', 'plantillas', 'evaluation_templates', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('evaluation_template_criteria', 'criterios', 'evaluation_template_criteria', fn ($query) => $query->whereIn('evaluation_template_id', $context['evaluationTemplateIds'] ?: [0])),
                    $this->sheet('player_evaluations', 'evaluaciones', 'player_evaluations', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('player_evaluation_scores', 'puntajes', 'player_evaluation_scores', fn ($query) => $query->whereIn('player_evaluation_id', $context['playerEvaluationIds'] ?: [0])),
                ],
            ],
            [
                'directory' => '10_notificaciones',
                'sheets' => [
                    $this->sheet('topic_notifications', 'notificaciones', 'topic_notifications', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('player_topic_notification', 'notificaciones_deportistas', 'player_topic_notification', fn ($query) => $query->where(fn ($subQuery) => $subQuery
                        ->where('school_id', $schoolId)
                        ->orWhereIn('topic_notification_id', $context['topicNotificationIds'] ?: [0]))),
                    $this->sheet('uniform_request', 'solicitudes_uniforme', 'uniform_request', fn ($query) => $query->where('school_id', $schoolId)),
                ],
            ],
            [
                'directory' => '11_inventario',
                'sheets' => [
                    $this->sheet('inventory_products', 'productos', 'inventory_products', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('inventory_movements', 'movimientos', 'inventory_movements', fn ($query) => $query->where('school_id', $schoolId)),
                ],
            ],
            [
                'directory' => '12_salidas',
                'sheets' => [
                    $this->sheet('school_outings', 'salidas', 'school_outings', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('school_outing_participants', 'participantes', 'school_outing_participants', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('school_outing_activities', 'actividades', 'school_outing_activities', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('school_outing_contributions', 'aportes', 'school_outing_contributions', fn ($query) => $query->where('school_id', $schoolId)),
                ],
            ],
            [
                'directory' => '13_contratos',
                'sheets' => [
                    $this->sheet('contracts', 'contratos', 'contracts', fn ($query) => $query->where('school_id', $schoolId)),
                    $this->sheet('contract_types', 'tipos_contrato', 'contract_types', fn ($query) => $query),
                ],
            ],
        ];
    }

    private function sheet(string $key, string $title, string $table, callable $scope): array
    {
        return [
            'key' => $key,
            'title' => $title,
            'table' => $table,
            'scope' => $scope,
        ];
    }

    private function writeCsv(string $table, callable $scope, string $relativeCsvPath): int
    {
        Storage::disk(self::DISK_LOCAL)->makeDirectory(dirname($relativeCsvPath));
        $absoluteCsvPath = Storage::disk(self::DISK_LOCAL)->path($relativeCsvPath);
        $handle = fopen($absoluteCsvPath, 'w');

        if ($handle === false) {
            throw new \RuntimeException("No fue posible crear el CSV temporal {$relativeCsvPath}.");
        }

        if (! Schema::hasTable($table)) {
            fputcsv($handle, ['sin_datos']);
            fclose($handle);

            return 0;
        }

        $columns = Schema::getColumnListing($table);
        fputcsv($handle, $columns);

        $query = DB::table($table)->orderBy($this->orderColumn($table));
        $scope($query);

        $count = 0;
        $query->chunk(1000, function ($rows) use ($handle, $columns, &$count): void {
            foreach ($rows as $row) {
                $sanitized = $this->sanitizeRow((array) $row);
                fputcsv($handle, collect($columns)->map(fn (string $column) => $sanitized[$column] ?? null)->all());
                $count++;
            }
        });

        fclose($handle);

        return $count;
    }

    private function rows(string $table, callable $scope): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $query = DB::table($table)->orderBy($this->orderColumn($table));
        $scope($query);

        return $query->get()
            ->map(fn ($row) => $this->sanitizeRow((array) $row))
            ->all();
    }

    private function ids(string $table, callable $scope, string $column = 'id'): array
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return [];
        }

        $query = DB::table($table)->select($column);
        $scope($query);

        return $query->pluck($column)->filter()->unique()->values()->all();
    }

    private function orderColumn(string $table): string
    {
        return Schema::hasColumn($table, 'id') ? 'id' : Arr::first(Schema::getColumnListing($table));
    }

    private function sanitizeRow(array $row): array
    {
        foreach (self::SECRET_COLUMNS as $column) {
            if (array_key_exists($column, $row)) {
                $row[$column] = '[excluido]';
            }
        }

        return $row;
    }

    private function referencedFiles(array $context): array
    {
        $schoolId = $context['schoolId'];
        $files = [];

        $logo = data_get($context, 'school.logo');
        if ($logo) {
            $files[] = ['path' => $logo, 'directory' => 'escuela', 'label' => 'logo'];
        }

        foreach ($this->rows('players', fn ($query) => $query->where('school_id', $schoolId)) as $player) {
            if (! empty($player['photo'])) {
                $files[] = ['path' => $player['photo'], 'directory' => 'deportistas', 'label' => (string) ($player['unique_code'] ?? $player['id'])];
            }
        }

        foreach ($this->rows('payment_request', fn ($query) => $query->where('school_id', $schoolId)) as $paymentRequest) {
            if (! empty($paymentRequest['image'])) {
                $files[] = ['path' => $paymentRequest['image'], 'directory' => 'soportes_pago', 'label' => (string) ($paymentRequest['id'] ?? 'soporte')];
            }
        }

        foreach ($this->rows('topic_notifications', fn ($query) => $query->where('school_id', $schoolId)) as $notification) {
            if (! empty($notification['image_url'])) {
                $files[] = ['path' => $notification['image_url'], 'directory' => 'notificaciones', 'label' => (string) ($notification['id'] ?? 'imagen')];
            }
        }

        return $files;
    }

    private function addReferencedFile(ZipArchive $zip, array $file, array &$manifest): void
    {
        $relativePath = $this->normalizePublicPath((string) $file['path']);

        if ($relativePath === '' || ! Storage::disk(self::DISK_PUBLIC)->exists($relativePath)) {
            $manifest['warnings'][] = [
                'type' => 'missing_file',
                'path' => (string) $file['path'],
            ];

            return;
        }

        $extension = pathinfo($relativePath, PATHINFO_EXTENSION);
        $safeLabel = Str::slug((string) $file['label']) ?: 'archivo';
        $safeName = $safeLabel . '_' . md5($relativePath) . ($extension ? ".{$extension}" : '');
        $archivePath = sprintf('archivos/%s/%s', $file['directory'], $safeName);

        $zip->addFile(Storage::disk(self::DISK_PUBLIC)->path($relativePath), $archivePath);

        $manifest['files'][] = [
            'source' => $relativePath,
            'archive_path' => $archivePath,
        ];
    }

    private function normalizePublicPath(string $path): string
    {
        $path = trim(parse_url($path, PHP_URL_PATH) ?: $path);
        $path = ltrim(str_replace('\\', '/', $path), '/');

        foreach (['storage/', 'img/dynamic/', 'api/img/dynamic/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return Str::after($path, $prefix);
            }
        }

        return $path;
    }

    private function zipPath(SchoolDataExport $schoolDataExport, School $school): string
    {
        $slug = $school->slug ?: Str::slug($school->name);
        $uuid = (string) Str::uuid();

        return "school-data-exports/{$slug}/golapp_export_{$slug}_{$schoolDataExport->id}_{$uuid}.zip";
    }

    private function initialManifest(SchoolDataExport $schoolDataExport, array $context): array
    {
        return [
            'generated_at' => now()->toIso8601String(),
            'expires_at' => now()->addDays(7)->toIso8601String(),
            'school' => [
                'id' => $context['school']->id,
                'slug' => $context['school']->slug,
                'name' => $context['school']->name,
            ],
            'requested_by' => [
                'id' => $schoolDataExport->requester?->id,
                'name' => $schoolDataExport->requester?->name,
                'email' => $schoolDataExport->requester?->email,
            ],
            'tables' => [],
            'files' => [],
            'warnings' => [],
            'omitted' => [
                'passwords',
                'remember_tokens',
                'personal_access_tokens',
                'sessions',
                'password_resets',
                'jobs',
                'cache',
                'logs',
            ],
        ];
    }

    private function readme(School $school): string
    {
        return implode(PHP_EOL, [
            'Exportacion masiva de datos de escuela - Golapp',
            '',
            "Escuela: {$school->name}",
            "Generado: " . now()->format('Y-m-d H:i:s'),
            '',
            'Estructura:',
            '- datos/: archivos CSV por tabla, agrupados por dominio funcional.',
            '- archivos/: binarios almacenados y referenciados por la escuela.',
            '- manifest.json: conteos, archivos incluidos, omisiones y advertencias.',
            '',
            'Este paquete contiene datos personales y debe compartirse solo por canales autorizados.',
            'No incluye credenciales, tokens, sesiones, jobs, cache ni logs operativos.',
        ]);
    }
}
