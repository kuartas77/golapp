<?php

namespace Tests\Feature;

use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
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

    public function test_optimize_if_changed_returns_success_after_generating_caches(): void
    {
        try {
            $this->artisan('optimize:if-changed')
                ->assertExitCode(Command::SUCCESS);
        } finally {
            Artisan::call('optimize:clear');
        }
    }
}
