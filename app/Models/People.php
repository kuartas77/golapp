<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @property mixed id
 */
class People extends Model
{
    use HasFactory;

    const TUTOR = 1;

    const FATHER = 2;

    const MOTHER = 3;

    const FAMILY_ONE = 4;

    const FAMILY_TWO = 5;

    protected $table = 'peoples';

    protected $fillable = [
        'tutor',
        'relationship',
        'names',
        'phone',
        'mobile',
        'identification_card',
        'neighborhood',
        'email',
        'profession',
        'business',
        'position'
    ];

    protected $casts = [
        'tutor' => 'integer'
    ];

    protected $appends = ['relationship_name'];

    public function setNamesAttribute($value): void
    {
        $this->attributes['names'] = strtoupper($value);
    }

    public function getRelationshipNameattribute()
    {
        $relationship = config('variables.KEY_RELATIONSHIPS_SELECT');
        return array_key_exists((integer)$this->relationship, $relationship) ? $relationship[(integer)$this->relationship] : '';
    }

}
