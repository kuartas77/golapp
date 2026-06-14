<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\TrainingGroup;
use PHPUnit\Framework\TestCase;

class TrainingGroupTest extends TestCase
{
    public function testTrainingGroupWithoutCategoryHasCleanLabels(): void
    {
        $group = new TrainingGroup();
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

    public function testTrainingGroupWithCategoriesKeepsExistingLabelFormat(): void
    {
        $group = new TrainingGroup();
        $group->setRawAttributes([
            'name' => 'Grupo Avanzado',
            'category' => 'SUB-13,SUB-15',
            'days' => 'Lunes',
            'schedules' => '08:00 AM - 09:00 AM',
        ]);

        $this->assertSame(['SUB-13', 'SUB-15'], $group->category);
        $this->assertSame('Grupo Avanzado - (SUB-13,SUB-15)', $group->full_group);
        $this->assertSame('Grupo Avanzado - (SUB-13,SUB-15) Lunes 08:00 AM - 09:00 AM', $group->full_schedule_group);
    }
}
