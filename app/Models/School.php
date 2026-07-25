<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\ResolvesLocalAssetPath;
use App\Observers\SchoolObserver;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $name
 * @property string $agent
 * @property string $address
 * @property string $phone
 * @property string $email
 * @property bool $is_enable
 * @property string $logo
 */
class School extends Model
{
    use HasFactory;
    use ResolvesLocalAssetPath;
    use SoftDeletes;

    public const ORGANIZATION_TYPE_SCHOOL = 'school';

    public const ORGANIZATION_TYPE_CLUB = 'club';

    public const ORGANIZATION_TYPE_ACADEMY = 'academy';

    public const ORGANIZATION_TYPE_FOUNDATION = 'foundation';

    public const ORGANIZATION_TYPE_LEAGUE = 'league';

    public const ORGANIZATION_TYPE_OTHER = 'other';

    public const KEY_SCHOOL_CACHE = 'school_';

    public const CACHE_PREFIX_ADMIN = 'admin.';

    public const CACHE_PREFIX_SCHOOL = 'school.';

    protected $table = "schools";

    protected $fillable = [
        'name',
        'organization_type',
        'agent',
        'address',
        'phone',
        'email',
        'is_enable',
        'logo',
        'slug',
        'create_contract',
        'send_documents',
        'send_monthly_payment_receipts',
        'tutor_platform',
        'sign_player',
        'inscriptions_enabled',
        'school_permissions',
        'short_name',
        'email_info',
        'auto_invoice',
        'deletion_status',
        'deletion_error',
        'deletion_requested_at',
    ];

    protected $casts = [
        'is_enable' => 'bool',
        'create_contract' => 'bool',
        'send_documents' => 'bool',
        'send_monthly_payment_receipts' => 'bool',
        'tutor_platform' => 'bool',
        'sign_player' => 'bool',
        'inscriptions_enabled' => 'bool',
        'school_permissions' => 'array',
        'auto_invoice' => 'bool',
        'deletion_requested_at' => 'datetime',
    ];

    protected $appends = [
        'logo_file',
        'settings'
    ];

    protected $with = ['settingsValues'];

    protected static function booted()
    {
        self::creating(function (self $school) {
            if ($school->getAttribute('school_permissions') === null) {
                $school->setAttribute('school_permissions', self::defaultSchoolPermissions());
            }
        });

        self::observe(SchoolObserver::class);
    }

    public static function permissionCatalog(): array
    {
        return config('school_permissions.permissions', []);
    }

    public static function defaultSchoolPermissions(): array
    {
        return collect(self::permissionCatalog())
            ->mapWithKeys(fn (array $permission, string $key) => [$key => (bool) ($permission['default'] ?? false)])
            ->all();
    }

    public static function normalizeSchoolPermissions(array $permissions): array
    {
        $defaults = self::defaultSchoolPermissions();

        foreach ($defaults as $key => $default) {
            if (!array_key_exists($key, $permissions)) {
                continue;
            }

            $normalized = filter_var($permissions[$key], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $defaults[$key] = $normalized ?? (bool) $permissions[$key];
        }

        return $defaults;
    }

    public static function cacheKeyFor(string $prefixKey, int $schoolId): string
    {
        if ($prefixKey === '') {
            return self::KEY_SCHOOL_CACHE . $schoolId;
        }

        return self::KEY_SCHOOL_CACHE . sprintf('_%s_%s', $prefixKey, $schoolId);
    }

    public static function organizationTypes(): array
    {
        return config('sports.organization_types', []);
    }

    public static function defaultOrganizationType(): string
    {
        return (string) config('sports.default_organization_type', self::ORGANIZATION_TYPE_SCHOOL);
    }

    public static function sportCatalog(): array
    {
        return config('sports.sports', []);
    }

    public static function defaultSports(): array
    {
        return [(string) config('sports.default_sport', 'football')];
    }

    public static function normalizeSports(array $sports): array
    {
        $catalog = array_keys(self::sportCatalog());
        $normalized = collect($sports)
            ->map(static fn ($sport) => (string) $sport)
            ->filter(static fn (string $sport) => in_array($sport, $catalog, true))
            ->unique()
            ->values()
            ->all();

        return $normalized ?: self::defaultSports();
    }

    public static function cacheKeysFor(int $schoolId): array
    {
        return [
            self::cacheKeyFor('', $schoolId),
            self::cacheKeyFor(self::CACHE_PREFIX_ADMIN, $schoolId),
            self::cacheKeyFor(self::CACHE_PREFIX_SCHOOL, $schoolId),
        ];
    }

    public static function forgetCachedSchool(int $schoolId): void
    {
        foreach (self::cacheKeysFor($schoolId) as $cacheKey) {
            Cache::forget($cacheKey);
        }
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function setLogoAttribute($value): void
    {
        if (!empty($value)) {
            if (!empty($this->attributes['logo'])) {
                Storage::disk('public')->delete($this->attributes['logo']);
            }

            $this->attributes['logo'] = $value;
        }
    }

    public function getLogoFileAttribute(): string
    {
        if (!empty($this->attributes['logo']) && Storage::disk('public')->exists($this->attributes['logo'])) {
            return route('images', $this->attributes['logo']);
        }

        return asset('img/ballon.webp');
    }

    public function getUrlEditAttribute(): string
    {
        return route('config.schools.edit', [$this->attributes['slug']]);
    }

    public function getUrlUpdateAttribute(): string
    {
        return route('config.schools.update', [$this->attributes['slug']]);
    }

    public function getUrlShowAttribute(): string
    {
        return ""; //route('public.school.show', [$this->attributes['slug']]);
    }

    public function getUrlDestroyAttribute(): string
    {
        return route('config.schools.destroy', [$this->attributes['slug']]);
    }

    public function getLogoLocalAttribute(): string
    {
        return $this->resolveLocalAssetPath($this->attributes['logo'] ?? null, [
            storage_path('standard/ballon.webp'),
            public_path('img/ballon.webp'),
            public_path('img/not-found.png'),
        ]);
    }

    public function getEnabledSportsAttribute(): array
    {
        $value = data_get($this, 'settings.' . Setting::SPORTS_ENABLED);

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                return self::normalizeSports($decoded);
            }
        }

        if (is_array($value)) {
            return self::normalizeSports($value);
        }

        return self::defaultSports();
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, SchoolUser::class, 'school_id', 'id', 'id', 'user_id');
    }

    public function admin(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, SchoolUser::class, 'school_id', 'id', 'id', 'user_id');
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tournament_payouts(): HasMany
    {
        return $this->hasMany(TournamentPayout::class);
    }

    public function assists(): HasMany
    {
        return $this->hasMany(Assist::class);
    }

    public function skillControls(): HasMany
    {
        return $this->hasMany(SkillsControl::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class);
    }

    public function competitionGroups(): HasMany
    {
        return $this->hasMany(CompetitionGroup::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function uniform_requests(): HasMany
    {
        return $this->hasMany(UniformRequest::class);
    }

    public function topic_notifications(): HasMany
    {
        return $this->hasMany(TopicNotification::class);
    }

    public function configDefault(): void
    {
        $schedules = [
            ['schedule' => '07:00AM - 08:00AM'],
            ['schedule' => "08:00AM - 09:00AM"],
            ['schedule' => "09:00AM - 10:00AM"],
            ['schedule' => "10:00AM - 11:00AM"],
            ['schedule' => "11:00AM - 12:00M"],
            ['schedule' => "12:00M  - 01:00PM"],
            ['schedule' => "01:00PM - 02:00PM"],
            ['schedule' => "02:00PM - 03:00PM"],
            ['schedule' => "03:00PM - 04:00PM"],
            ['schedule' => "04:00PM - 05:00PM"],
            ['schedule' => "05:00PM - 06:00PM"],
            ['schedule' => "06:00PM - 07:00PM"],
            ['schedule' => "07:00PM - 08:00PM"],
            ['schedule' => "08:00PM - 09:00PM"],
            ['schedule' => "09:00PM - 10:00PM"],
        ];

        if (!$this->schedules()->exists()) {
            $this->schedules()->createMany($schedules);
        }

        $this->trainingGroups()->firstOrCreate(
            ['name' => 'Provisional'],
            [
                'year' => null,
                'category' => 'Todas las categorías',
                'days' => 'Grupo predeterminado',
                'schedules' => '10:00AM - 11:00AM',
            ]
        );

        $defaultSettings = collect(SettingValue::settingsDefault($this->id));
        foreach ($defaultSettings->pluck('setting_key')->unique() as $settingKey) {
            Setting::query()->firstOrCreate(
                ['key' => $settingKey],
                ['public' => false]
            );
        }
        $existingSettings = SettingValue::query()
            ->where('school_id', $this->id)
            ->whereIn('setting_key', $defaultSettings->pluck('setting_key')->all())
            ->get()
            ->keyBy('setting_key');

        foreach ($defaultSettings as $setting) {
            $existingSetting = $existingSettings->get($setting['setting_key']);

            if ($existingSetting) {
                continue;
            }

            SettingValue::query()->create($setting);
        }

        $this->load('settingsValues');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function trainingGroups(): HasMany
    {
        return $this->hasMany(TrainingGroup::class);
    }

    public function settingsValues(): HasMany
    {
        return $this->hasMany(SettingValue::class);
    }

    public function getSettingsAttribute()
    {
        if ($this->relationLoaded('settingsValues')) {
            return $this->settingsValues->mapWithKeys(function ($setting) {
                return [$setting->setting_key => $setting->value];
            });
        }

        return null;
    }

    public function getResolvedSchoolPermissions(): array
    {
        return self::normalizeSchoolPermissions($this->school_permissions ?? []);
    }

    public function hasSchoolPermission(string $key): bool
    {
        $permissions = $this->getResolvedSchoolPermissions();

        return array_key_exists($key, $permissions) && (bool) $permissions[$key];
    }

}
