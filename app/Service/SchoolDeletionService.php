<?php

namespace App\Service;

use App\Models\School;
use App\Service\Auth\AuthUserContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SchoolDeletionService
{
    public function delete(int $schoolId): void
    {
        $school = School::withTrashed()->findOrFail($schoolId);
        $school->forceFill(['deletion_status' => 'processing', 'deletion_error' => null])->saveQuietly();

        $slug = $school->slug;
        $logo = $school->getRawOriginal('logo');
        $context = $this->context($schoolId);

        try {
            DB::transaction(function () use ($context, $school, $schoolId): void {
                Schema::disableForeignKeyConstraints();
                try {
                    $this->deleteDependentRows($context);
                    $this->deleteSchoolRows($schoolId);
                    $this->reassignSharedUsers($context['shared_user_schools'], $schoolId);
                    $this->deleteExclusiveUsers($context['exclusive_user_ids']);
                    $this->deleteOrphanPeople($context['people_ids']);
                    $school->forceDeleteQuietly();
                } finally {
                    Schema::enableForeignKeyConstraints();
                }
            });
        } catch (Throwable $exception) {
            $school->refresh()->forceFill([
                'deletion_status' => 'failed',
                'deletion_error' => mb_substr($exception->getMessage(), 0, 2000),
                'is_enable' => false,
            ])->saveQuietly();
            throw $exception;
        }

        Storage::disk('public')->deleteDirectory($slug);
        Storage::disk('local')->deleteDirectory("{$slug}/documents");
        Storage::disk('export')->deleteDirectory("school-data-exports/{$slug}");
        if (filled($logo)) {
            Storage::disk('public')->delete($logo);
        }
        foreach ($context['file_paths'] as $path) {
            Storage::disk('public')->delete($this->normalizePublicPath($path));
        }

        School::forgetCachedSchool($schoolId);
        AuthUserContext::forgetSchool($schoolId);
        Cache::forget('admin.schools');
    }

    private function context(int $schoolId): array
    {
        $ids = fn (string $table, string $column = 'id') => Schema::hasTable($table)
            ? DB::table($table)->where('school_id', $schoolId)->pluck($column)->filter()->all()
            : [];

        $playerIds = $ids('players');

        $userIds = collect($ids('schools_user', 'user_id'))->merge($ids('users'))->unique()->values()->all();
        $sharedUserSchools = $this->sharedUserSchools($schoolId, $userIds);

        return [
            'school_id' => $schoolId,
            'player_ids' => $playerIds,
            'inscription_ids' => $ids('inscriptions'),
            'training_group_ids' => $ids('training_groups'),
            'invoice_ids' => $ids('invoices'),
            'training_session_ids' => $ids('training_sessions'),
            'evaluation_template_ids' => $ids('evaluation_templates'),
            'player_evaluation_ids' => $ids('player_evaluations'),
            'people_ids' => Schema::hasTable('peoples_players')
                ? DB::table('peoples_players')->whereIn('player_id', $playerIds ?: [0])->pluck('people_id')->filter()->all()
                : [],
            'exclusive_user_ids' => array_values(array_diff($userIds, array_keys($sharedUserSchools))),
            'shared_user_schools' => $sharedUserSchools,
            'file_paths' => $this->filePaths($schoolId),
        ];
    }

    private function deleteDependentRows(array $context): void
    {
        $scopes = [
            ['player_evaluation_scores', 'player_evaluation_id', 'player_evaluation_ids'],
            ['evaluation_template_criteria', 'evaluation_template_id', 'evaluation_template_ids'],
            ['training_session_phases', 'training_session_id', 'training_session_ids'],
            ['training_session_details', 'training_session_id', 'training_session_ids'],
            ['invoice_items', 'invoice_id', 'invoice_ids'],
            ['competition_group_inscription', 'inscription_id', 'inscription_ids'],
            ['training_group_user', 'training_group_id', 'training_group_ids'],
            ['peoples_players', 'player_id', 'player_ids'],
        ];

        foreach ($scopes as [$table, $column, $idsKey]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                DB::table($table)->whereIn($column, $context[$idsKey] ?: [0])->delete();
            }
        }
    }

    private function deleteSchoolRows(int $schoolId): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $tables = collect(DB::select("select name from sqlite_master where type = 'table'"))
                ->pluck('name')
                ->filter(fn ($table) => Schema::hasColumn($table, 'school_id'))
                ->reject(fn ($table) => in_array($table, ['schools', 'users'], true));
        } else {
            $tables = collect(DB::select("select table_name from information_schema.columns where table_schema = database() and column_name = 'school_id'"))
                ->map(fn ($row) => $row->table_name ?? $row->TABLE_NAME ?? null)
                ->filter()
                ->reject(fn ($table) => str_starts_with($table, 'vw_') || in_array($table, ['schools', 'users'], true));
        }

        foreach ($tables as $table) {
            DB::table($table)->where('school_id', $schoolId)->delete();
        }
    }

    private function sharedUserSchools(int $schoolId, array $userIds): array
    {
        if (! $userIds || ! Schema::hasTable('schools_user')) {
            return [];
        }

        return DB::table('schools_user')->whereIn('user_id', $userIds)
            ->where('school_id', '!=', $schoolId)
            ->orderBy('school_id')
            ->get(['user_id', 'school_id'])
            ->unique('user_id')
            ->pluck('school_id', 'user_id')
            ->all();
    }

    private function reassignSharedUsers(array $sharedUserSchools, int $schoolId): void
    {
        foreach ($sharedUserSchools as $userId => $replacementSchoolId) {
            DB::table('users')->where('id', $userId)->where('school_id', $schoolId)
                ->update(['school_id' => $replacementSchoolId]);
        }
    }

    private function deleteExclusiveUsers(array $exclusiveIds): void
    {
        if (! $exclusiveIds || ! Schema::hasTable('users')) {
            return;
        }

        foreach (['personal_access_tokens' => 'tokenable_id', 'profiles' => 'user_id', 'model_has_roles' => 'model_id', 'model_has_permissions' => 'model_id'] as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                DB::table($table)->whereIn($column, $exclusiveIds ?: [0])->delete();
            }
        }
        DB::table('users')->whereIn('id', $exclusiveIds ?: [0])->delete();
    }

    private function deleteOrphanPeople(array $peopleIds): void
    {
        if (! $peopleIds || ! Schema::hasTable('peoples')) {
            return;
        }

        $stillLinked = Schema::hasTable('peoples_players')
            ? DB::table('peoples_players')->whereIn('people_id', $peopleIds)->pluck('people_id')->all()
            : [];
        DB::table('peoples')->whereIn('id', array_diff($peopleIds, $stillLinked) ?: [0])->delete();
    }

    private function filePaths(int $schoolId): array
    {
        $sources = [
            ['players', 'photo'],
            ['payment_request', 'image'],
            ['topic_notifications', 'image_url'],
        ];

        return collect($sources)->flatMap(function (array $source) use ($schoolId) {
            [$table, $column] = $source;
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                return [];
            }

            return DB::table($table)->where('school_id', $schoolId)->pluck($column);
        })->filter()->unique()->values()->all();
    }

    private function normalizePublicPath(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', trim(parse_url($path, PHP_URL_PATH) ?: $path)), '/');

        foreach (['storage/', 'img/dynamic/', 'api/img/dynamic/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return substr($path, strlen($prefix));
            }
        }

        return $path;
    }
}
