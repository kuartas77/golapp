<?php

declare(strict_types=1);

namespace App\Service\Groups;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

final class TrainingGroupYearFilter
{
    public static function activeForCurrentYear(EloquentCollection|Collection $groups): EloquentCollection|Collection
    {
        return $groups->filter(fn ($group) => $group->year_active <= now()->year);
    }
}
