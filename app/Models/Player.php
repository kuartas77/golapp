<?php

namespace App\Models;

use App\Traits\Fields;
use Jenssegers\Date\Date;
use Illuminate\Http\Request;
use App\Traits\GeneralScopes;
use Intervention\Image\Facades\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property mixed inscriptions
 * @property mixed photo
 */
class Player extends Model
{
    use SoftDeletes;
    use Fields;
    use GeneralScopes;
    use HasFactory;

    protected $table = "players";
    protected $dateFormat = "Y-m-d";

    protected $fillable = [
        'id',
        'unique_code',
        'names',
        'last_names',
        'gender',
        'date_birth',
        'place_birth',
        'identification_document',
        'rh',
        'photo',
        'position_field',
        'dominant_profile',
        'school',
        'degree',
        'address',
        'municipality',
        'neighborhood',
        'zone',
        'commune',
        'phones',
        'email',
        'mobile',
        'eps',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'date_birth' => "datetime:Y-m-d",
        'created_at' => "datetime:Y-m-d",
    ];

    protected $appends = ['full_names', 'url_edit', 'url_show', 'url_impression'];

    public function getRouteKeyName(): string
    {
        return 'unique_code';
    }

    public function setPhotoAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['photo'] = $value;
        }
    }

    public function getUrlImpressionAttribute(): string
    {
        return route('export.player', [$this->attributes['unique_code']]);
    }

    public function getUrlEditAttribute(): string
    {
        return route('players.edit', [$this->attributes['unique_code']]);
    }

    public function getUrlShowAttribute(): string
    {
        return route('players.show', [$this->attributes['unique_code']]);
    }

    public function getDateBirthAttribute(): string
    {
        return Date::parse($this->attributes['date_birth'])->format('Y-m-d');
    }

    public function getFullNamesAttribute(): string
    {
        return "{$this->names} {$this->last_names}";
    }

    public function getPayYearsAttribute()
    {
        return $this->payments->pluck('year');
    }

    public function getPhotoAttribute(): string
    {
        if (Storage::disk('public')->exists($this->attributes['photo'])) {
            return url("storage/{$this->attributes['photo']}");
        }
        return url('img/user.png');
    }

    public function getPhotoFileAttribute():string
    {
        if (Storage::disk('public')->exists($this->attributes['photo'])) {
            return url("storage/{$this->attributes['photo']}");
        }
        return url('img/user.png');
    }

    public function getTutorPeopleAttribute()
    {
        return $this->peoples->where('is_tutor', true)->first();
    }

    public function inscription(): HasOne
    {
        return $this->hasOne(Inscription::class)->where('year', now())->latest('year');
    }

    public function inscriptions(): HasMany
    {
        return $this->hasMany(Inscription::class);
    }

    public function peoples(): BelongsToMany
    {
        return $this->belongsToMany(People::class, 'peoples_players');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'unique_code','unique_code');
    }

    /**
     * @param $method
     * @param Request $request
     * @return string|null
     */
    public function fileName($method, Request $request): ?string
    {
        $path = null;
        if ($method == 'store') {
            $path = 'user.png';
        }
        if ($request->hasFile('image')) {

            if ($method == 'update') {
                Storage::disk('public')->delete($this->photo);
            }

            $file = $request->file('image');
            $path = $file->hashName('img');

            $img = Image::make($file)->resize(420, 420, function ($constraint) {
                $constraint->aspectRatio();
            });
            Storage::disk('public')->put($path, (string)$img->encode());
        }
        return $path;
    }
}
