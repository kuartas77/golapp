<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolOutingContribution extends Model
{
    use GeneralScopes;
    use HasFactory;

    protected $fillable = [
        'school_outing_id',
        'school_outing_participant_id',
        'school_outing_activity_id',
        'school_id',
        'amount',
        'contribution_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'contribution_date' => 'date:Y-m-d',
    ];

    public function outing(): BelongsTo
    {
        return $this->belongsTo(SchoolOuting::class, 'school_outing_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(SchoolOutingParticipant::class, 'school_outing_participant_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(SchoolOutingActivity::class, 'school_outing_activity_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
