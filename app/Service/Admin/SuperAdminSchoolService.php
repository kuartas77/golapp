<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Models\School;
use App\Models\SchoolUser;
use App\Models\Setting;
use App\Models\SettingValue;
use App\Models\User;
use App\Notifications\RegisterNotification;
use App\Traits\UploadFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SuperAdminSchoolService
{
    use UploadFile;

    public function options(?School $exclude = null): array
    {
        return [
            'schools' => School::query()
                ->when($exclude, fn ($query) => $query->whereKeyNot($exclude->id))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (School $school) => [
                    'value' => $school->id,
                    'label' => $school->name,
                ])
                ->values(),
        ];
    }

    public function formData(School $school): array
    {
        $school->loadMissing('settingsValues');

        return [
            'school' => [
                'id' => $school->id,
                'slug' => $school->slug,
                'name' => $school->name,
                'agent' => $school->agent,
                'address' => $school->address,
                'phone' => $school->phone,
                'email' => $school->email,
                'is_enable' => $school->is_enable,
                'logo_file' => $school->logo_file,
                'is_campus' => !empty($this->campusIdsForForm($school)),
            ],
            'multiple_schools' => $this->campusIdsForForm($school),
            ...$this->options($school),
        ];
    }

    public function store(FormRequest $request): School
    {
        $this->ensureMultipleSchoolsSettingExists();

        return DB::transaction(function () use ($request) {
            [$user, $password, $shouldNotify] = $this->resolveAdminUser($request);

            $data = $this->extractSchoolData($request);
            $school = School::query()->create($data);

            $user->forceFill(['school_id' => $school->id])->save();

            SchoolUser::query()->firstOrCreate([
                'user_id' => $user->id,
                'school_id' => $school->id,
            ]);

            $this->syncCampusGroup(
                $school,
                $request->boolean('is_campus'),
                $request->input('multiple_schools', [])
            );

            if ($shouldNotify && $password !== null) {
                $user->notify(new RegisterNotification($user, $password));
            }

            $this->flushCaches([$school->id]);

            return $school->fresh(['settingsValues']);
        });
    }

    public function update(FormRequest $request, School $school): School
    {
        $this->ensureMultipleSchoolsSettingExists();

        return DB::transaction(function () use ($request, $school) {
            $data = $this->extractSchoolData($request, $school);

            $school->fill($data)->save();

            $this->syncCampusGroup(
                $school,
                $request->boolean('is_campus'),
                $request->input('multiple_schools', [])
            );

            $this->flushCaches([$school->id]);

            return $school->fresh(['settingsValues']);
        });
    }

    private function extractSchoolData(FormRequest $request, ?School $school = null): array
    {
        $data = Arr::only($request->validated(), [
            'name',
            'slug',
            'agent',
            'address',
            'phone',
            'email',
            'is_enable',
        ]);

        if ($request->hasFile('logo')) {
            if ($school) {
                $request->merge(['school_id' => $school->id]);
            }

            $data['logo'] = $this->saveFile($request, 'logo');
        }

        return $data;
    }

    private function resolveAdminUser(FormRequest $request): array
    {
        if (!$request->boolean('is_campus')) {
            $password = randomPassword();
            $user = User::query()->create([
                'name' => $request->string('agent')->toString(),
                'email' => $request->string('email')->toString(),
                'password' => $password,
            ]);

            $user->syncRoles([User::SCHOOL]);

            if ($user->profile()->doesntExist()) {
                $user->profile()->create();
            }

            return [$user, $password, true];
        }

        $user = User::query()->firstWhere('email', $request->string('email')->toString());

        if ($user->profile()->doesntExist()) {
            $user->profile()->create();
        }

        return [$user, null, false];
    }

    private function syncCampusGroup(School $school, bool $isCampus, array $relatedSchoolIds): void
    {
        $normalizedRelatedIds = $this->normalizeSchoolIds($relatedSchoolIds, $school->id);
        $currentGroupIds = $this->storedCampusGroupIds($school);
        $newGroupIds = $isCampus
            ? array_values(array_unique([$school->id, ...$normalizedRelatedIds]))
            : [];

        $affectedSchoolIds = array_values(array_unique(array_merge($currentGroupIds, $newGroupIds, [$school->id])));

        foreach ($affectedSchoolIds as $schoolId) {
            if (count($newGroupIds) > 1 && in_array($schoolId, $newGroupIds, true)) {
                SettingValue::query()->updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'setting_key' => Setting::MULTIPLE_SCHOOLS,
                    ],
                    [
                        'value' => json_encode($newGroupIds, JSON_THROW_ON_ERROR),
                    ]
                );

                continue;
            }

            SettingValue::query()
                ->where('school_id', $schoolId)
                ->where('setting_key', Setting::MULTIPLE_SCHOOLS)
                ->delete();
        }

        $this->flushCaches($affectedSchoolIds);
    }

    private function campusIdsForForm(School $school): array
    {
        return array_values(array_filter(
            $this->storedCampusGroupIds($school),
            static fn (int $schoolId) => $schoolId !== $school->id
        ));
    }

    private function storedCampusGroupIds(School $school): array
    {
        $school->loadMissing('settingsValues');

        $multipleSchools = data_get($school, 'settings.' . Setting::MULTIPLE_SCHOOLS);

        if (!is_string($multipleSchools) || $multipleSchools === '') {
            return [];
        }

        $decoded = json_decode($multipleSchools, true);

        if (!is_array($decoded)) {
            return [];
        }

        return $this->normalizeSchoolIds($decoded);
    }

    private function normalizeSchoolIds(array $schoolIds, ?int $excludedSchoolId = null): array
    {
        $ids = collect($schoolIds)
            ->map(static fn ($value) => filter_var($value, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]))
            ->filter(static fn ($value) => $value !== false && $value !== null)
            ->map(static fn ($value) => (int) $value)
            ->unique()
            ->when($excludedSchoolId !== null, fn ($collection) => $collection->reject(
                static fn (int $value) => $value === $excludedSchoolId
            ))
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        return School::query()
            ->whereIn('id', $ids->all())
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();
    }

    private function ensureMultipleSchoolsSettingExists(): void
    {
        Setting::query()->firstOrCreate(
            ['key' => Setting::MULTIPLE_SCHOOLS],
            ['public' => false]
        );
    }

    private function flushCaches(array $schoolIds): void
    {
        foreach (array_unique($schoolIds) as $schoolId) {
            School::forgetCachedSchool((int) $schoolId);
        }

        Cache::forget('admin.schools');
        Cache::forget('SCHOOLS_ENABLED');
    }
}
