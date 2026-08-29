<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\TrainingGroup;
use PHPUnit\Framework\TestCase;

class TrainingGroupTest extends TestCase
{
    public function test_training_group_without_category_has_clean_labels(): void
    {
        $group = new TrainingGroup;
        $group->setRawAttributes([
            'name' => 'Grupo Libre',
            'category' => null,
            'days' => 'Lunes',
            'schedules' => '08:00 AM - 09:00 AM',
        ]);

        $this->assertSame([], $group->category);
        $this->assertSame('Grupo Libre', $group->full_group);
        $this->assertSame('Grupo Libre Lunes 08:00 AM - 09:00 AM', $group->full_schedule_group);
        $this->assertStringNotContainsString('()', $group->full_group);
        $this->assertStringNotContainsString('()', $group->full_schedule_group);
    }

    public function test_training_group_categories_are_not_included_in_labels(): void
    {
        $group = new TrainingGroup;
        $group->setRawAttributes([
            'name' => 'Grupo Avanzado',
            'category' => 'SUB-13,SUB-15',
            'days' => 'Lunes',
            'schedules' => '08:00 AM - 09:00 AM',
        ]);

        $this->assertSame(['SUB-13', 'SUB-15'], $group->category);
        $this->assertSame('Grupo Avanzado', $group->full_group);
        $this->assertSame('Grupo Avanzado Lunes 08:00 AM - 09:00 AM', $group->full_schedule_group);
    }

    public function test_training_group_labels_include_stage_when_present(): void
    {
        $group = new TrainingGroup;
        $group->setRawAttributes([
            'name' => 'Grupo Avanzado',
            'stage' => 'Cancha Norte',
            'category' => 'SUB-13,SUB-15',
            'days' => 'Lunes',
            'schedules' => '08:00 AM - 09:00 AM',
        ]);

        $this->assertSame('Grupo Avanzado - Cancha Norte', $group->full_group);
        $this->assertSame(
            'Grupo Avanzado - Cancha Norte Lunes 08:00 AM - 09:00 AM',
            $group->full_schedule_group
        );
    }

    public function test_training_group_without_schedules_has_clean_labels_and_empty_exploded_value(): void
    {
        $group = new TrainingGroup;
        $group->setRawAttributes([
            'name' => 'Grupo Sin Horario',
            'stage' => 'Cancha Norte',
            'days' => 'Lunes,Miércoles',
            'schedules' => null,
        ]);

        $this->assertSame('Grupo Sin Horario - Cancha Norte', $group->full_group);
        $this->assertSame('Grupo Sin Horario - Cancha Norte Lunes,Miércoles', $group->full_schedule_group);
        $this->assertSame([], $group->explode_schedules);
        $this->assertStringNotContainsString('  ', $group->full_schedule_group);
    }

    public function test_training_group_normalizes_empty_schedules_to_null(): void
    {
        $group = new TrainingGroup;

        $group->schedules = [];

        $this->assertNull($group->getAttributes()['schedules']);
        $this->assertSame([], $group->explode_schedules);
    }
}
