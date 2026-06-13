<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MethodologyRecord extends Model
{
    use HasFactory;
    use GeneralScopes;
    use SoftDeletes;

    public const TYPE_PLANNING = 'planning';
    public const TYPE_CHARACTERIZATION_SHEET = 'characterization_sheet';
    public const TYPE_MONTHLY_REPORT = 'monthly_report';
    public const TYPE_CATEGORY_MONTHLY_REPORT = 'category_monthly_report';

    public const TYPES = [
        self::TYPE_PLANNING,
        self::TYPE_CHARACTERIZATION_SHEET,
        self::TYPE_MONTHLY_REPORT,
        self::TYPE_CATEGORY_MONTHLY_REPORT,
    ];

    protected $fillable = [
        'school_id',
        'user_id',
        'training_group_id',
        'type',
        'title',
        'fields',
        'diagrams',
    ];

    protected $casts = [
        'fields' => 'array',
        'diagrams' => 'array',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingGroup(): BelongsTo
    {
        return $this->belongsTo(TrainingGroup::class);
    }
}
