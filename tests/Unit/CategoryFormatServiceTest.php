<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Service\Category\CategoryFormatService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

final class CategoryFormatServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_formats_and_converts_both_supported_category_formats(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 12));
        $formatter = new CategoryFormatService;

        $this->assertSame('SUB-9', $formatter->formatBirthYearForMode(2017, CategoryFormatService::SUB_AGE));
        $this->assertSame('CAT-2017', $formatter->formatBirthYearForMode(2017, CategoryFormatService::BIRTH_YEAR));
        $this->assertSame('CAT-2015', $formatter->convertLabel('SUB-11', CategoryFormatService::BIRTH_YEAR));
        $this->assertSame('SUB-9', $formatter->convertLabel('CAT-2017', CategoryFormatService::SUB_AGE));
        $this->assertSame('SUB-9', $formatter->convertLabel('Categoria-2017', CategoryFormatService::SUB_AGE));
        $this->assertSame('Todas las categorías', $formatter->convertLabel('Todas las categorías', CategoryFormatService::BIRTH_YEAR));
    }
}
