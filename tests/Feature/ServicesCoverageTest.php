<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assist;
use App\Models\CompetitionGroup;
use App\Models\Inscription;
use App\Models\Player;
use App\Models\Tournament;
use App\Models\TrainingGroup;
use App\Models\TrainingSession;
use App\Models\TrainingSessionDetail;
use App\Service\API\Instructor\AssistsService;
use App\Service\API\Instructor\TrainingGroupsService;
use App\Service\Assist\AssistService;
use App\Service\Notification\TopicService;
use App\Service\Payment\PaymentExportService;
use App\Service\Player\PlayerExportService;
use App\Service\SharedService;
use App\Service\StopWatch;
use App\Service\TrainigSession\TrainingSessionExportService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
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

    public function testStopWatchStartStopAndElapsedTime(): void
    {
        $stopWatch = new StopWatch();

        $this->assertTrue($stopWatch->start());
        $this->assertFalse($stopWatch->start());
        $stopWatch->stop();
        $this->assertStringEndsWith('s', $stopWatch->getTimeElapsed());

        $fresh = new StopWatch();
        $this->assertFalse($fresh->stop());
        $this->assertSame('', $fresh->getTimeElapsed());
    }

    public function testAssistsServiceGetAssists(): void
    {
        $this->actingAs($this->user);
        $trainingGroup = $this->createTrainingGroup('Assists API Group');
        $inscription = $this->createInscription($this->makePlayer(), $trainingGroup);

        Assist::query()->create([
            'training_group_id' => $trainingGroup->id,
            'inscription_id' => $inscription->id,
            'year' => now()->year,
            'month' => '1',
            'school_id' => $this->school['id'],
        ]);

        $service = new AssistsService();
        $assists = $service->getAssists([
            'training_group_id' => $trainingGroup->id,
            'month' => '1',
            'year' => now()->year,
        ]);

        $this->assertInstanceOf(Collection::class, $assists);
        $this->assertGreaterThanOrEqual(1, $assists->count());
        $this->assertTrue($assists->contains(fn(Assist $assist) => (int) $assist->inscription_id === (int) $inscription->id));
    }

    public function testTrainingGroupsServiceGetGroupsAndGetGroup(): void
    {
        $this->actingAs($this->user);
        $group = $this->createTrainingGroup('Searchable Group');
        $this->createInscription($this->makePlayer(), $group);

        request()->merge([
            'q' => 'Searchable',
            'limit' => 10,
            'page' => 1,
        ]);

        $service = new TrainingGroupsService();
        $groups = $service->getGroups();

        $this->assertTrue($groups->contains(fn(TrainingGroup $item) => $item->id === $group->id));
        $this->assertSame($group->id, $service->getGroup($group->id)->id);

        $this->expectException(ModelNotFoundException::class);
        $service->getGroup(999999);
    }

    public function testTopicServiceGenerateTopicAndPlayerTopicsAndSchoolTopics(): void
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
        $this->assertTrue(collect($topics)->contains(fn(string $topic) => str_contains($topic, 'general')));

        $topicsBySchool = TopicService::generateTopicBySchool($this->user->fresh());
        $this->assertCount(4, $topicsBySchool);
        $this->assertNotEmpty($topicsBySchool[0]);
        $this->assertNotEmpty($topicsBySchool[1]);
        $this->assertNotEmpty($topicsBySchool[2]);
        $this->assertNotEmpty($topicsBySchool[3]);
    }

    public function testAssistServiceGenerateTable(): void
    {
        $this->actingAs($this->user);
        $group = $this->createTrainingGroup('Assist Service Group');
        $inscription = $this->createInscription($this->makePlayer(), $group);
        Assist::query()->create([
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => now()->year,
            'month' => '1',
            'school_id' => $this->school['id'],
        ]);

        $renderable = new class
        {
            public function render(): string
            {
                return '<html>rendered</html>';
            }
        };
        View::shouldReceive('make')->andReturn($renderable);

        $service = new AssistService();
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

    public function testPaymentExportServicePdfMethods(): void
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
        $requestStream = new class ((int) $trainingGroup->id)
        {
            public function __construct(public int $training_group_id)
            {
            }
        };
        $this->assertSame('streamed', $streamMock->paymentsPdfByGroup(collect(), $requestStream, true));

        $outputMock = Mockery::mock(PaymentExportService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $outputMock->shouldReceive('setConfigurationMpdf')->twice();
        $outputMock->shouldReceive('createPDF')->twice();
        $outputMock->shouldReceive('output')->twice()->andReturn('output');
        $requestOutput = new class (0)
        {
            public function __construct(public int $training_group_id)
            {
            }
        };
        $this->assertSame('output', $outputMock->paymentsPdfByGroup(collect(), $requestOutput, false));
        $this->assertSame('output', $outputMock->tournamentPayoutsPdfByGroup(collect(), [
            'tournament_id' => $tournament->id,
            'competition_group_id' => $competitionGroup->id,
        ], false));
    }

    public function testTrainingSessionExportServicePdfMethod(): void
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

    public function testPlayerExportServiceGetExcelAndLoadClassDaysAndPdf(): void
    {
        $this->actingAs($this->user);
        $group = $this->createTrainingGroup('Player Export Group');

        $enabledPlayer = $this->makePlayer();
        $inscription = $this->createInscription($enabledPlayer, $group);
        Assist::query()->create([
            'training_group_id' => $group->id,
            'inscription_id' => $inscription->id,
            'year' => now()->year,
            'month' => '1',
            'school_id' => $this->school['id'],
        ]);

        $this->makePlayer();

        $service = new PlayerExportService();
        $excel = $service->getExcel();
        $this->assertArrayHasKey('enabled', $excel->toArray());
        $this->assertArrayHasKey('disabled', $excel->toArray());
        $this->assertGreaterThanOrEqual(1, $excel['enabled']->count());

        $playerWithRelations = Player::query()->with([
            'inscriptions' => fn($q) => $q->with('trainingGroup', 'assistance'),
        ])->findOrFail($enabledPlayer->id);
        PlayerExportService::loadClassDays($playerWithRelations);
        $this->assertInstanceOf(
            \Illuminate\Support\Collection::class,
            $playerWithRelations->inscriptions->first()->assistance->first()->classDays
        );

        $streamMock = Mockery::mock(PlayerExportService::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $streamMock->shouldReceive('setConfigurationMpdf')->once();
        $streamMock->shouldReceive('createPDF')->once();
        $streamMock->shouldReceive('stream')->once()->andReturn('player-stream');
        $this->assertSame('player-stream', $streamMock->makePDFPlayer($enabledPlayer->fresh(), true));
    }

    public function testSharedServiceAssignTrainingGroupBranches(): void
    {
        $this->actingAs($this->user);
        $origin = $this->createTrainingGroup('Origin Shared');
        $target = $this->createTrainingGroup('Target Shared');
        $inscription = $this->createInscription($this->makePlayer(), $origin);

        $requestWithTarget = new class ((int) $target->id)
        {
            public function __construct(private int $target)
            {
            }

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

        $service = new SharedService();
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
        $serviceError = Mockery::mock(SharedService::class)->makePartial();
        $serviceError->shouldReceive('logError')->once();
        $this->assertFalse($serviceError->assignTrainingGroup(999999, $requestWithTarget));
    }

    private function makePlayer(): Player
    {
        return Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => 'RC-' . fake()->unique()->numberBetween(1000, 9999),
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
