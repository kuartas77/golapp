<?php

namespace Tests\Feature;

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsoleCommandExitCodesTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_payments_returns_a_success_exit_code(): void
    {
        $this->artisan('check:payments')
            ->assertExitCode(Command::SUCCESS);
    }

    public function test_check_categories_returns_a_success_exit_code(): void
    {
        $this->artisan('check:categories')
            ->assertExitCode(Command::SUCCESS);
    }
}
