<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\PreinscriptionsProvitional;
use App\Models\Inscription;
use App\Models\Player;
use App\Models\TrainingGroup;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class VerifyInscriptionStatusTest extends TestCase
{
    public function test_it_sends_preinscription_report_only_to_valid_school_admin_emails(): void
    {
        Mail::fake();

        $schoolId = $this->school['id'];
        $trainingGroup = TrainingGroup::query()->where('school_id', $schoolId)->firstOrFail();
        $player = Player::factory()->create();

        Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => now()->year,
            'school_id' => $schoolId,
            'training_group_id' => $trainingGroup->id,
            'pre_inscription' => true,
        ]);

        User::factory()
            ->create([
                'email' => 'correo-invalido',
                'school_id' => $schoolId,
            ])
            ->assignRole('school');

        $this->artisan('inscription:status')->assertExitCode(Command::SUCCESS);

        Mail::assertSent(PreinscriptionsProvitional::class, function (PreinscriptionsProvitional $mail): bool {
            return $mail->hasTo($this->user->email);
        });
        Mail::assertNotSent(PreinscriptionsProvitional::class, function (PreinscriptionsProvitional $mail): bool {
            return $mail->hasTo('correo-invalido');
        });
        Mail::assertSent(PreinscriptionsProvitional::class, 1);
    }
}
