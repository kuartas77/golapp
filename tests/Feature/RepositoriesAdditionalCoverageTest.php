<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Dto\AssistDTO;
use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Player;
use App\Models\PlayerTopicNotification;
use App\Models\Schedule;
use App\Models\Tournament;
use App\Models\TournamentPayout;
use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use App\Models\User;
use App\Repositories\AssistRepository;
use App\Repositories\BaseRepository;
use App\Repositories\IncidentRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentRequestRepository;
use App\Repositories\PeopleRepository;
use App\Repositories\ScheduleRepository;
use App\Repositories\TopicNotificationRepository;
use App\Repositories\TournamentPayoutsRepository;
use App\Repositories\TrainingSessionRepository;
use App\Repositories\UniformRequestRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

final class RepositoriesAdditionalCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testBaseRepositoryAndPeopleRepositoryBasicFlows(): void
    {
        $this->actingAs($this->user);

        $baseRepository = new BaseRepository(new Schedule());
        $schedule = Schedule::query()->create([
            'schedule' => '08:00 - 09:00',
            'school_id' => $this->school['id'],
        ]);

        $all = $baseRepository->all();
        $this->assertGreaterThan(0, $all->count());
        $this->assertNotNull($baseRepository->get($schedule->id));

        $schedule->schedule = '09:00 - 10:00';
        $saved = $baseRepository->save($schedule);
        $this->assertSame('09:00 - 10:00', $saved->schedule);

        $deleted = $baseRepository->delete($schedule);
        $this->assertNotNull($deleted->deleted_at);

        $peopleRepository = app(PeopleRepository::class);
        $peopleIds = $peopleRepository->getPeopleIds([
            [
                'tutor' => 'true',
                'relationship' => '30',
                'names' => 'Tutor Uno',
                'identification_card' => 'DOC-100',
                'phone' => '3001234567',
            ],
        ]);

        $this->assertCount(1, $peopleIds);
        $this->assertNotNull($peopleIds->first());
    }

    public function testScheduleRepositoryStoreUpdateAndAll(): void
    {
        $this->actingAs($this->user);
        $repository = app(ScheduleRepository::class);

        $repository->store([
            'schedule' => '07:00 - 08:00',
            'school_id' => $this->school['id'],
        ]);

        $schedule = Schedule::query()->where('school_id', $this->school['id'])->firstOrFail();
        $repository->update([
            'schedule' => '10:00 - 11:00',
            'school_id' => $this->school['id'],
        ], $schedule);

        $all = $repository->all();
        $this->assertTrue($all->contains(fn(Schedule $item) => $item->id === $schedule->id));
    }

    public function testUniformRequestRepositoryStoreAndCancel(): void
    {
        $player = $this->createTestPlayer();
        app('request')->setUserResolver(fn() => $player);

        $repository = app(UniformRequestRepository::class);
        $created = $repository->store([
            'type' => 'UNIFORM',
            'quantity' => 1,
            'size' => 'M',
            'additional_notes' => 'Inicial',
        ]);

        $this->assertNotEmpty($created);
        $this->assertSame('PENDING', $created->fresh()->status);

        $cancelled = $repository->cancel($created);
        $this->assertTrue($cancelled);
        $this->assertSame('CANCELLED', $created->fresh()->status);
    }

    public function testPaymentRepositorySetPayPaymentsByStatusAndGraphics(): void
    {
        $this->actingAs($this->user);
        [, $payment] = $this->createInscriptionAndPayment();
        $repository = app(PaymentRepository::class);

        $results = $repository->paymentsByStatus(['status' => '1']);
        $this->assertGreaterThan(0, $results->count());

        $setPay = $repository->setPay(['january' => '1'], $payment);
        $this->assertTrue($setPay);

        $graphics = $repository->dataGraphicsYear((int) now()->year);
        $this->assertTrue($graphics->has('labels'));
        $this->assertTrue($graphics->has('series'));
    }

    public function testTrainingSessionRepositoryStoreUpdateAndList(): void
    {
        $this->actingAs($this->user);
        $trainingGroup = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();
        $repository = app(TrainingSessionRepository::class);

        $payload = $this->trainingSessionPayload($trainingGroup->id);
        $stored = $repository->store($payload);
        $this->assertNotNull($stored);
        $this->assertSame(1, $stored->tasks()->count());

        $payload['material'] = 'Conos y escaleras';
        $payload['task_name'][0] = 'TaskB';
        $updated = $repository->update($stored, $payload);
        $this->assertTrue($updated);
        $this->assertSame('Conos y escaleras', $stored->fresh()->material);

        $list = $repository->list();
        $this->assertTrue($list->contains(fn(TrainingSession $item) => $item->id === $stored->id));
    }

    public function testAssistRepositoryUpsertBranches(): void
    {
        $this->actingAs($this->user);
        [$inscription] = $this->createInscriptionAndPayment();
        $trainingGroup = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();
        $repository = app(AssistRepository::class);

        $skipDto = AssistDTO::fromArray([
            'school_id' => $this->school['id'],
            'training_group_id' => $trainingGroup->id,
            'inscription_id' => $inscription->id,
            'month' => 1,
            'year' => (int) now()->year,
            'column' => 'assistance_one',
            'value' => null,
            'attendance_date' => null,
            'observations' => null,
        ]);
        $this->assertTrue($repository->upsert($skipDto));

        DB::table('assists')->insert([
            'training_group_id' => $trainingGroup->id,
            'inscription_id' => $inscription->id,
            'year' => now()->year,
            'month' => '1',
            'school_id' => $this->school['id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $updateDto = AssistDTO::fromArray([
            'school_id' => $this->school['id'],
            'training_group_id' => $trainingGroup->id,
            'inscription_id' => $inscription->id,
            'month' => 1,
            'year' => (int) now()->year,
            'column' => 'assistance_one',
            'value' => 1,
            'attendance_date' => '2026-03-03',
            'observations' => 'ok',
        ]);

        $this->assertTrue($repository->upsert($updateDto));
        $this->assertDatabaseHas('assists', [
            'inscription_id' => $inscription->id,
            'assistance_one' => '1',
        ]);
    }

    public function testIncidentAndTournamentPayoutRepositories(): void
    {
        $this->actingAs($this->user);
        [$inscription] = $this->createInscriptionAndPayment();
        $incidentRepository = app(IncidentRepository::class);

        $incidentRequest = new class ((int) $this->user->id)
        {
            public function __construct(private int $userIncidentId)
            {
            }

            public function input(string $key): int
            {
                return $this->userIncidentId;
            }

            public function validated(): array
            {
                return [
                    'user_incident_id' => $this->userIncidentId,
                    'incidence' => 'Falta',
                    'description' => 'Incidente de prueba',
                ];
            }
        };

        $createdIncident = $incidentRepository->createIncident($incidentRequest);
        $this->assertNotNull($createdIncident->id);
        $this->assertGreaterThan(0, $incidentRepository->all()->count());

        $tournament = Tournament::query()->create([
            'name' => 'Torneo Test',
            'school_id' => $this->school['id'],
        ]);

        $competitionGroup = CompetitionGroup::query()->create([
            'name' => 'Comp A',
            'year' => (string) now()->year,
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'category' => '2010-2011',
            'school_id' => $this->school['id'],
        ]);

        $payout = TournamentPayout::query()->create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
            'year' => now()->year,
            'unique_code' => $inscription->unique_code,
            'status' => '0',
            'value' => 0,
        ]);

        $payoutRepository = app(TournamentPayoutsRepository::class);
        $query = $payoutRepository->filterSelect([
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
            'year' => now()->year,
            'unique_code' => $inscription->unique_code,
        ]);
        $this->assertTrue($query->exists());

        $updated = $payoutRepository->update($payout, ['status' => '1', 'value' => 90000]);
        $this->assertTrue($updated);
        $this->assertSame('1', $payout->fresh()->status);
    }

    public function testPaymentRequestRepositoryCreatePaymentRequest(): void
    {
        $this->actingAs($this->user);
        Storage::fake('public');
        $player = $this->createTestPlayer();
        [$inscription, , $trainingGroup] = $this->createInscriptionAndPayment($player);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'FAC-TEST-' . now()->format('YmdHis'),
            'inscription_id' => $inscription->id,
            'training_group_id' => $trainingGroup->id,
            'year' => now()->year,
            'student_name' => $player->names . ' ' . $player->last_names,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addWeek()->toDateString(),
            'status' => 'pending',
            'school_id' => $this->school['id'],
            'created_by' => $this->user->id,
        ]);

        app('request')->setUserResolver(fn() => $player);
        $repository = app(PaymentRequestRepository::class);

        $result = $repository->createPaymentRequest([
            'invoice_id' => $invoice->id,
            'amount' => 10000,
            'description' => 'Pago parcial',
            'reference_number' => 'REF-123',
            'payment_method' => 'transfer',
            'image' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        $this->assertNotNull($result);
        $this->assertSame($invoice->id, $result->id);
        $this->assertDatabaseHas('payment_request', [
            'invoice_id' => $invoice->id,
            'player_id' => $player->id,
            'school_id' => $this->school['id'],
        ]);
    }

    public function testTopicNotificationRepositoryMarkReadAndMarkReadAllWithMocks(): void
    {
        $repository = app(TopicNotificationRepository::class);

        $relation = Mockery::mock();
        $relation->shouldReceive('whereKey')->once()->with(15)->andReturnSelf();
        $relation->shouldReceive('first')->once()->andReturn((object) ['id' => 15]);
        $relation->shouldReceive('updateExistingPivot')->once()->with(15, ['is_read' => true]);
        $relation->shouldReceive('pluck')->once()->with('topic_notifications.id')->andReturn(collect([21, 22]));
        $relation->shouldReceive('updateExistingPivot')->once()->with(21, ['is_read' => true]);
        $relation->shouldReceive('updateExistingPivot')->once()->with(22, ['is_read' => true]);

        $player = Mockery::mock();
        $player->shouldReceive('notifications')->andReturn($relation);

        app('request')->merge(['notificationId' => 15]);
        app('request')->setUserResolver(fn() => $player);

        $repository->markRead();
        $repository->markReadAll();

        $this->assertTrue(true);
    }

    private function createTestPlayer(): Player
    {
        return Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => 'RC-' . fake()->unique()->numberBetween(1000, 9999),
        ]);
    }

    private function createInscriptionAndPayment(?Player $player = null): array
    {
        $player = $player ?: $this->createTestPlayer();
        $trainingGroup = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();

        $inscription = Inscription::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => now()->year,
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'category' => '2010-2011',
            'training_group_id' => $trainingGroup->id,
            'competition_group_id' => null,
        ]);

        $payment = Payment::query()
            ->where('inscription_id', $inscription->id)
            ->where('year', now()->year)
            ->firstOrFail();
        $payment->school_id = $this->school['id'];
        $payment->january = '2';
        $payment->february = '1';
        $payment->save();

        return [$inscription, $payment, $trainingGroup];
    }

    private function trainingSessionPayload(int $trainingGroupId): array
    {
        return [
            'school_id' => $this->school['id'],
            'user_id' => $this->user->id,
            'training_group_id' => $trainingGroupId,
            'year' => now()->year,
            'period' => 'P1',
            'session' => 'S1',
            'date' => now()->toDateString(),
            'hour' => '08:00',
            'training_ground' => 'Cancha A',
            'material' => 'Balones',
            'back_to_calm' => 'Si',
            'players' => '10',
            'absences' => '0',
            'incidents' => 'N/A',
            'feedback' => 'OK',
            'warm_up' => 'Trote',
            'task_number' => [1],
            'task_name' => ['TaskA'],
            'general_objective' => ['Obj'],
            'specific_goal' => ['Goal'],
            'content_one' => ['C1'],
            'content_two' => ['C2'],
            'content_three' => ['C3'],
            'ts' => ['10'],
            'sr' => ['10'],
            'tt' => ['20'],
            'observations' => ['None'],
        ];
    }
}
