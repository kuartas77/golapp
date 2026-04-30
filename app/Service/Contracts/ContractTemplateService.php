<?php

declare(strict_types=1);

namespace App\Service\Contracts;

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Player;
use App\Models\School;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ContractTemplateService
{
    public function supportedTypes(): array
    {
        return config('contract_templates.types', []);
    }

    public function supportedTypeCodes(): array
    {
        return array_keys($this->supportedTypes());
    }

    public function resolveType(string $code): array
    {
        $type = $this->supportedTypes()[$code] ?? null;

        abort_if($type === null, 404, 'El tipo de contrato no esta disponible.');

        $contractType = $this->resolveContractTypeModel($code);

        abort_if($contractType === null, 404, 'El tipo de contrato no existe en la configuracion actual.');

        return [
            'code' => $code,
            'contract_type_id' => $contractType->id,
            'contract_type' => $contractType,
            ...$type,
        ];
    }

    public function editorPayload(School $school): array
    {
        $contracts = $this->contractsForSchool($school);

        return [
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
                'slug' => $school->slug,
                'create_contract' => (bool) $school->create_contract,
                'sign_player' => (bool) $school->sign_player,
            ],
            'types' => collect($this->supportedTypeCodes())
                ->map(fn (string $code) => $this->editorTypePayload($school, $code, $contracts))
                ->values()
                ->all(),
        ];
    }

    public function editorTypePayload(School $school, string $code, ?EloquentCollection $contracts = null): array
    {
        $type = $this->resolveType($code);
        $contracts ??= $this->contractsForSchool($school);
        $contract = $contracts->firstWhere('contract_type_id', $type['contract_type_id']);
        $isConfigured = $this->isConfiguredContract($contract);

        return [
            'code' => $type['code'],
            'label' => $type['label'],
            'description' => $type['description'] ?? '',
            'configured' => $isConfigured,
            'preview_url' => $isConfigured
                ? route('admin.contracts.preview', ['contractTypeCode' => $type['code']])
                : null,
            'portal' => [
                'requires_acceptance' => (bool) data_get($type, 'portal.requires_acceptance', false),
                'requires_tutor_signature' => (bool) data_get($type, 'portal.requires_tutor_signature', false),
                'requires_player_signature' => (bool) data_get($type, 'portal.requires_player_signature', false),
                'acceptance_field' => $type['acceptance_field'] ?? null,
            ],
            'help' => [
                'placeholders' => $this->placeholderCatalog($type['code']),
            ],
            'template' => [
                'name' => $contract?->name ?? $type['label'],
                'header' => $contract?->header ?? '',
                'body' => $contract?->body ?? '',
                'footer' => $contract?->footer ?? '',
                'used_parameters' => $this->usedParametersForContract($contract),
                'updated_at' => $contract?->updated_at?->toISOString(),
            ],
        ];
    }

    public function upsertSchoolContract(School $school, string $code, array $attributes): Contract
    {
        $type = $this->resolveType($code);
        $usedParameters = $this->extractUsedParameters([
            $attributes['header'] ?? '',
            $attributes['body'] ?? '',
            $attributes['footer'] ?? '',
        ]);

        $contract = Contract::query()->firstOrNew([
            'school_id' => $school->id,
            'contract_type_id' => $type['contract_type_id'],
        ]);

        $contract->fill([
            'name' => $attributes['name'],
            'header' => $attributes['header'],
            'body' => $attributes['body'],
            'footer' => $attributes['footer'],
            'parameters' => implode(',', $usedParameters),
        ]);
        $contract->save();

        $contract->loadMissing('contract_type');

        return $contract;
    }

    public function availablePortalContracts(School $school): array
    {
        $contracts = $this->contractsForSchool($school);

        return collect($this->supportedTypeCodes())
            ->map(function (string $code) use ($school, $contracts) {
                $type = $this->resolveType($code);
                $contract = $contracts->firstWhere('contract_type_id', $type['contract_type_id']);

                if (!$this->isConfiguredContract($contract)) {
                    return null;
                }

                return [
                    'code' => $type['code'],
                    'label' => $type['label'],
                    'url' => route('portal.school.contract.show', [$school->slug, $type['code']]),
                    'acceptance_field' => $type['acceptance_field'] ?? null,
                    'requires_acceptance' => (bool) data_get($type, 'portal.requires_acceptance', false),
                    'requires_tutor_signature' => (bool) data_get($type, 'portal.requires_tutor_signature', false),
                    'requires_player_signature' => (bool) data_get($type, 'portal.requires_player_signature', false),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function renderForSchool(School $school, string $code, array $variables): ?array
    {
        $type = $this->resolveType($code);
        $contract = Contract::query()
            ->where('school_id', $school->id)
            ->firstWhere('contract_type_id', $type['contract_type_id']);

        if (!$this->isConfiguredContract($contract)) {
            return null;
        }

        [$header, $body, $footer] = $this->replaceParameters($contract, $variables);

        return [
            'type' => $type,
            'contract' => $contract,
            'school' => $school,
            'header' => $header,
            'body' => $body,
            'footer' => $footer,
        ];
    }

    public function buildPlayerVariables(School $school, Player $player, array $paths = []): array
    {
        $player->loadMissing('people');

        $variables = [
            'SCHOOL_LOGO' => $school->logo_local,
            'SCHOOL_NAME' => Str::upper($school->name),
            'SCHOOL_NAMES' => Str::upper($school->name),
            'SCHOOL_AGENT' => (string) $school->agent,
            'DAY' => now()->format('d'),
            'MONTH' => config('variables.KEY_MONTHS_INDEX')[now()->month] ?? now()->translatedFormat('F'),
            'YEAR' => now()->format('Y'),
            'DATE' => now()->format('d-m-Y'),
            'PLAYER_FULLNAMES' => Str::upper($player->full_names),
            'PLAYER_DOC' => (string) $player->identification_document,
            'PLAYER_DATE_BIRTH' => (string) $player->date_birth,
            'PLAYER_ADDRESS' => (string) $player->address,
            'PLAYER_EPS' => (string) $player->eps,
            'CATEGORY' => (string) $player->category,
            'SCHOOL_SIGN' => $this->schoolSignaturePath($school),
            'IMAGE_ONE' => $this->schoolAssetPath($school, 'img-contract-1.jpg'),
            'IMAGE_TWO' => $this->schoolAssetPath($school, 'img-contract-2.jpg'),
            'IMAGE_THREE' => $this->schoolAssetPath($school, 'img-contract-3.jpg'),
        ];

        $tutor = $player->people->firstWhere('tutor', true);

        $variables['TUTOR_NAME'] = data_get($tutor, 'names', '');
        $variables['TUTOR_DOC'] = data_get($tutor, 'identification_card', '');
        $variables['TUTOR_MAIL'] = data_get($tutor, 'email', '');
        $variables['TUTOR_PHONE'] = data_get($tutor, 'mobile', '');
        $variables['SIGN_TUTOR'] = isset($paths['sign_tutor']) ? $this->localPath($paths['sign_tutor']) : $this->fallbackSignaturePath($school);
        $variables['SIGN_PLAYER'] = isset($paths['sign_player']) ? $this->localPath($paths['sign_player']) : $this->fallbackSignaturePath($school);

        return $variables;
    }

    public function buildPreviewVariables(School $school): array
    {
        return [
            'SCHOOL_LOGO' => $school->logo_local,
            'SCHOOL_NAME' => Str::upper($school->name),
            'SCHOOL_NAMES' => Str::upper($school->name),
            'SCHOOL_AGENT' => (string) ($school->agent ?: 'REPRESENTANTE LEGAL'),
            'SCHOOL_SIGN' => $this->schoolSignaturePath($school),
            'IMAGE_ONE' => $this->schoolAssetPath($school, 'img-contract-1.jpg'),
            'IMAGE_TWO' => $this->schoolAssetPath($school, 'img-contract-2.jpg'),
            'IMAGE_THREE' => $this->schoolAssetPath($school, 'img-contract-3.jpg'),
            'DAY' => now()->format('d'),
            'MONTH' => config('variables.KEY_MONTHS_INDEX')[now()->month] ?? now()->translatedFormat('F'),
            'YEAR' => now()->format('Y'),
            'DATE' => now()->format('d-m-Y'),
            'SIGN_PLAYER' => $this->fallbackSignaturePath($school),
            'PLAYER_FULLNAMES' => 'DEPORTISTA DE PRUEBA',
            'PLAYER_DOC' => '1234567890',
            'PLAYER_DATE_BIRTH' => '2012-01-01',
            'PLAYER_ADDRESS' => 'Direccion de ejemplo',
            'PLAYER_EPS' => 'EPS DEMO',
            'CATEGORY' => '2012',
            'TUTOR_NAME' => 'ACUDIENTE DE PRUEBA',
            'TUTOR_DOC' => '1020304050',
            'SIGN_TUTOR' => $this->fallbackSignaturePath($school),
            'TUTOR_MAIL' => 'acudiente@example.com',
            'TUTOR_PHONE' => '3000000000',
        ];
    }

    public function placeholderCatalog(?string $code = null): array
    {
        return collect(config('contract_templates.placeholders', []))
            ->filter(function (array $placeholder, string $key) use ($code) {
                $types = $placeholder['types'] ?? null;

                if ($code === null || $types === null) {
                    return true;
                }

                return in_array($code, $types, true);
            })
            ->map(fn (array $placeholder, string $key) => [
                'key' => $key,
                'token' => sprintf('[%s]', $key),
                'label' => $placeholder['label'] ?? $key,
                'description' => $placeholder['description'] ?? '',
                'example' => $placeholder['example'] ?? sprintf('[%s]', $key),
            ])
            ->values()
            ->all();
    }

    public function pdfViewForCode(string $code): string
    {
        return $this->resolveType($code)['pdf_view'];
    }

    public function fileLabelForCode(string $code): string
    {
        return $this->resolveType($code)['file_label'];
    }

    public function pdfConfiguration(): array
    {
        return [
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 20,
            'margin_right' => 20,
            'margin_top' => 35,
            'margin_bottom' => 20,
            'margin_header' => 4,
            'margin_footer' => 4,
        ];
    }

    public function watermarkSize(): array
    {
        return [80, 80];
    }

    public function requiresTutorSignature(array $contracts): bool
    {
        return collect($contracts)->contains(fn (array $contract) => (bool) ($contract['requires_tutor_signature'] ?? false));
    }

    public function requiresPlayerSignature(array $contracts): bool
    {
        return collect($contracts)->contains(fn (array $contract) => (bool) ($contract['requires_player_signature'] ?? false));
    }

    public function acceptanceFields(array $contracts): array
    {
        return collect($contracts)
            ->pluck('acceptance_field')
            ->filter()
            ->values()
            ->all();
    }

    public function extractUsedParameters(array $fields): array
    {
        return collect($fields)
            ->flatMap(function (?string $field) {
                if (!is_string($field) || trim($field) === '') {
                    return [];
                }

                preg_match_all('/\\[([^\\]]+)\\]/', $field, $matches);

                return collect($matches[1] ?? [])
                    ->map(fn (string $parameter) => trim($parameter))
                    ->filter();
            })
            ->unique()
            ->values()
            ->all();
    }

    public function usedParametersForContract(?Contract $contract): array
    {
        if (!$contract instanceof Contract) {
            return [];
        }

        $parameters = $this->normalizeParameters($contract->parameters);

        if ($parameters !== []) {
            return collect($parameters)
                ->map(fn (string $parameter) => sprintf('[%s]', $parameter))
                ->values()
                ->all();
        }

        return collect($this->extractUsedParameters([$contract->header, $contract->body, $contract->footer]))
            ->map(fn (string $parameter) => sprintf('[%s]', $parameter))
            ->values()
            ->all();
    }

    private function contractsForSchool(School $school): EloquentCollection
    {
        return Contract::query()
            ->where('school_id', $school->id)
            ->with('contract_type')
            ->get();
    }

    private function resolveContractTypeModel(string $code): ?ContractType
    {
        $type = $this->supportedTypes()[$code] ?? null;

        if ($type === null) {
            return null;
        }

        return ContractType::query()
            ->where(function ($query) use ($type) {
                $query->where('code', $type['db_code']);

                if (isset($type['fallback_id'])) {
                    $query->orWhere('id', $type['fallback_id']);
                }

                if (!empty($type['fallback_name'])) {
                    $query->orWhere('name', $type['fallback_name']);
                }
            })
            ->orderByRaw('CASE WHEN code = ? THEN 0 ELSE 1 END', [$type['db_code']])
            ->orderBy('id')
            ->first();
    }

    private function replaceParameters(Contract $contract, array $variables): array
    {
        $parameters = $this->normalizeParameters($contract->parameters);

        if ($parameters === []) {
            $parameters = $this->extractUsedParameters([$contract->header, $contract->body, $contract->footer]);
        }

        $header = (string) $contract->header;
        $body = (string) $contract->body;
        $footer = (string) $contract->footer;

        foreach ($parameters as $parameter) {
            if (!array_key_exists($parameter, $variables)) {
                continue;
            }

            $token = sprintf('[%s]', $parameter);
            $value = (string) $variables[$parameter];

            $header = str_replace($token, $value, $header);
            $body = str_replace($token, $value, $body);
            $footer = str_replace($token, $value, $footer);
        }

        return [$header, $body, $footer];
    }

    private function normalizeParameters(?string $parameters): array
    {
        if (!is_string($parameters) || trim($parameters) === '') {
            return [];
        }

        return collect(explode(',', $parameters))
            ->map(fn (string $parameter) => trim(str_replace(['[', ']'], '', $parameter)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function isConfiguredContract(?Contract $contract): bool
    {
        if (!$contract instanceof Contract) {
            return false;
        }

        return filled($contract->header) && filled($contract->body) && filled($contract->footer);
    }

    private function schoolSignaturePath(School $school): string
    {
        $path = storage_path('app/public/' . $school->slug . '/firma10+pro.jpg');

        return is_file($path) ? $path : $this->fallbackSignaturePath($school);
    }

    private function schoolAssetPath(School $school, string $filename): string
    {
        $path = storage_path('app/public/' . $school->slug . '/' . $filename);

        return is_file($path) ? $path : $school->logo_local;
    }

    private function fallbackSignaturePath(School $school): string
    {
        return $school->logo_local;
    }

    private function localPath(string $relativePath): string
    {
        return storage_path('app/' . ltrim($relativePath, '/'));
    }
}
