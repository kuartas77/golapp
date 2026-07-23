<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assist;
use App\Models\Inscription;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\People;
use App\Models\Player;
use App\Models\School;
use App\Models\TopicNotification;
use App\Models\TrainingGroup;
use App\Models\UniformRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class NotificationGuardianApiTest extends TestCase
{
    public function testGuardianMobileLoginDoesNotRequireRecaptcha(): void
    {
        $this->createGuardianScenario([
            'email' => 'without.recaptcha@example.com',
            'password' => 'secret-mobile',
        ]);

        config()->set('recaptchav3.sitekey', 'configured-site-key');
        config()->set('recaptchav3.secret', 'configured-secret');

        app()->detectEnvironment(fn (): string => 'production');

        try {
            $this->postJson('/api/notify/v2/guardians/login', [
                'email' => 'without.recaptcha@example.com',
                'password' => 'secret-mobile',
            ])->assertOk();
        } finally {
            app()->detectEnvironment(fn (): string => 'testing');
        }
    }

    public function testGuardianCanLoginAndReceiveMobileTokensWithPlayers(): void
    {
        [$guardian, $player] = $this->createGuardianScenario([
            'email' => 'mobile.guardian@example.com',
            'password' => 'secret-mobile',
        ]);

        $response = $this->postJson('/api/notify/v2/guardians/login', [
            'email' => 'mobile.guardian@example.com',
            'password' => 'secret-mobile',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'token_type',
                'access_token',
                'refresh_token',
                'expires_at',
                'guardian' => ['id', 'names', 'email'],
                'players' => [['id', 'full_names', 'unique_code']],
                'topics',
            ],
        ]);
        $response->assertJsonPath('data.players.0.id', $player->id);
        $this->assertContains($player->unique_code . '-' . $player->schoolData->slug, $response->json('data.topics'));
        $this->assertNotNull($guardian->fresh()->last_login_at);
    }

    public function testGuardianMobileTokenRemainsValidAfterGlobalOneMinuteWindow(): void
    {
        $this->createGuardianScenario([
            'email' => 'lasting.guardian@example.com',
            'password' => 'secret-mobile',
        ]);

        $loginResponse = $this->postJson('/api/notify/v2/guardians/login', [
            'email' => 'lasting.guardian@example.com',
            'password' => 'secret-mobile',
        ]);

        $loginResponse->assertOk();

        $this->travel(2)->minutes();

        $this->withHeader('Authorization', 'Bearer ' . $loginResponse->json('data.access_token'))
            ->getJson('/api/notify/v2/guardians/players')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function testGuardianCanRegisterMobileDeviceToken(): void
    {
        [$guardian] = $this->createGuardianScenario();

        Sanctum::actingAs($guardian, ['auth']);

        $this->postJson('/api/notify/v2/guardians/notifications/device-token', [
            'platform' => 'android',
            'token' => 'firebase-device-token',
        ])->assertOk()
            ->assertJsonPath('data.platform', 'android');

        $this->assertDatabaseHas('guardian_device_tokens', [
            'people_id' => $guardian->id,
            'platform' => 'android',
            'token' => 'firebase-device-token',
        ]);
    }

    public function testGuardianDeviceTokenRequiresAuthenticationAndValidPayload(): void
    {
        $this->postJson('/api/notify/v2/guardians/notifications/device-token', [
            'platform' => 'android',
            'token' => 'firebase-device-token',
        ])->assertUnauthorized();

        [$guardian] = $this->createGuardianScenario();

        Sanctum::actingAs($guardian, ['auth']);

        $this->postJson('/api/notify/v2/guardians/notifications/device-token', [
            'platform' => 'windows',
            'token' => '',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['platform', 'token']);
    }

    public function testGuardianAggregatedListsOnlyReturnOwnedPlayersAndIncludePlayerPayload(): void
    {
        [$guardian, $ownedPlayer, $ownedInscription, $school] = $this->createGuardianScenario();
        [, $otherPlayer, $otherInscription] = $this->createGuardianScenario();

        $ownedInvoice = $this->createInvoice($ownedInscription, $school);
        $this->createInvoice($otherInscription, $otherPlayer->schoolData);

        UniformRequest::query()->create([
            'school_id' => $school->id,
            'player_id' => $ownedPlayer->id,
            'type' => 'UNIFORM',
            'quantity' => 1,
            'size' => 'M',
            'additional_notes' => 'Own request',
        ]);
        UniformRequest::query()->create([
            'school_id' => $otherPlayer->school_id,
            'player_id' => $otherPlayer->id,
            'type' => 'BALL',
            'quantity' => 1,
            'size' => 'U',
            'additional_notes' => 'Other request',
        ]);

        $notification = TopicNotification::query()->create([
            'school_id' => $school->id,
            'topics' => 'general',
            'title' => 'Entrenamiento',
            'body' => 'Mensaje para el acudiente',
            'type' => 'GENERAL',
            'priority' => 'NORMAL',
        ]);
        $notification->players()->attach($ownedPlayer->id, [
            'school_id' => $school->id,
            'is_read' => false,
        ]);

        Sanctum::actingAs($guardian, ['auth', 'payment-index', 'request-index', 'notification-index']);

        $this->getJson('/api/notify/v2/guardians/invoices')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownedInvoice->id)
            ->assertJsonPath('data.0.player.id', $ownedPlayer->id)
            ->assertJsonPath('data.0.player.unique_code', $ownedPlayer->unique_code);

        $this->getJson('/api/notify/v2/guardians/requests')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.player.full_names', $ownedPlayer->full_names);

        $this->getJson('/api/notify/v2/guardians/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.player.unique_code', $ownedPlayer->unique_code);
    }

    public function testGuardianCannotAccessOtherPlayerResources(): void
    {
        [$guardian] = $this->createGuardianScenario();
        [, $otherPlayer, $otherInscription] = $this->createGuardianScenario();

        $otherInvoice = $this->createInvoice($otherInscription, $otherPlayer->schoolData);
        $otherRequest = UniformRequest::query()->create([
            'school_id' => $otherPlayer->school_id,
            'player_id' => $otherPlayer->id,
            'type' => 'UNIFORM',
            'quantity' => 1,
            'size' => 'M',
        ]);

        Sanctum::actingAs($guardian, ['auth', 'payment-index', 'request-index']);

        $this->getJson("/api/notify/v2/guardians/invoices/{$otherInvoice->id}")
            ->assertNotFound();

        $this->getJson("/api/notify/v2/guardians/requests/{$otherRequest->id}")
            ->assertNotFound();
    }

    public function testGuardianCanCreateRequestForOwnedPlayerOnly(): void
    {
        [$guardian, $ownedPlayer] = $this->createGuardianScenario();
        [, $otherPlayer] = $this->createGuardianScenario();

        Sanctum::actingAs($guardian, ['auth', 'request-store']);

        $this->postJson('/api/notify/v2/guardians/requests', [
            'player_id' => $ownedPlayer->id,
            'type' => 'UNIFORM',
            'quantity' => 2,
            'size' => 'M',
            'additional_notes' => 'Para torneo',
        ])->assertCreated()
            ->assertJsonPath('data.player.id', $ownedPlayer->id);

        $this->postJson('/api/notify/v2/guardians/requests', [
            'player_id' => $otherPlayer->id,
            'type' => 'UNIFORM',
            'quantity' => 1,
            'size' => 'S',
        ])->assertNotFound();
    }

    public function testGuardianCanUploadPaymentProofForOwnedInvoiceOnly(): void
    {
        Storage::fake('public');

        [$guardian, , $ownedInscription, $school] = $this->createGuardianScenario();
        [, $otherPlayer, $otherInscription] = $this->createGuardianScenario();

        $ownedInvoice = $this->createInvoice($ownedInscription, $school);
        $otherInvoice = $this->createInvoice($otherInscription, $otherPlayer->schoolData);

        Sanctum::actingAs($guardian, ['auth', 'payment-update']);

        $this->postJson('/api/notify/v2/guardians/invoices/payment', [
            'invoice_id' => $ownedInvoice->id,
            'amount' => 50000,
            'description' => 'Pago parcial',
            'reference_number' => 'REF-1',
            'payment_method' => 'transfer',
            'image' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertOk()
            ->assertJsonPath('data.id', $ownedInvoice->id);

        $this->postJson('/api/notify/v2/guardians/invoices/payment', [
            'invoice_id' => $otherInvoice->id,
            'amount' => 50000,
            'payment_method' => 'transfer',
            'image' => UploadedFile::fake()->image('proof-other.jpg'),
        ])->assertNotFound();
    }

    public function testGuardianMarksNotificationReadForOnlyTheSelectedPlayer(): void
    {
        [$guardian, $playerA, , $school] = $this->createGuardianScenario();
        [$playerB] = $this->attachSecondPlayerToGuardian($guardian, $school);

        $notification = TopicNotification::query()->create([
            'school_id' => $school->id,
            'topics' => 'general',
            'title' => 'Recordatorio',
            'body' => 'Pago pendiente',
            'type' => 'REMINDER',
            'priority' => 'NORMAL',
        ]);
        $notification->players()->attach($playerA->id, ['school_id' => $school->id, 'is_read' => false]);
        $notification->players()->attach($playerB->id, ['school_id' => $school->id, 'is_read' => false]);

        Sanctum::actingAs($guardian, ['auth', 'notification-index']);

        $this->putJson('/api/notify/v2/guardians/notifications/read', [
            'notification_id' => $notification->id,
            'player_id' => $playerA->id,
        ])->assertOk();

        $this->assertDatabaseHas('player_topic_notification', [
            'topic_notification_id' => $notification->id,
            'player_id' => $playerA->id,
            'is_read' => true,
        ]);
        $this->assertDatabaseHas('player_topic_notification', [
            'topic_notification_id' => $notification->id,
            'player_id' => $playerB->id,
            'is_read' => false,
        ]);
    }

    public function testGuardianCanMarkNotificationReadUsingMobileUrlContract(): void
    {
        [$guardian, $playerA, , $school] = $this->createGuardianScenario();
        [$playerB] = $this->attachSecondPlayerToGuardian($guardian, $school);

        $notification = TopicNotification::query()->create([
            'school_id' => $school->id,
            'topics' => 'general',
            'title' => 'Aviso móvil',
            'body' => 'Mensaje pendiente',
            'type' => 'GENERAL',
            'priority' => 'NORMAL',
        ]);
        $notification->players()->attach($playerA->id, ['school_id' => $school->id, 'is_read' => false]);
        $notification->players()->attach($playerB->id, ['school_id' => $school->id, 'is_read' => false]);

        Sanctum::actingAs($guardian, ['auth', 'notification-index']);

        $this->putJson("/api/notify/v2/guardians/notifications/read/{$notification->id}")
            ->assertOk()
            ->assertJsonPath('data.success', true);

        $this->assertDatabaseMissing('player_topic_notification', [
            'topic_notification_id' => $notification->id,
            'is_read' => false,
        ]);
    }

    public function testGuardianCanFetchOwnedPlayerSportsSummary(): void
    {
        [$guardian, $player, $inscription, , $trainingGroup] = $this->createGuardianScenario();
        $trainingGroup->update(['name' => 'Sub 12 Mobile']);

        Sanctum::actingAs($guardian, ['auth']);

        $this->getJson("/api/notify/v2/guardians/players/{$player->id}/sports-summary")
            ->assertOk()
            ->assertJsonPath('data.id', $player->id)
            ->assertJsonPath('data.unique_code', $player->unique_code)
            ->assertJsonPath('data.full_names', $player->full_names)
            ->assertJsonPath('data.current_inscription.id', $inscription->id)
            ->assertJsonPath('data.current_inscription.training_group.name', 'Sub 12 Mobile')
            ->assertJsonStructure([
                'data' => [
                    'current_inscription' => [
                        'stats' => [
                            'total_matches',
                            'assistance',
                            'goals',
                            'qualification',
                        ],
                    ],
                ],
            ]);
    }

    public function testGuardianCanFetchOwnedPlayerMobileActivity(): void
    {
        [$guardian, $player, $inscription, $school, $trainingGroup] = $this->createGuardianScenario();
        $trainingGroup->update(['days' => ['Lunes']]);

        Payment::query()->updateOrCreate([
            'inscription_id' => $inscription->id,
            'year' => now()->year,
        ], array_merge([
            'school_id' => $school->id,
            'training_group_id' => $trainingGroup->id,
            'unique_code' => $player->unique_code,
            'enrollment' => 1,
            'january' => 1,
            'february' => 2,
        ], array_fill_keys(array_values(Payment::FIELD_AMOUNT_MAP), 0)));

        $assist = Assist::query()->updateOrCreate([
            'inscription_id' => $inscription->id,
            'training_group_id' => $trainingGroup->id,
            'year' => now()->year,
            'month' => now()->month,
        ], [
            'school_id' => $school->id,
            'assistance_one' => 1,
            'assistance_two' => 1,
            'assistance_three' => 1,
            'assistance_four' => 1,
        ]);

        Sanctum::actingAs($guardian, ['auth']);

        $response = $this->getJson("/api/notify/v2/guardians/players/{$player->id}/activity")
            ->assertOk()
            ->assertJsonPath('data.player.id', $player->id)
            ->assertJsonPath('data.current_inscription.id', $inscription->id)
            ->assertJsonPath('data.payments.0.months.0.field', 'january')
            ->assertJsonPath('data.payments.0.months.0.value', 1)
            ->assertJsonPath('data.attendance.0.id', $assist->id)
            ->assertJsonMissingPath('data.evaluations');

        $this->assertTrue(
            collect($response->json('data.attendance.0.registers'))
                ->contains(fn (array $register): bool => $register['status'] === 1 && $register['label'] === 'Asistencia')
        );
    }

    public function testGuardianCannotFetchOtherPlayerMobileExperience(): void
    {
        [$guardian] = $this->createGuardianScenario();
        [, $otherPlayer] = $this->createGuardianScenario();

        Sanctum::actingAs($guardian, ['auth']);

        $this->getJson("/api/notify/v2/guardians/players/{$otherPlayer->id}/sports-summary")
            ->assertNotFound();
        $this->getJson("/api/notify/v2/guardians/players/{$otherPlayer->id}/activity")
            ->assertNotFound();
    }

    public function testGuardianMobileExperienceEndpointsRequireAuthentication(): void
    {
        [, $player] = $this->createGuardianScenario();

        $this->getJson("/api/notify/v2/guardians/players/{$player->id}/sports-summary")
            ->assertUnauthorized();
        $this->getJson("/api/notify/v2/guardians/players/{$player->id}/activity")
            ->assertUnauthorized();
    }

    private function createGuardianScenario(array $guardianAttributes = []): array
    {
        $school = School::factory()->create([
            'email' => fake()->unique()->safeEmail(),
            'tutor_platform' => true,
            'is_enable' => true,
        ]);

        $trainingGroup = $school->trainingGroups()->create([
            'name' => 'Provisional',
            'year' => now()->year,
            'year_active' => now()->year,
            'category' => 'Todas las categorias',
            'days' => 'Grupo predeterminado',
            'schedules' => '10:00AM - 11:00AM',
        ]);

        $player = Player::factory()->create([
            'school_id' => $school->id,
            'unique_code' => (string) fake()->unique()->numerify('##########'),
            'identification_document' => (string) fake()->unique()->numerify('##########'),
        ]);

        $guardian = People::factory()->create(array_merge([
            'tutor' => true,
            'email' => fake()->unique()->safeEmail(),
            'password' => 'secret-guardian',
            'identification_card' => (string) fake()->unique()->numerify('##########'),
        ], $guardianAttributes));

        $player->people()->attach($guardian->id);

        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => now()->year,
            'school_id' => $school->id,
            'training_group_id' => $trainingGroup->id,
            'competition_group_id' => null,
            'category' => categoriesName((int) date('Y', strtotime($player->date_birth))),
        ]);

        return [$guardian, $player, $inscription, $school, $trainingGroup];
    }

    private function attachSecondPlayerToGuardian(People $guardian, School $school): array
    {
        $trainingGroup = TrainingGroup::query()
            ->where('school_id', $school->id)
            ->firstOrFail();

        $player = Player::factory()->create([
            'school_id' => $school->id,
            'unique_code' => (string) fake()->unique()->numerify('##########'),
            'identification_document' => (string) fake()->unique()->numerify('##########'),
        ]);

        $player->people()->attach($guardian->id);

        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => now()->year,
            'school_id' => $school->id,
            'training_group_id' => $trainingGroup->id,
            'competition_group_id' => null,
            'category' => categoriesName((int) date('Y', strtotime($player->date_birth))),
        ]);

        return [$player, $inscription];
    }

    private function createInvoice(Inscription $inscription, School $school): Invoice
    {
        return Invoice::query()->create([
            'invoice_number' => 'INV-' . fake()->unique()->numerify('######'),
            'inscription_id' => $inscription->id,
            'training_group_id' => $inscription->training_group_id,
            'year' => now()->year,
            'student_name' => $inscription->player->full_names,
            'total_amount' => 100000,
            'paid_amount' => 0,
            'status' => 'pending',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
            'school_id' => $school->id,
            'created_by' => $this->user->id,
        ]);
    }
}
