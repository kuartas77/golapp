<?php

namespace App\Models;

use App\Traits\Fields;
use App\Traits\GeneralScopes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use SoftDeletes;
    use Fields;
    use HasFactory;
    use GeneralScopes;
    
    protected $table = "schedules";
    protected $fillable = [
        'schedule', 'day_id','school_id',
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    public function day(): BelongsTo
    {
        return $this->belongsTo(Day::class);
    }
}
