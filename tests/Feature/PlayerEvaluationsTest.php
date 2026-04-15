<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Evaluations\EvaluationPeriod;
use App\Models\Evaluations\EvaluationTemplate;
use App\Models\Evaluations\EvaluationTemplateCriterion;
use App\Models\Evaluations\PlayerEvaluation;
use App\Models\Evaluations\PlayerEvaluationScore;
use App\Models\Inscription;
use App\Models\Player;
use App\Models\TrainingGroup;
use Tests\TestCase;

final class PlayerEvaluationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function testPlayerEvaluationsSpaRoutesRenderThemeShell(): void
    {
        $fixture = $this->createEvaluationFixture();

        $this->actingAs($this->user);

        $this->get('/player-evaluations')
            ->assertOk()
            ->assertSee('id="app"', false);

        $this->get('/player-evaluations/create')
            ->assertOk()
            ->assertSee('id="app"', false);

        $this->get('/player-evaluations/comparison')
            ->assertOk()
            ->assertSee('id="app"', false);

        $this->get('/player-evaluations/' . $fixture['evaluation']->id)
            ->assertOk()
            ->assertSee('id="app"', false);
    }

    public function testPlayerEvaluationsOptionsEndpointReturnsFiltersAndSelectionData(): void
    {
        $fixture = $this->createEvaluationFixture();

        $this->actingAs($this->user);

        $this->getJson('/api/v2/player-evaluations/options')
            ->assertOk()
            ->assertJsonStructure([
                'filters' => ['players', 'training_groups', 'periods', 'statuses', 'evaluation_types'],
                'selection' => ['inscriptions', 'periods', 'templates', 'statuses', 'evaluation_types'],
                'scale_options',
            ])
            ->assertJsonFragment([
                'name' => $fixture['player']->full_names,
            ])
            ->assertJsonFragment([
                'label' => '#'.$fixture['inscription']->id.' - '.$fixture['player']->full_names.' - '.$fixture['group']->name,
            ]);
    }

    public function testPlayerEvaluationsIndexEndpointReturnsSerializedEvaluations(): void
    {
        $fixture = $this->createEvaluationFixture();

        $this->actingAs($this->user);

        $this->getJson('/api/v2/player-evaluations')
            ->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonFragment([
                'id' => $fixture['evaluation']->id,
                'name' => $fixture['player']->full_names,
            ])
            ->assertJsonFragment([
                'name' => $fixture['template']->name,
            ])
            ->assertJsonFragment([
                'name' => $fixture['periodA']->name,
            ]);
    }

    public function testPlayerEvaluationsCreateAndComparisonEndpointsReturnExpectedPayloads(): void
    {
        $fixture = $this->createEvaluationFixture();

        $this->actingAs($this->user);

        $this->getJson('/api/v2/player-evaluations/create?'.http_build_query([
            'inscription_id' => $fixture['inscription']->id,
            'evaluation_period_id' => $fixture['periodA']->id,
            'evaluation_template_id' => $fixture['template']->id,
        ]))
            ->assertOk()
            ->assertJsonPath('inscription.player.name', $fixture['player']->full_names)
            ->assertJsonPath('period.name', $fixture['periodA']->name)
            ->assertJsonPath('template.name', $fixture['template']->name)
            ->assertJsonPath('criteria_by_dimension.Técnica.0.name', 'Pase');

        $this->getJson('/api/v2/player-evaluations/comparison')
            ->assertOk()
            ->assertJsonPath('comparison', null);

        $this->getJson('/api/v2/player-evaluations/comparison?'.http_build_query([
            'inscription_id' => $fixture['inscription']->id,
            'period_a_id' => $fixture['periodA']->id,
            'period_b_id' => $fixture['periodB']->id,
        ]))
            ->assertOk()
            ->assertJsonPath('comparison.player.name', $fixture['player']->full_names)
            ->assertJsonPath('comparison.period_a.period_name', $fixture['periodA']->name)
            ->assertJsonPath('comparison.period_b.period_name', $fixture['periodB']->name)
            ->assertJsonPath('comparison.overall.period_a_score', 4.5)
            ->assertJsonPath('comparison.overall.period_b_score', 3.8);
    }

    private function createEvaluationFixture(): array
    {
        $group = TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();

        $player = Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => 'PE-001',
            'names' => 'Juan',
            'last_names' => 'Perez',
        ]);

        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'school_id' => $this->school['id'],
        ]);

        $periodA = EvaluationPeriod::create([
            'name' => 'Corte 1',
            'code' => 'T1',
            'year' => now()->year,
            'starts_at' => now()->startOfMonth(),
            'ends_at' => now()->endOfMonth(),
            'sort_order' => 1,
            'is_active' => true,
            'school_id' => $this->school['id'],
        ]);

        $periodB = EvaluationPeriod::create([
            'name' => 'Corte 2',
            'code' => 'T2',
            'year' => now()->year,
            'starts_at' => now()->addMonth()->startOfMonth(),
            'ends_at' => now()->addMonth()->endOfMonth(),
            'sort_order' => 2,
            'is_active' => true,
            'school_id' => $this->school['id'],
        ]);

        $template = EvaluationTemplate::create([
            'name' => 'Plantilla de campo',
            'description' => 'Plantilla base para pruebas.',
            'year' => now()->year,
            'training_group_id' => $group->id,
            'status' => 'active',
            'version' => 1,
            'created_by' => $this->user->id,
            'school_id' => $this->school['id'],
        ]);

        $criterionPass = EvaluationTemplateCriterion::create([
            'evaluation_template_id' => $template->id,
            'code' => 'technical_pass',
            'dimension' => 'Técnica',
            'name' => 'Pase',
            'description' => 'Precisión y calidad del pase.',
            'score_type' => 'numeric',
            'min_score' => 1,
            'max_score' => 5,
            'weight' => 1.2,
            'sort_order' => 1,
            'is_required' => true,
        ]);

        $criterionDecision = EvaluationTemplateCriterion::create([
            'evaluation_template_id' => $template->id,
            'code' => 'tactical_decision_making',
            'dimension' => 'Táctica',
            'name' => 'Toma de decisiones',
            'description' => 'Lectura del juego y elección de acciones.',
            'score_type' => 'numeric',
            'min_score' => 1,
            'max_score' => 5,
            'weight' => 1.0,
            'sort_order' => 2,
            'is_required' => true,
        ]);

        $evaluation = PlayerEvaluation::create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'evaluation_period_id' => $periodA->id,
            'evaluation_template_id' => $template->id,
            'evaluator_user_id' => $this->user->id,
            'evaluation_type' => 'periodic',
            'status' => 'completed',
            'evaluated_at' => now(),
            'general_comment' => 'Buen rendimiento general.',
            'strengths' => 'Buen pase.',
            'improvement_opportunities' => 'Mejorar visión periférica.',
            'recommendations' => 'Seguir trabajo técnico.',
            'overall_score' => 4.5,
        ]);

        PlayerEvaluationScore::create([
            'player_evaluation_id' => $evaluation->id,
            'template_criterion_id' => $criterionPass->id,
            'score' => 4.8,
            'comment' => 'Muy seguro en corto y medio pase.',
        ]);

        PlayerEvaluationScore::create([
            'player_evaluation_id' => $evaluation->id,
            'template_criterion_id' => $criterionDecision->id,
            'score' => 4.2,
            'comment' => 'Lee bien las superioridades.',
        ]);

        $comparisonEvaluation = PlayerEvaluation::create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'evaluation_period_id' => $periodB->id,
            'evaluation_template_id' => $template->id,
            'evaluator_user_id' => $this->user->id,
            'evaluation_type' => 'periodic',
            'status' => 'completed',
            'evaluated_at' => now()->addMonth(),
            'general_comment' => 'Bajó un poco el ritmo.',
            'strengths' => 'Sigue sosteniendo el pase.',
            'improvement_opportunities' => 'Decisiones bajo presión.',
            'recommendations' => 'Trabajar toma de decisiones.',
            'overall_score' => 3.8,
        ]);

        PlayerEvaluationScore::create([
            'player_evaluation_id' => $comparisonEvaluation->id,
            'template_criterion_id' => $criterionPass->id,
            'score' => 4.1,
            'comment' => 'Sigue siendo confiable.',
        ]);

        PlayerEvaluationScore::create([
            'player_evaluation_id' => $comparisonEvaluation->id,
            'template_criterion_id' => $criterionDecision->id,
            'score' => 3.4,
            'comment' => 'Necesita mejores elecciones con presión.',
        ]);

        return [
            'group' => $group,
            'player' => $player,
            'inscription' => $inscription,
            'periodA' => $periodA,
            'periodB' => $periodB,
            'template' => $template,
            'evaluation' => $evaluation,
        ];
    }
}
