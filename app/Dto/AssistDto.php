<?php

namespace App\Dto;

use App\Dto\DtoContract;
use stdClass;

class AssistDto implements DtoContract
{
    private function __construct(
        public readonly int $school_id,
        public readonly int $training_group_id,
        public readonly int $inscription_id,
        public readonly int $month,
        public readonly int $year,
        public readonly string $column,
        public readonly string $value,
        public readonly string|null $attendance_date,
        public readonly string|null $observations
    ) {}

    public function toArray(): array
    {
        return array_filter(get_object_vars($this));
    }

    public static function fromArray(array $data): DtoContract
    {
        return new self(
            school_id: $data['school_id'],
            training_group_id: $data['training_group_id'],
            inscription_id: $data['inscription_id'],
            month: $data['month'],
            year: $data['year'],
            column: $data['column'],
            value: $data['value'],
            observations: isset($data['observations']) ? $data['observations'] : null,
            attendance_date: isset($data['attendance_date']) ? $data['attendance_date'] : null,
        );
    }
}
