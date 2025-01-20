<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CompetitionGroupInscription extends Pivot
{
    protected $table = 'competition_group_inscription';
}
