<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assist;
use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Models\Player;
use App\Models\School;
use App\Models\Setting;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use App\Models\TrainingSessionDetail;
use App\Service\API\Instructor\AssistsService;
use App\Service\API\Instructor\TrainingGroupsService;
use App\Service\Assist\AssistService;
use App\Service\Groups\TrainingGroupYearFilter;
use App\Service\Notification\TopicService;
use App\Service\Payment\PaymentExportService;
use App\Service\PaymentAmountResolver;
use App\Service\Player\PlayerExportService;
use App\Service\ReportService;
use App\Service\SharedService;
use App\Service\StopWatch;
use App\Service\TrainigSession\TrainingSessionExportService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Mockery;
use Tests\TestCase;

final class ServicesCoverageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_stop_watch_start_stop_and_elapsed_time(): void
    {
        $stopWatch = new StopWatch;

        $this->assertTrue($stopWatch->start());
        $this->assertFalse($stopWatch->start());
        $stopWatch->stop();
        $this->assertStringEndsWith('s', $stopWatch->getTimeElapsed());

        $fresh = new StopWatch;
        $this->assertFalse($fresh->stop());
        $this->assertSame('', $fresh->getTimeElapsed());
    }

    public function test_assists_service_get_assists(): void
    {
        $this->actingAs($this->user);
        $trainingGroup = $this->createTrainingGroup('Assists API Group');
        $inscription = $this->createInscription($this->makePlayer(), $trainingGroup);

        $service = new AssistsService;
        $assists = $service->getAssists([
            'training_group_id' => $trainingGroup->id,
            'month' => '1',
            'year' => now()->year,
        ]);

        $this->assertInstanceOf(Collection::class, $assists);
        $this->assertGreaterThanOrEqual(1, $assists->count());
        $this->assertTrue($assists->contains(fn (Assist $assist) => (int) $assist->inscription_id === (int) $inscription->id));
    }

    public function test_training_groups_service_get_groups_and_get_group(): void
    {
        $this->actingAs($this->user);
        $group = $this->createTrainingGroup('Searchable Group');
        $this->createInscription($this->makePlayer(), $group);

        request()->merge([
            'q' => 'Searchable',
            'limit' => 10,
            'page' => 1,
        ]);

        $service = new TrainingGroupsService;
        $groups = $service->getGroups();

        $this->assertTrue($groups->contains(fn (TrainingGroup $item) => $item->id === $group->id));
        $this->assertSame($group->id, $service->getGroup($group->id)->id);

        $this->expectException(ModelNotFoundException::class);
        $service->getGroup(999999);
    }

    public function test_training_group_year_filter_keeps_current_and_past_active_groups(): void
    {
        $groups = collect([
            (object) ['id' => 1, 'year_active' => now()->year - 1],
            (object) ['id' => 2, 'year_active' => now()->year],
            (object) ['id' => 3, 'year_active' => now()->year + 1],
        ]);

        $this->assertSame([1, 2], TrainingGroupYearFilter::activeForCurrentYear($groups)->pluck('id')->values()->all());
    }

    public function test_report_service_calls_stored_procedures_with_expected_parameters(): void
    {
        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_group_payment_report(?, ?, ?)', [2026, 7, 3])
            ->andReturn([(object) ['total' => 1]]);
        $this->assertSame(1, ReportService::paymentByGroupReport(2026, 7, 3)->first()->total);

        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_general_payment_report(?, ?)', [2026, 7])
            ->andReturn([(object) ['total' => 2]]);
        $this->assertSame(2, ReportService::generalReport(2026, 7)->first()->total);

        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_monthly_payment_report(?, ?, ?)', [2026, 7, null])
            ->andReturn([(object) ['total' => 3]]);
        $this->assertSame(3, ReportService::monthlyReport(2026, 7)->first()->total);

        DB::shouldReceive('select')
            ->once()
            ->with('CALL sp_get_assists_report_with_percentages(?, ?, ?, ?)', [2026, 5, null, 7])
            ->andReturn([(object) ['total' => 4]]);
        $this->assertSame(4, ReportService::assistsPercentagesReport(2026, 5, null, 7)->first()->total);
    }

    public function test_topic_service_generate_topic_and_player_topics_and_school_topics(): void
    {
        $this->actingAs($this->user);
        $group = $this->createTrainingGroup('Topic Group');
        $player = $this->makePlayer();
        $inscription = $this->createInscription($player, $group);

        $tournament = Tournament::query()->create([
            'name' => 'Topic Tournament',
            'school_id' => $this->school['id'],
        ]);
        $competitionGroup = CompetitionGroup::query()->create([
            'name' => 'Topic Competition',
            'year' => (string) now()->year,
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'category' => '2010-2011',
            'school_id' => $this->school['id'],
        ]);
        $competitionGroup->inscriptions()->attach($inscription->id);

        $this->assertSame('general-school-test', TopicService::generateTopic('General', 'school-test'));

        $topics = TopicService::generatePlayerTopics($player->fresh());
        $this->assertGreaterThanOrEqual(4, count($topics));
        $this->assertTrue(collect($topics)->contains(fn (string $topic) => str_contains($topic, 'general')));

        $topicsBySchool = TopicService::generateTopicBySchool($this->user->fresh());
        $this->assertCount(4, $topicsBySchool);
        $this->assertNotEmpty($topicsBySchool[0]);
        $this->assertNotEmpty($topicsBySchool[1]);
        $this->assertNotEmpty($topicsBySchool[2]);
        $this->assertNotEmpty($topicsBySchool[3]);
    }

    public function test_assist_service_generate_table(): void
    {
        $this->actingAs($this->user);
        $group = $this->createTrainingGroup('Assist Service Group');
        $inscription = $this->createInscription($this->makePlayer(), $group);

        $renderable = new class
        {
            public function render(): string
            {
                return '<html>rendered</html>';
            }
        };
        View::shouldReceive('make')->andReturn($renderable);

        $service = new AssistService;
        $result = $service->generateTable(
            Assist::query()->where('training_group_id', $group->id),
            $group->fresh(),
            [
                'training_group_id' => $group->id,
                'month' => 1,
                'year' => now()->year,
            ]
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('table', $result);
        $this->assertArrayHasKey('url_print', $result);
        $this->assertArrayHasKey('url_print_excel', $result);
    }

    public function test_payment_amount_resolver_falls_back_to_regular_monthly_amount_when_brother_setting_is_empty(): void
    {
        $this->actingAs($this->user);

        $school = School::query()->findOrFail($this->school['id']);
        $school->settingsValues()->where('setting_key', Setting::MONTHLY_PAYMENT)->update(['value' => '51000']);
        $school->settingsValues()->where('setting_key', Setting::BROTHER_MONTHLY_PAYMENT)->update(['value' => '']);

        $player = Player::factory()->create();
        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => now()->year,
            'training_group_id' => 1,
            'competition_group_id' => null,
            'start_date' => now()->format('Y-m-d'),
            'category' => categoriesName(now()->subYears(10)->year),
            'school_id' => $school->id,
            'brother_payment' => true,
        ]);

        $resolver = app(PaymentAmountResolver::class);

        $this->assertSame(51000, $resolver->monthlyAmountForInscription($inscription->fresh()));
    }

    public function test_payment_export_service_pdf_methods(): void
    {
        $this->actingAs($this->user);

        $trainingGroup = $this->createTrainingGroup('Payment Export Group');
        $tournament = Tournament::query()->create([
            'name' => 'Payment Tournament',
            'school_id' => $this->school['id'],
        ]);
        $competitionGroup = CompetitionGroup::query()->create([
            'name' => 'Payment Competition',
            'year' => (string) now()->year,
            'tournament_id' => $tournament->id,
            'user_id' => $this->user->id,
            'category' => '2010-2011',
            'school_id' => $this->school['id'],
        ]);

        $streamMock = Mockery::mock(PaymentExportService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $streamMock->shouldReceive('setConfigurationMpdf')->once();
        $streamMock->shouldReceive('createPDF')->once();
        $streamMock->shouldReceive('stream')->once()->andReturn('streamed');
        $requestStream = new class((int) $trainingGroup->id)
        {
            public function __construct(public int $training_group_id) {}

            public function input(string $key, $default = null)
            {
                return $default;
            }
        };
        $this->assertSame('streamed', $streamMock->paymentsPdfByGroup(collect(), $requestStream, true));

        $outputMock = Mockery::mock(PaymentExportService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $outputMock->shouldReceive('setConfigurationMpdf')->twice();
        $outputMock->shouldReceive('createPDF')->twice();
        $outputMock->shouldReceive('output')->twice()->andReturn('output');
        $requestOutput = new class(0)
        {
            public function __construct(public int $training_group_id) {}

            public function input(string $key, $default = null)
            {
                return $default;
            }
        };
        $this->assertSame('output', $outputMock->paymentsPdfByGroup(collect(), $requestOutput, false));
        $this->assertSame('output', $outputMock->tournamentPayoutsPdfByGroup(collect(), [
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
        ], false));
    }

    public function test_training_session_export_service_pdf_method(): void
    {
        $this->actingAs($this->user);
        $group = $this->createTrainingGroup('Session Export Group');
        $session = TrainingSession::query()->create([
            'school_id' => $this->school['id'],
            'user_id' => $this->user->id,
            'training_group_id' => $group->id,
            'year' => now()->year,
            'period' => 'P1',
            'session' => 'S1',
            'date' => now()->toDateString(),
            'hour' => '08:00',
            'training_ground' => 'A',
        ]);
        TrainingSessionDetail::query()->create([
            'training_session_id' => $session->id,
            'task_number' => 1,
            'task_name' => 'Task1',
        ]);

        $streamMock = Mockery::mock(TrainingSessionExportService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $streamMock->shouldReceive('setConfigurationMpdf')->once();
        $streamMock->shouldReceive('createPDF')->once();
        $streamMock->shouldReceive('stream')->once()->andReturn('session-stream');
        $this->assertSame('session-stream', $streamMock->exportSessionPDF($session->id, true));

        $outputMock = Mockery::mock(TrainingSessionExportService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $outputMock->shouldReceive('setConfigurationMpdf')->once();
        $outputMock->shouldReceive('createPDF')->once();
        $outputMock->shouldReceive('output')->once()->andReturn('session-output');
        $this->assertSame('session-output', $outputMock->exportSessionPDF($session->id, false));
    }

    public function test_player_export_service_get_excel_and_load_class_days_and_pdf(): void
    {
        $this->actingAs($this->user);
        $group = $this->createTrainingGroup('Player Export Group');

        $enabledPlayer = $this->makePlayer();
        $inscription = $this->createInscription($enabledPlayer, $group);
        $complementaryGroup = $this->createTrainingGroup('Player Export Complementary');
        $complementaryGroup->update([
            'days' => 'martes',
            'is_complementary' => true,
        ]);
        $inscription->update(['complementary_group_id' => $complementaryGroup->id]);
        $complementaryAssist = Assist::query()->updateOrCreate([
            'inscription_id' => $inscription->id,
            'training_group_id' => $complementaryGroup->id,
            'year' => now()->year,
            'month' => now()->month,
        ], [
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'training_group_id' => $complementaryGroup->id,
            'year' => now()->year,
            'month' => now()->month,
            'assistance_one' => 1,
        ]);

        $this->makePlayer();

        $service = new PlayerExportService;
        $excel = $service->getExcel();
        $this->assertArrayHasKey('enabled', $excel->toArray());
        $this->assertArrayHasKey('disabled', $excel->toArray());
        $this->assertGreaterThanOrEqual(1, $excel['enabled']->count());

        $playerWithRelations = Player::query()->with([
            'inscriptions' => fn ($q) => $q->with(
                'trainingGroup',
                'complementaryGroup',
                'assistance.trainingGroup'
            ),
        ])->findOrFail($enabledPlayer->id);
        PlayerExportService::loadClassDays($playerWithRelations);
        $loadedComplementaryAssist = $playerWithRelations->inscriptions
            ->first()
            ->assistance
            ->firstWhere('id', $complementaryAssist->id);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $loadedComplementaryAssist->classDays);
        $this->assertSame('Grupo complementario', $loadedComplementaryAssist->groupLabel);
        $this->assertSame('Player Export Complementary', $loadedComplementaryAssist->groupName);

        $streamMock = Mockery::mock(PlayerExportService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $streamMock->shouldReceive('setConfigurationMpdf')->once();
        $streamMock->shouldReceive('createPDF')->once();
        $streamMock->shouldReceive('stream')->once()->andReturn('player-stream');
        $this->assertSame('player-stream', $streamMock->makePDFPlayer($enabledPlayer->fresh(), true));
    }

    public function test_player_export_excel_excludes_payments_from_other_schools_with_the_same_unique_code(): void
    {
        $this->actingAs($this->user);
        $year = now()->year;
        $uniqueCode = 'SHARED-EXPORT-001';
        $localGroup = $this->createTrainingGroup('Local Export Group');
        $localPlayer = Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => $uniqueCode,
        ]);
        $localInscription = $this->createInscription($localPlayer, $localGroup);
        $localPayment = $localInscription->payment()->firstOrFail();

        $otherSchool = $this->createSchool();
        $otherGroup = TrainingGroup::query()
            ->where('school_id', $otherSchool['id'])
            ->firstOrFail();
        $otherPlayer = Player::factory()->create([
            'school_id' => $otherSchool['id'],
            'unique_code' => 'OTHER-EXPORT-001',
        ]);
        $otherInscription = Inscription::query()->create([
            'school_id' => $otherSchool['id'],
            'player_id' => $otherPlayer->id,
            'unique_code' => $otherPlayer->unique_code,
            'year' => $year,
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'category' => '2010-2011',
            'training_group_id' => $otherGroup->id,
            'competition_group_id' => null,
        ]);
        $otherInscription->payment()->firstOrFail()->update(['unique_code' => $uniqueCode]);

        $exportedPlayer = (new PlayerExportService)
            ->getExcel($year)['enabled']
            ->firstWhere('id', $localPlayer->id);

        $this->assertSame([$localPayment->id], $exportedPlayer->payments->pluck('id')->all());
    }

    public function test_player_export_excel_classifies_players_using_only_current_school_inscriptions(): void
    {
        $this->actingAs($this->user);
        $year = now()->year;
        $localPlayer = Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => 'LOCAL-WITH-FOREIGN-INSCRIPTION',
        ]);

        $otherSchool = $this->createSchool();
        $otherGroup = TrainingGroup::query()
            ->where('school_id', $otherSchool['id'])
            ->firstOrFail();
        Inscription::query()->create([
            'school_id' => $otherSchool['id'],
            'player_id' => $localPlayer->id,
            'unique_code' => 'FOREIGN-INSCRIPTION-001',
            'year' => $year,
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'category' => '2010-2011',
            'training_group_id' => $otherGroup->id,
            'competition_group_id' => null,
        ]);

        $export = (new PlayerExportService)->getExcel($year);
        $enabledPlayer = $export['enabled']->firstWhere('id', $localPlayer->id);
        $disabledPlayer = $export['disabled']->firstWhere('id', $localPlayer->id);
        $exportedPlayer = $enabledPlayer ?: $disabledPlayer;

        $this->assertSame([
            'sheet' => 'disabled',
            'inscription_ids' => [],
        ], [
            'sheet' => $enabledPlayer ? 'enabled' : 'disabled',
            'inscription_ids' => $exportedPlayer->inscriptions->pluck('id')->all(),
        ]);
    }

    public function test_shared_service_assign_training_group_branches(): void
    {
        $this->actingAs($this->user);
        $origin = $this->createTrainingGroup('Origin Shared');
        $target = $this->createTrainingGroup('Target Shared');
        $inscription = $this->createInscription($this->makePlayer(), $origin);

        $requestWithTarget = new class((int) $target->id)
        {
            public function __construct(private int $target) {}

            public function input(string $key, $default = null)
            {
                if ($key === 'target_group') {
                    return $this->target;
                }
                if ($key === 'origin_group') {
                    return null;
                }

                return $default;
            }
        };

        $service = new SharedService(app(PaymentAmountResolver::class));
        $updated = $service->assignTrainingGroup($inscription->id, $requestWithTarget);
        $this->assertTrue($updated);
        $this->assertSame($target->id, $inscription->fresh()->training_group_id);

        $requestWithoutTarget = new class
        {
            public function input(string $key, $default = null)
            {
                return null;
            }
        };
        $this->assertFalse($service->assignTrainingGroup($inscription->id, $requestWithoutTarget));
        $serviceError = Mockery::mock(SharedService::class, [app(PaymentAmountResolver::class)])->makePartial();
        $serviceError->shouldReceive('logError')->once();
        $this->assertFalse($serviceError->assignTrainingGroup(999999, $requestWithTarget));
    }

    private function makePlayer(): Player
    {
        return Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => 'RC-'.fake()->unique()->numberBetween(1000, 9999),
            'category' => '2010-2011',
        ]);
    }

    private function createTrainingGroup(string $name): TrainingGroup
    {
        return TrainingGroup::query()->create([
            'name' => $name,
            'stage' => 'Stage A',
            'year' => (string) now()->year,
            'days' => 'lunes,miercoles',
            'schedules' => '08:00 - 09:00',
            'school_id' => $this->school['id'],
            'year_active' => now()->year,
        ]);
    }

    private function createInscription(Player $player, TrainingGroup $group): Inscription
    {
        return Inscription::query()->create([
            'school_id' => $this->school['id'],
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'year' => now()->year,
            'start_date' => now()->startOfYear()->format('Y-m-d'),
            'category' => '2010-2011',
            'training_group_id' => $group->id,
            'competition_group_id' => null,
        ]);
    }
}
