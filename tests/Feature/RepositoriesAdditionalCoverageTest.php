<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Dto\AssistDTO;
use App\Models\Assist;
use App\Models\CompetitionGroup;
use App\Models\CompetitionGroupInscription;
use App\Models\Game;
use App\Models\Inscription;
use App\Models\Invoice;
use App\Models\PaymentReceived;
use App\Models\Payment;
use App\Models\Player;
use App\Models\PlayerTopicNotification;
use App\Models\Schedule;
use App\Models\SchoolUser;
use App\Models\SkillsControl;
use App\Models\Tournament;
use App\Models\TournamentPayout;
use App\Models\TopicNotification;
use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use App\Models\User;
use App\Notifications\RegisterNotification;
use App\Repositories\AssistRepository;
use App\Repositories\BaseRepository;
use App\Repositories\CompetitionGroupRepository;
use App\Repositories\IncidentRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentRequestRepository;
use App\Repositories\PeopleRepository;
use App\Repositories\ScheduleRepository;
use App\Repositories\SchoolRepository;
use App\Repositories\TopicNotificationRepository;
use App\Repositories\TournamentPayoutsRepository;
use App\Repositories\TrainingGroupRepository;
use App\Repositories\TrainingSessionRepository;
use App\Repositories\UniformRequestRepository;
use App\Repositories\UserRepository;
use App\Repositories\GameRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
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

        $this->assertDatabaseHas('assists', [
            'training_group_id' => $trainingGroup->id,
            'inscription_id' => $inscription->id,
            'year' => now()->year,
            'month' => '1',
            'school_id' => $this->school['id'],
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

    public function testSchoolRepositoryGetAllAndSchoolsInfo(): void
    {
        $this->actingAs($this->user);
        $repository = app(SchoolRepository::class);

        $schools = $repository->getAll();
        $this->assertGreaterThan(0, $schools->count());
        $this->assertNotEmpty($schools->first()->url_edit);

        $schoolInfo = $repository->schoolsInfo((int) $this->school['id']);
        $this->assertNotNull($schoolInfo);
        $this->assertSame((int) $this->school['id'], (int) $schoolInfo->id);
    }

    public function testUserRepositoryGetAllTrashAndRestore(): void
    {
        $this->actingAs($this->user);
        $repository = app(UserRepository::class);

        $user = User::factory()->create([
            'school_id' => $this->school['id'],
            'email' => fake()->unique()->safeEmail(),
        ]);
        $user->profile()->create();
        $user->syncRoles([User::INSTRUCTOR]);
        SchoolUser::query()->create([
            'school_id' => $this->school['id'],
            'user_id' => $user->id,
        ]);

        $all = $repository->getAll();
        $this->assertTrue($all->contains(fn(User $item) => $item->id === $user->id));

        $user->delete();
        $trash = $repository->getAllTrash();
        $this->assertTrue($trash->contains(fn(User $item) => $item->id === $user->id));

        $restored = $repository->restore($user->id);
        $this->assertSame(1, $restored);
    }

    public function testInvoiceRepositoryCoreQueriesAndPlayerStats(): void
    {
        $this->actingAs($this->user);
        $player = $this->createTestPlayer();
        [$inscription, $payment, $trainingGroup] = $this->createInscriptionAndPayment($player);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'FAC-COV-' . now()->format('YmdHis'),
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

        $repository = app(InvoiceRepository::class);
        $query = $repository->query();
        $this->assertInstanceOf(Builder::class, $query);

        [$inscriptionData, $pendingMonths] = $repository->createInvoice($inscription->id);
        $this->assertSame($inscription->id, $inscriptionData->id);
        $this->assertIsArray($pendingMonths);

        app('request')->setUserResolver(fn() => $player);
        $playerInvoices = $repository->invoicesPlayer();
        $stats = $repository->statisticsPlayer();
        $this->assertGreaterThan(0, $playerInvoices->count());
        $this->assertGreaterThan(0, $stats->count());

        $itemsQuery = $repository->getAllItems();
        $this->assertInstanceOf(Builder::class, $itemsQuery);
    }

    public function testInvoicePaymentRegistrationUpdatesIssueDate(): void
    {
        $this->actingAs($this->user);
        [$inscription, $payment, $trainingGroup] = $this->createInscriptionAndPayment();

        Schema::disableForeignKeyConstraints();

        try {
            $invoice = Invoice::query()->create([
                'invoice_number' => 'FAC-PAY-' . now()->format('YmdHis'),
                'inscription_id' => $inscription->id,
                'training_group_id' => $trainingGroup->id,
                'year' => now()->year,
                'student_name' => $inscription->player->full_names,
                'total_amount' => 0,
                'paid_amount' => 0,
                'issue_date' => '2026-04-01',
                'due_date' => now()->addWeek()->toDateString(),
                'status' => 'pending',
                'school_id' => $this->school['id'],
                'created_by' => $this->user->id,
            ]);

            $item = $invoice->items()->create([
                'type' => 'monthly',
                'description' => 'Mensualidad Enero',
                'quantity' => 1,
                'unit_price' => 50000,
                'month' => 'january',
                'payment_id' => $payment->id,
                'is_paid' => false,
                'payment_received_id' => 0,
            ]);

            $response = $this->from(route('invoices.show', $invoice->id))
                ->post(route('invoices.addPayment', $invoice->id), [
                    'amount' => '50000',
                    'payment_method' => 'cash',
                    'issue_date' => '2026-04-15',
                    'payment_date' => '2026-04-20',
                    'reference' => 'REF-50000',
                    'notes' => 'Pago de prueba',
                    'paid_items' => [$item->id],
                ]);

            $response->assertRedirect(route('invoices.show', $invoice->id));

            $invoice->refresh();
            $item->refresh();
            $payment->refresh();

            $paymentReceived = PaymentReceived::query()->firstWhere('invoice_id', $invoice->id);

            $this->assertNotNull($paymentReceived);
            $this->assertSame('2026-04-15', $invoice->issue_date->toDateString());
            $this->assertSame('2026-04-20', $paymentReceived->payment_date->toDateString());
            $this->assertSame('paid', $invoice->status);
            $this->assertSame('50000.00', $invoice->paid_amount);
            $this->assertTrue($item->is_paid);
            $this->assertSame($paymentReceived->id, $item->payment_received_id);
            $this->assertSame('1', (string) $payment->january);
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    public function testGameRepositoryDatatableAndExportMatchDetail(): void
    {
        $this->actingAs($this->user);
        [$inscription] = $this->createInscriptionAndPayment();

        $tournament = Tournament::query()->create([
            'name' => 'Torneo Cobertura',
            'school_id' => $this->school['id'],
        ]);

        $competitionGroup = CompetitionGroup::query()->create([
            'name' => 'Competencia Cobertura',
            'year' => (string) now()->year,
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'category' => '2010-2011',
            'school_id' => $this->school['id'],
        ]);
        $competitionGroup->inscriptions()->attach($inscription->id);

        $game = Game::query()->create([
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
            'date' => now()->toDateString(),
            'hour' => '08:00',
            'num_match' => '1',
            'place' => 'Cancha',
            'rival_name' => 'Rival',
            'final_score' => '{"local":1,"visitor":0}',
            'general_concept' => 'Partido de prueba',
            'school_id' => $this->school['id'],
        ]);

        $repository = app(GameRepository::class);
        $datatable = $repository->getDatatable(now()->year)->get();
        $this->assertTrue($datatable->contains(fn(Game $item) => $item->id === $game->id));

        $details = $repository->exportMatchDetail($competitionGroup->id);
        $this->assertGreaterThan(0, $details->count());
    }

    public function testCompetitionGroupRepositoryMainFlows(): void
    {
        $this->actingAs($this->user);
        [$inscription] = $this->createInscriptionAndPayment();

        $tournament = Tournament::query()->create([
            'name' => 'Torneo Grupo',
            'school_id' => $this->school['id'],
        ]);

        $repository = app(CompetitionGroupRepository::class);
        $group = $repository->createOrUpdateTeam([
            'name' => 'Grupo Cobertura',
            'year' => (string) now()->year,
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'category' => '2010-2011',
            'school_id' => $this->school['id'],
        ]);
        $this->assertNotNull($group->id);

        $enabled = $repository->listGroupEnabled();
        $this->assertTrue($enabled->contains(fn(CompetitionGroup $item) => $item->id === $group->id));

        $fullName = $repository->getListGroupFullName();
        $this->assertArrayHasKey($group->id, $fullName->toArray());

        $yearGroups = $repository->getGroupsYear((string) now()->year);
        $this->assertArrayHasKey($group->id, $yearGroups->toArray());

        $assign = $repository->assignInscriptionGroup((string) $inscription->id, (string) $group->id, true);
        $this->assertSame(1, $assign);
        $this->assertDatabaseHas('competition_group_inscription', [
            'competition_group_id' => $group->id,
            'inscription_id' => $inscription->id,
        ]);

        $rowsData = $repository->makeRows($group->fresh());
        $this->assertIsArray($rowsData);
        $this->assertSame(1, $rowsData[1]);

        $unassign = $repository->assignInscriptionGroup((string) $inscription->id, (string) $group->id, false);
        $this->assertSame(2, $unassign);

        $group->delete();
        $disabled = $repository->listGroupDisabled();
        $this->assertTrue($disabled->contains(fn(CompetitionGroup $item) => $item->id === $group->id));
    }

    public function testTrainingGroupRepositoryFlows(): void
    {
        $this->actingAs($this->user);
        $repository = app(TrainingGroupRepository::class);
        $year = (int) now()->year;

        $payload = [
            'name' => 'Grupo Entreno',
            'stage' => 'Stage A',
            'user_id' => [$this->user->id],
            'years' => [2010, 2011],
            'schedules' => ['08:00AM - 09:00AM'],
            'days' => ['lunes', 'miercoles'],
            'school_id' => $this->school['id'],
            'year_active' => $year,
        ];

        $request = Mockery::mock(FormRequest::class);
        $request->shouldReceive('input')
            ->andReturnUsing(fn(string $key, $default = null) => data_get($payload, $key, $default));

        $group = $repository->createTrainingGroup($request);
        $this->assertNotNull($group);

        $loadedGroup = $repository->getTrainingGroup($group);
        $this->assertGreaterThan(0, $loadedGroup->years->count());

        $listByUser = $repository->getListGroupsSchedule(false, $this->user->id);
        $this->assertTrue($listByUser->contains(fn(TrainingGroup $item) => $item->id === $group->id));

        $listFiltered = $repository->getListGroupsSchedule(false, null, fn($items) => $items->filter(fn($item) => $item->id === $group->id));
        $this->assertTrue($listFiltered->contains(fn(TrainingGroup $item) => $item->id === $group->id));

        $groupsYear = $repository->getGroupsYear('2010');
        $this->assertArrayHasKey($group->id, $groupsYear->toArray());

        [$inscription, $payment] = $this->createInscriptionAndPayment($this->createTestPlayer(), $group);
        DB::table('assists')->insert([
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => $year - 1,
            'month' => '1',
            'school_id' => $this->school['id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payment->update(['year' => $year - 1]);

        $historicAssists = $repository->historicAssistData();
        $this->assertGreaterThanOrEqual(1, $historicAssists->count());

        $historicPayments = $repository->historicPaymentData();
        $this->assertGreaterThanOrEqual(1, $historicPayments->count());

        $group->delete();
        DB::table('training_groups')
            ->where('id', $group->id)
            ->update(['year_active' => $year - 1]);
        DB::table('training_group_user')
            ->where('training_group_id', $group->id)
            ->update(['assigned_year' => $year - 1]);

        $disabled = $repository->listGroupDisabled();
        $this->assertTrue($disabled->contains(fn(TrainingGroup $item) => $item->id === $group->id));
    }

    public function testTournamentPayoutsRepositoryCreateAndSearch(): void
    {
        $this->actingAs($this->user);
        [$inscription] = $this->createInscriptionAndPayment();

        $tournament = Tournament::query()->create([
            'name' => 'Torneo Payout',
            'school_id' => $this->school['id'],
        ]);

        $competitionGroup = CompetitionGroup::query()->create([
            'name' => 'Grupo Payout',
            'year' => (string) now()->year,
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'category' => '2010-2011',
            'school_id' => $this->school['id'],
        ]);
        CompetitionGroupInscription::query()->create([
            'competition_group_id' => $competitionGroup->id,
            'inscription_id' => $inscription->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $repository = app(TournamentPayoutsRepository::class);
        $created = $repository->create([
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
        ]);
        $this->assertIsArray($created);
        $this->assertArrayHasKey('count', $created);
        $this->assertGreaterThanOrEqual(1, $created['count']);

        $raw = $repository->search([
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
            'unique_code' => $inscription->unique_code,
        ], true);
        $this->assertIsArray($raw);
        $this->assertArrayHasKey('rows', $raw);
        $this->assertGreaterThanOrEqual(1, $raw['count']);

        $table = $repository->search([
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
            'unique_code' => $inscription->unique_code,
        ]);
        $this->assertIsArray($table);
        $this->assertArrayHasKey('rows', $table);
        $this->assertGreaterThanOrEqual(1, $table['count']);
    }

    public function testUserRepositoryCreateAndUpdate(): void
    {
        $this->actingAs($this->user);
        Notification::fake();
        $repository = app(UserRepository::class);

        $createRequest = new class ([
            'name' => 'Repo User',
            'email' => fake()->unique()->safeEmail(),
            'password' => 'secret123',
        ], 3) extends FormRequest
        {
            public string $password;

            public function __construct(private array $data, private int $roleId)
            {
                parent::__construct();
                $this->password = (string) data_get($data, 'password');
            }

            public function validated($key = null, $default = null): array
            {
                return $this->data;
            }

            public function input($key = null, $default = null): mixed
            {
                if ($key === 'rol_id') {
                    return $this->roleId;
                }

                return data_get($this->data, $key, $default);
            }
        };

        $created = $repository->create($createRequest);
        $this->assertInstanceOf(User::class, $created);
        $this->assertNotNull($created->id);
        $this->assertDatabaseHas('schools_user', [
            'school_id' => $this->school['id'],
            'user_id' => $created->id,
        ]);
        $this->assertDatabaseHas('profiles', ['user_id' => $created->id]);
        Notification::assertSentTo([$created], RegisterNotification::class);

        $updateRequest = new class ([
            'name' => 'Repo User Updated',
            'email' => $created->email,
        ], 2) extends FormRequest
        {
            public function __construct(private array $data, private int $roleId)
            {
                parent::__construct();
            }

            public function validated($key = null, $default = null): array
            {
                return $this->data;
            }

            public function input($key = null, $default = null): mixed
            {
                if ($key === 'rol_id') {
                    return $this->roleId;
                }

                return data_get($this->data, $key, $default);
            }
        };

        $repository->update($created, $updateRequest);
        $this->assertSame('Repo User Updated', $created->fresh()->name);
    }

    public function testGameRepositoryExtendedFlows(): void
    {
        $this->actingAs($this->user);

        $renderable = new class
        {
            public function render(): string
            {
                return '<tr></tr>';
            }
        };
        View::shouldReceive('make')->andReturn($renderable);

        [$inscription] = $this->createInscriptionAndPayment();
        $tournament = Tournament::query()->create([
            'name' => 'Torneo Game Ext',
            'school_id' => $this->school['id'],
        ]);
        $competitionGroup = CompetitionGroup::query()->create([
            'name' => 'Grupo Game Ext',
            'year' => (string) now()->year,
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'category' => '2010-2011',
            'school_id' => $this->school['id'],
        ]);
        $competitionGroup->inscriptions()->attach($inscription->id);

        $game = Game::query()->create([
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
            'date' => now()->toDateString(),
            'hour' => '08:00',
            'num_match' => '2',
            'place' => 'Cancha B',
            'rival_name' => 'Rival B',
            'final_score' => '{"local":2,"visitor":1}',
            'general_concept' => 'Partido extendido',
            'school_id' => $this->school['id'],
        ]);

        SkillsControl::query()->create([
            'game_id' => $game->id,
            'inscription_id' => $inscription->id,
            'assistance' => true,
            'titular' => true,
            'played_approx' => 45,
            'position' => 'DEF',
            'goals' => 0,
            'yellow_cards' => 0,
            'red_cards' => 0,
            'qualification' => '4',
            'observation' => 'Bien',
            'school_id' => $this->school['id'],
        ]);

        $repository = app(GameRepository::class);
        request()->merge(['competition_group' => $competitionGroup->id]);
        $matchInfo = $repository->getInformationToMatch();
        $this->assertSame($competitionGroup->id, $matchInfo->id);
        $this->assertSame(1, $matchInfo->count);

        $matchInfoEdit = $repository->getInformationToMatch($game->fresh());
        $this->assertSame($competitionGroup->id, $matchInfoEdit->id);
        $this->assertSame(1, $matchInfoEdit->count);

        $newMatch = $repository->createMatchSkill([
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
            'date' => now()->toDateString(),
            'hour' => '09:00',
            'num_match' => '3',
            'place' => 'Cancha C',
            'rival_name' => 'Rival C',
            'final_score' => '{"local":0,"visitor":0}',
            'general_concept' => 'Sin goles',
            'school_id' => $this->school['id'],
        ], [
            'inscriptions_id' => [],
        ]);
        $this->assertNotNull($newMatch->id);

        $updated = $repository->updateMatchSkill([
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
            'date' => now()->toDateString(),
            'hour' => '10:00',
            'num_match' => '4',
            'place' => 'Cancha D',
            'rival_name' => 'Rival D',
            'final_score' => '{"local":1,"visitor":1}',
            'general_concept' => 'Empate',
            'school_id' => $this->school['id'],
        ], [
            'ids' => [],
            'inscriptions_id' => [],
            'assistance' => [],
            'titular' => [],
            'played_approx' => [],
            'position' => [],
            'goals' => [],
            'goal_assists' => [],
            'goal_saves' => [],
            'red_cards' => [],
            'yellow_cards' => [],
            'qualification' => [],
            'observation' => [],
        ], $game);
        $this->assertTrue($updated);

        $pdfRepository = Mockery::mock(GameRepository::class, [new Game()])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $pdfRepository->shouldReceive('setConfigurationMpdf')->once();
        $pdfRepository->shouldReceive('createPDF')->once();
        $pdfRepository->shouldReceive('stream')->once()->andReturn('pdf-stream');

        $pdfResult = $pdfRepository->makePDF($game->id);
        $this->assertSame('pdf-stream', $pdfResult);
    }

    public function testGameRepositoryCreateUpdateAndLoadDataCoverRemainingBranches(): void
    {
        $this->actingAs($this->user);

        if (!Schema::hasColumn('skills_control', 'goal_assists')) {
            Schema::table('skills_control', function ($table): void {
                $table->smallInteger('goal_assists')->default(0);
                $table->smallInteger('goal_saves')->default(0);
            });
        }

        $renderable = new class
        {
            public function render(): string
            {
                return '<tr>row</tr>';
            }
        };
        View::shouldReceive('make')->andReturn($renderable);

        [$inscriptionA] = $this->createInscriptionAndPayment();
        [$inscriptionB] = $this->createInscriptionAndPayment($this->createTestPlayer());
        [$inscriptionC] = $this->createInscriptionAndPayment($this->createTestPlayer());

        $tournament = Tournament::query()->create([
            'name' => 'Torneo Branches Game',
            'school_id' => $this->school['id'],
        ]);
        $competitionGroup = CompetitionGroup::query()->create([
            'name' => 'Grupo Branches Game',
            'year' => (string) now()->year,
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'category' => '2010-2011',
            'school_id' => $this->school['id'],
        ]);

        $repository = app(GameRepository::class);
        $createdMatch = $repository->createMatchSkill([
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
            'date' => now()->toDateString(),
            'hour' => '11:00',
            'num_match' => '5',
            'place' => 'Cancha E',
            'rival_name' => 'Rival E',
            'final_score' => '{"local":3,"visitor":2}',
            'general_concept' => 'Partido con skills',
            'school_id' => $this->school['id'],
        ], [
            'inscriptions_id' => [$inscriptionA->id],
            'assistance' => [1],
            'titular' => [1],
            'played_approx' => ['30'],
            'position' => ['MID'],
            'goals' => ['2'],
            'goal_assists' => ['1'],
            'goal_saves' => ['0'],
            'red_cards' => ['0'],
            'yellow_cards' => ['1'],
            'qualification' => [''],
            'observation' => ['Buen partido'],
        ]);

        $this->assertNotNull($createdMatch->id);
        $this->assertDatabaseHas('skills_control', [
            'game_id' => $createdMatch->id,
            'inscription_id' => $inscriptionA->id,
            'qualification' => '1',
            'goals' => 2,
            'goal_assists' => 1,
        ]);

        $existingSkill = SkillsControl::query()->create([
            'game_id' => $createdMatch->id,
            'inscription_id' => $inscriptionB->id,
            'assistance' => true,
            'titular' => true,
            'played_approx' => 10,
            'position' => 'DEF',
            'goals' => 0,
            'goal_assists' => 0,
            'goal_saves' => 0,
            'yellow_cards' => 0,
            'red_cards' => 0,
            'qualification' => '2',
            'observation' => 'Inicial',
            'school_id' => $this->school['id'],
        ]);

        $updated = $repository->updateMatchSkill([
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
            'date' => now()->toDateString(),
            'hour' => '11:30',
            'num_match' => '6',
            'place' => 'Cancha F',
            'rival_name' => 'Rival F',
            'final_score' => '{"local":1,"visitor":0}',
            'general_concept' => 'Actualizacion completa',
            'school_id' => $this->school['id'],
        ], [
            'ids' => [$existingSkill->id, 999999, ''],
            'inscriptions_id' => [$inscriptionB->id, $inscriptionC->id, $inscriptionA->id],
            'assistance' => [0, 1, 1],
            'titular' => [0, 1, 0],
            'played_approx' => [20, 40, 50],
            'position' => ['VOL', 'DEL', 'POR'],
            'goals' => [1, 2, 0],
            'goal_assists' => [3, 1, 0],
            'goal_saves' => [0, 0, 4],
            'red_cards' => [0, 0, 0],
            'yellow_cards' => [1, 0, 1],
            'qualification' => ['5', '4', '3'],
            'observation' => ['Actualizado', 'Nuevo por id no existente', 'Nuevo por id vacio'],
        ], $createdMatch->fresh());
        $this->assertTrue($updated);
        $this->assertDatabaseHas('skills_control', [
            'id' => $existingSkill->id,
            'position' => 'VOL',
            'goals' => 1,
            'goal_assists' => 3,
            'observation' => 'Actualizado',
        ]);
        $this->assertGreaterThanOrEqual(4, SkillsControl::query()->where('game_id', $createdMatch->id)->count());

        $loadedData = $repository->loadDataFromFile(SkillsControl::query()->where('game_id', $createdMatch->id)->get());
        $this->assertGreaterThan(0, $loadedData->count);
        $this->assertStringContainsString('<tr>row</tr>', $loadedData->rows);
    }

    public function testGameRepositoryCreateAndUpdateHandleExceptions(): void
    {
        $this->actingAs($this->user);

        $repository = Mockery::mock(GameRepository::class, [new Game()])->makePartial();
        $repository->shouldReceive('logError')->twice();

        $failedCreate = $repository->createMatchSkill([
            'tournament_id' => 99999999,
            'competition_group_id' => 99999999,
            'date' => now()->toDateString(),
            'hour' => '12:00',
            'num_match' => '7',
            'place' => 'Cancha G',
            'rival_name' => 'Rival G',
            'final_score' => '{"local":0,"visitor":0}',
            'general_concept' => 'Debe fallar',
            'school_id' => $this->school['id'],
        ], [
            'inscriptions_id' => [],
        ]);
        $this->assertNull($failedCreate->id);

        $gameMock = Mockery::mock(Game::class);
        $gameMock->shouldReceive('update')->once()->andThrow(new \Exception('forced update error'));

        $failedUpdate = $repository->updateMatchSkill([
            'tournament_id' => 1,
            'competition_group_id' => 1,
            'date' => now()->toDateString(),
            'hour' => '12:30',
            'num_match' => '8',
            'place' => 'Cancha H',
            'rival_name' => 'Rival H',
            'final_score' => '{"local":0,"visitor":1}',
            'general_concept' => 'Debe fallar update',
            'school_id' => $this->school['id'],
        ], [
            'ids' => [],
            'inscriptions_id' => [],
            'assistance' => [],
            'titular' => [],
            'played_approx' => [],
            'position' => [],
            'goals' => [],
            'goal_assists' => [],
            'goal_saves' => [],
            'red_cards' => [],
            'yellow_cards' => [],
            'qualification' => [],
            'observation' => [],
        ], $gameMock);
        $this->assertFalse($failedUpdate);
    }

    public function testUserRepositoryCreateAndUpdateHandleExceptions(): void
    {
        $this->actingAs($this->user);
        $repository = Mockery::mock(UserRepository::class, [new User()])->makePartial();
        $repository->shouldReceive('logError')->twice();

        $duplicateCreateRequest = new class ([
            'name' => 'Duplicate Email User',
            'email' => (string) $this->user->email,
            'password' => 'secret123',
        ], 3) extends FormRequest
        {
            public string $password;

            public function __construct(private array $data, private int $roleId)
            {
                parent::__construct();
                $this->password = (string) data_get($data, 'password');
            }

            public function validated($key = null, $default = null): array
            {
                return $this->data;
            }

            public function input($key = null, $default = null): mixed
            {
                if ($key === 'rol_id') {
                    return $this->roleId;
                }

                return data_get($this->data, $key, $default);
            }
        };

        $failedCreate = $repository->create($duplicateCreateRequest);
        $this->assertNull($failedCreate->id);

        $userToUpdate = User::factory()->create([
            'school_id' => $this->school['id'],
            'name' => 'Usuario Original',
            'email' => fake()->unique()->safeEmail(),
        ]);
        $userToUpdate->profile()->create();
        $userToUpdate->syncRoles([User::INSTRUCTOR]);

        $existingEmailOwner = User::factory()->create([
            'school_id' => $this->school['id'],
            'email' => fake()->unique()->safeEmail(),
        ]);
        $existingEmailOwner->syncRoles([User::INSTRUCTOR]);

        $duplicateUpdateRequest = new class ([
            'name' => 'Debe Mantenerse',
            'email' => (string) $existingEmailOwner->email,
        ], 3) extends FormRequest
        {
            public function __construct(private array $data, private int $roleId)
            {
                parent::__construct();
            }

            public function validated($key = null, $default = null): array
            {
                return $this->data;
            }

            public function input($key = null, $default = null): mixed
            {
                if ($key === 'rol_id') {
                    return $this->roleId;
                }

                return data_get($this->data, $key, $default);
            }
        };

        $originalName = $userToUpdate->name;
        $repository->update($userToUpdate, $duplicateUpdateRequest);
        $this->assertSame($originalName, $userToUpdate->fresh()->name);
    }

    public function testAssistRepositorySearchCreateUpdateAndBulkBranches(): void
    {
        $this->actingAs($this->user);
        $repository = app(AssistRepository::class);

        $serviceMock = Mockery::mock(\App\Service\Assist\AssistService::class);
        $serviceMock->shouldReceive('generateTable')
            ->once()
            ->andReturnUsing(function ($assists, TrainingGroup $group, array $data, bool $deleted): array {
                $this->assertInstanceOf(Builder::class, $assists);
                $this->assertFalse($deleted);
                $this->assertSame((int) now()->year, (int) $data['year']);
                return ['count' => 0, 'table' => '', 'group_name' => $group->full_schedule_group, 'url_print' => '', 'url_print_excel' => ''];
            });

        $reflection = new \ReflectionClass($repository);
        $serviceProperty = $reflection->getProperty('service');
        $serviceProperty->setAccessible(true);
        $serviceProperty->setValue($repository, $serviceMock);

        $group = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();
        $searchResult = $repository->search([
            'training_group_id' => $group->id,
            'month' => 1,
            'year' => 1999,
        ]);
        $this->assertIsArray($searchResult);

        $serviceMockDeleted = Mockery::mock(\App\Service\Assist\AssistService::class);
        $serviceMockDeleted->shouldReceive('generateTable')
            ->once()
            ->andReturnUsing(function ($assists, TrainingGroup $groupArg, array $data, bool $deleted) use ($group): array {
                $this->assertTrue($deleted);
                $this->assertSame($group->id, $groupArg->id);
                $this->assertSame((string) (now()->year - 1), (string) $data['year']);
                return ['count' => 0, 'table' => '', 'group_name' => '', 'url_print' => '', 'url_print_excel' => ''];
            });
        $serviceProperty->setValue($repository, $serviceMockDeleted);

        $group->delete();
        $deletedResult = $repository->search([
            'training_group_id' => $group->id,
            'month' => 1,
            'year' => now()->year - 1,
        ], true);
        $this->assertIsArray($deletedResult);

        $groupA = TrainingGroup::query()->create([
            'name' => 'Assist A',
            'stage' => 'Stage',
            'year' => (string) now()->year,
            'days' => 'lunes,miercoles',
            'schedules' => '08:00 - 09:00',
            'school_id' => $this->school['id'],
            'year_active' => now()->year,
        ]);
        $groupB = TrainingGroup::query()->create([
            'name' => 'Assist B',
            'stage' => 'Stage',
            'year' => (string) now()->year,
            'days' => 'martes,jueves',
            'schedules' => '10:00 - 11:00',
            'school_id' => $this->school['id'],
            'year_active' => now()->year,
        ]);

        $firstGroupId = TrainingGroup::query()->orderBy('id')
            ->firstWhere('school_id', $this->school['id'])->id;
        $createEarlyReturn = $repository->create([
            'training_group_id' => $firstGroupId,
            'month' => 1,
        ]);
        $this->assertSame([], $createEarlyReturn);

        $player = $this->createTestPlayer();
        $inscription = Inscription::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => now()->year,
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'category' => '2010-2011',
            'training_group_id' => $groupB->id,
            'competition_group_id' => null,
        ]);

        Assist::query()->create([
            'training_group_id' => $groupA->id,
            'inscription_id' => $inscription->id,
            'year' => now()->year,
            'month' => '1',
            'school_id' => $this->school['id'],
        ]);

        $createBranchEmpty = $repository->create([
            'training_group_id' => $groupA->id,
            'month' => 1,
        ]);
        $this->assertSame([], $createBranchEmpty);

        $createBranchBulk = $repository->create([
            'training_group_id' => $groupB->id,
            'month' => 1,
        ]);
        $this->assertSame([], $createBranchBulk);
        $this->assertDatabaseHas('assists', [
            'training_group_id' => $groupB->id,
            'inscription_id' => $inscription->id,
            'month' => '1',
        ]);

        $assist = Assist::query()->firstOrFail();
        $updated = $repository->update($assist, [
            'assistance_one' => 'as',
            'attendance_date' => '2026-03-04',
            'observations' => 'Llego puntual',
        ]);
        $this->assertTrue($updated);
        $this->assertSame('as', $assist->fresh()->assistance_one);
        $this->assertSame('Llego puntual', data_get((array) $assist->fresh()->observations, '2026-03-04'));

        $assistWithObject = Assist::query()->create([
            'training_group_id' => $groupB->id,
            'inscription_id' => $inscription->id,
            'year' => now()->year,
            'month' => '2',
            'school_id' => $this->school['id'],
            'observations' => (object) ['2026-03-01' => 'Previo'],
        ]);
        $updatedWithObject = $repository->update($assistWithObject, [
            'attendance_date' => '2026-03-05',
            'observations' => 'Nuevo',
        ]);
        $this->assertTrue($updatedWithObject);
        $this->assertSame('Previo', data_get((array) $assistWithObject->fresh()->observations, '2026-03-01'));
        $this->assertSame('Nuevo', data_get((array) $assistWithObject->fresh()->observations, '2026-03-05'));

        $playerBulk = $this->createTestPlayer();
        $inscriptionBulk = Inscription::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $playerBulk->id,
            'unique_code' => $playerBulk->unique_code,
            'year' => now()->year,
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'category' => '2010-2011',
            'training_group_id' => $groupB->id,
            'competition_group_id' => null,
        ]);
        Assist::query()->create([
            'training_group_id' => $groupA->id,
            'inscription_id' => $inscriptionBulk->id,
            'year' => now()->year,
            'month' => '2',
            'school_id' => $this->school['id'],
        ]);

        AssistRepository::createAssistBulk(
            collect([$inscriptionBulk->id]),
            Assist::query()->where([
                ['training_group_id', $groupB->id],
                ['month', '2'],
                ['year', now()->year],
                ['school_id', $this->school['id']],
            ]),
            [
                'year' => now()->year,
                'month' => '2',
                'training_group_id' => $groupB->id,
            ],
            (int) $this->school['id']
        );
        $this->assertDatabaseHas('assists', [
            'training_group_id' => $groupB->id,
            'inscription_id' => $inscriptionBulk->id,
            'month' => '2',
        ]);
        $this->assertDatabaseMissing('assists', [
            'training_group_id' => $groupA->id,
            'inscription_id' => $inscriptionBulk->id,
            'month' => '2',
        ]);

        AssistRepository::createAssistBulk(collect(), Assist::query(), [
            'year' => now()->year,
            'month' => 1,
            'training_group_id' => $groupB->id,
        ], (int) $this->school['id']);
        $this->assertTrue(true);
    }

    public function testAssistRepositoryErrorBranches(): void
    {
        $this->actingAs($this->user);

        $createRepository = Mockery::mock(AssistRepository::class, [new Assist()])->makePartial();
        $createRepository->shouldReceive('logError')->once();
        $createResult = $createRepository->create(['training_group_id' => 1]);
        $this->assertSame([], $createResult);

        $trainingGroup = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();
        $inscription = Inscription::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $this->createTestPlayer()->id,
            'unique_code' => 'RC-ERR-' . fake()->unique()->numberBetween(1000, 9999),
            'year' => now()->year,
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'category' => '2010-2011',
            'training_group_id' => $trainingGroup->id,
            'competition_group_id' => null,
        ]);
        $assist = $inscription->assistance()
            ->where('training_group_id', $trainingGroup->id)
            ->where('year', now()->year)
            ->where('month', '1')
            ->firstOrFail();

        $upsertRepository = Mockery::mock(AssistRepository::class, [new Assist()])->makePartial();
        $upsertRepository->shouldReceive('logError')->once();
        $failedUpsert = $upsertRepository->upsert(AssistDTO::fromArray([
            'school_id' => $this->school['id'],
            'training_group_id' => $assist->training_group_id,
            'inscription_id' => $assist->inscription_id,
            'month' => 1,
            'year' => (int) now()->year,
            'column' => 'invalid_column',
            'value' => 1,
            'attendance_date' => '2026-03-04',
            'observations' => 'error expected',
        ]));
        $this->assertFalse($failedUpsert);

        $updateRepository = Mockery::mock(AssistRepository::class, [new Assist()])->makePartial();
        $updateRepository->shouldReceive('logError')->once();
        $assistMock = Mockery::mock(new Assist())->makePartial();
        $assistMock->shouldReceive('update')->once()->andThrow(new \Exception('forced update error'));
        $failedUpdate = $updateRepository->update($assistMock, [
            'assistance_one' => 'as',
        ]);
        $this->assertFalse($failedUpdate);
    }

    public function testTopicNotificationRepositoryAllBranches(): void
    {
        $this->actingAs($this->user);
        $repository = app(TopicNotificationRepository::class);

        $topicId = DB::table('topic_notifications')->insertGetId([
            'school_id' => $this->school['id'],
            'topic' => 'general-' . $this->school['id'],
            'type' => 'GENERAL',
            'priority' => 'NORMAL',
            'title' => 'Aviso',
            'body' => 'Mensaje',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $topic = TopicNotification::query()->findOrFail($topicId);
        $mockPlayer = Mockery::mock();
        $mockPlayer->notifications = collect([$topic]);
        $mockPlayer->shouldReceive('load')->times(2)->with('notifications');
        app('request')->setUserResolver(fn() => $mockPlayer);

        $playerNotifications = $repository->getPlayerNotifications();
        $this->assertGreaterThan(0, $playerNotifications->count());

        $singleNotification = $repository->getPlayerNotification($topic->id);
        $this->assertNotNull($singleNotification);
        $this->assertSame($topic->id, $singleNotification->id);

        $allQuery = $repository->getAll();
        $this->assertInstanceOf(Builder::class, $allQuery);
        $this->assertTrue($allQuery->where('id', $topic->id)->exists());

        $singleTopicQuery = $repository->getNotificationByTopic([
            'school_id' => $this->school['id'],
            'topic' => 'general-' . $this->school['id'],
        ]);
        $this->assertStringContainsString('topic', $singleTopicQuery->toSql());

        $arrayTopicQuery = $repository->getNotificationByTopic([
            'school_id' => $this->school['id'],
            'topic' => ['general-' . $this->school['id'], 'team-' . $this->school['id']],
        ]);
        $this->assertStringContainsString('in', strtolower($arrayTopicQuery->toSql()));

        $topics = $repository->getTopics();
        $this->assertCount(4, $topics);

        $this->expectException(ModelNotFoundException::class);
        $relation = Mockery::mock();
        $relation->shouldReceive('whereKey')->once()->with(9999999)->andReturnSelf();
        $relation->shouldReceive('first')->once()->andReturn(null);

        $playerForException = Mockery::mock();
        $playerForException->shouldReceive('notifications')->andReturn($relation);

        app('request')->setUserResolver(fn() => $playerForException);
        app('request')->merge(['notificationId' => 9999999]);
        $repository->markRead();
    }

    public function testUniformRequestRepositoryRemainingBranchesAndErrors(): void
    {
        $this->actingAs($this->user);
        $player = $this->createTestPlayer();
        app('request')->setUserResolver(fn() => $player);

        $repository = app(UniformRequestRepository::class);
        $stored = $repository->store([
            'type' => 'BALL',
            'quantity' => 2,
            'size' => 'L',
            'additional_notes' => null,
        ]);
        $this->assertNotEmpty($stored);

        $playerRequests = $repository->uniformRequestPlayer();
        $this->assertGreaterThan(0, $playerRequests->count());

        $cancelled = $repository->cancel($stored);
        $this->assertTrue($cancelled);
        $this->assertSame('Cancelada por el usuario.', $stored->fresh()->additional_notes);

        $trainingGroup = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();
        Inscription::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => now()->year,
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'category' => '2010-2011',
            'training_group_id' => $trainingGroup->id,
            'competition_group_id' => null,
        ]);

        $queryTableResponse = $repository->queryTable();
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $queryTableResponse);

        $dataTableEngine = Mockery::mock();
        $dataTableEngine->shouldReceive('filterColumn')
            ->twice()
            ->andReturnUsing(function (string $column, callable $callback) use ($dataTableEngine) {
                if ($column === 'created_at') {
                    $query = Mockery::mock();
                    $query->shouldReceive('whereDate')->once()->with('uniform_request.created_at', '2026-03-04');
                    $callback($query, '2026-03-04');
                }

                if ($column === 'full_names') {
                    $query = Mockery::mock();
                    $query->shouldReceive('whereRaw')
                        ->once()
                        ->with("CONCAT(players.names, ' ', players.last_names) like ?", ['%john%']);
                    $callback($query, 'john');
                }

                return $dataTableEngine;
            });
        $dataTableEngine->shouldReceive('toJson')->once()->andReturn(response()->json(['ok' => true]));

        $dataTablesFacade = Mockery::mock();
        $dataTablesFacade->shouldReceive('of')->once()->andReturn($dataTableEngine);
        $this->app->instance('datatables', $dataTablesFacade);

        $callbackCoverageResponse = $repository->queryTable();
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $callbackCoverageResponse);

        $errorRepository = Mockery::mock(UniformRequestRepository::class)->makePartial();
        $errorRepository->shouldReceive('logError')->once();
        $failedStore = $errorRepository->store([
            'type' => 'INVALID_ENUM',
            'quantity' => 1,
            'size' => 'M',
            'additional_notes' => '',
        ]);
        $this->assertSame([], $failedStore);

        $cancelErrorRepository = Mockery::mock(UniformRequestRepository::class)->makePartial();
        $cancelErrorRepository->shouldReceive('logError')->once();
        $uniformRequestMock = Mockery::mock(new \App\Models\UniformRequest())->makePartial();
        $uniformRequestMock->status = 'PENDING';
        $uniformRequestMock->additional_notes = 'X';
        $uniformRequestMock->shouldReceive('save')->once()->andThrow(new \Exception('forced cancel error'));
        $failedCancel = $cancelErrorRepository->cancel($uniformRequestMock);
        $this->assertFalse($failedCancel);
    }

    private function createTestPlayer(): Player
    {
        return Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => 'RC-' . fake()->unique()->numberBetween(1000, 9999),
        ]);
    }

    private function createInscriptionAndPayment(?Player $player = null, ?TrainingGroup $trainingGroup = null): array
    {
        $player = $player ?: $this->createTestPlayer();
        $trainingGroup = $trainingGroup ?: TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();

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
