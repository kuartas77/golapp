<?php

namespace App\Dto;

use App\Shared\Traits\StaticCreateSelf;
use App\Shared\Traits\ToArray;

final class AssistDTO
{
    use StaticCreateSelf;
    use ToArray;

    public readonly int $school_id;
    public readonly int $training_group_id;
    public readonly int $inscription_id;
    public readonly int $month;
    public readonly int $year;
    public readonly string $column;
    public readonly int $value;
    public readonly string|null $attendance_date;
    public readonly string|null $observations;
}
