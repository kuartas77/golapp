<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Evaluations\EvaluationPeriod;
use App\Models\Evaluations\EvaluationTemplate;
use App\Models\Evaluations\EvaluationTemplateCriterion;
use App\Models\Evaluations\PlayerEvaluation;
use App\Models\Inscription;
use App\Models\Player;
use App\Models\SchoolUser;
use App\Models\TrainingGroup;
use App\Models\User;
use Tests\TestCase;

final class EvaluationTemplatesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function testSuperAdminCanAccessEvaluationTemplatesSpaRoute(): void
    {
        $superAdmin = $this->createSuperAdminForCurrentSchool();

        $this->withSession(['admin.selected_school' => $this->school['id']])
            ->actingAs($superAdmin)
            ->get('/administracion/plantillas-evaluacion')
            ->assertOk()
            ->assertSee('id="app"', false);
    }

    public function testSchoolUserCannotAccessEvaluationTemplatesSpaOrApi(): void
    {
        $this->actingAs($this->user)
            ->get('/administracion/plantillas-evaluacion')
            ->assertForbidden();

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/evaluation-templates/options')
            ->assertForbidden();
    }

    public function testSuperAdminCanCreateListAndUpdateEvaluationTemplates(): void
    {
        $superAdmin = $this->createSuperAdminForCurrentSchool();
        $group = $this->trainingGroup();

        $payload = [
            'name' => 'Plantilla integral',
            'description' => 'Seguimiento completo para jugadores.',
            'year' => now()->year,
            'training_group_id' => null,
            'status' => 'draft',
            'criteria' => [
                [
                    'dimension' => 'Técnica',
                    'name' => 'Pase',
                    'description' => 'Precisión de pase.',
                    'score_type' => 'numeric',
                    'min_score' => 1,
                    'max_score' => 5,
                    'weight' => 1.2,
                    'sort_order' => 1,
                    'is_required' => true,
                ],
                [
                    'dimension' => 'Actitudinal',
                    'name' => 'Compromiso',
                    'description' => 'Nivel de compromiso.',
                    'score_type' => 'scale',
                    'min_score' => null,
                    'max_score' => null,
                    'weight' => 1.0,
                    'sort_order' => 2,
                    'is_required' => true,
                ],
            ],
        ];

        $createResponse = $this->withSession(['admin.selected_school' => $this->school['id']])
            ->actingAs($superAdmin)
            ->postJson('/api/v2/admin/evaluation-templates', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Plantilla integral')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.criteria.0.score_type', 'numeric')
            ->assertJsonPath('data.criteria.1.score_type', 'scale');

        $templateId = (int) $createResponse->json('data.id');

        $this->assertDatabaseHas('evaluation_templates', [
            'id' => $templateId,
            'school_id' => $this->school['id'],
            'training_group_id' => null,
            'status' => 'draft',
            'version' => 1,
        ]);

        $this->assertDatabaseHas('evaluation_template_criteria', [
            'evaluation_template_id' => $templateId,
            'name' => 'Compromiso',
            'score_type' => 'scale',
            'min_score' => null,
            'max_score' => null,
        ]);

        $this->withSession(['admin.selected_school' => $this->school['id']])
            ->actingAs($superAdmin)
            ->getJson('/api/v2/admin/evaluation-templates')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $templateId,
                'name' => 'Plantilla integral',
            ]);

        $groupPayload = [
            'name' => 'Plantilla por grupo',
            'description' => 'Solo para un grupo de entrenamiento.',
            'year' => now()->year,
            'training_group_id' => $group->id,
            'status' => 'active',
            'criteria' => [
                [
                    'dimension' => 'Táctica',
                    'name' => 'Lectura',
                    'description' => null,
                    'score_type' => 'numeric',
                    'min_score' => 1,
                    'max_score' => 5,
                    'weight' => 1,
                    'sort_order' => 1,
                    'is_required' => true,
                ],
            ],
        ];

        $groupResponse = $this->withSession(['admin.selected_school' => $this->school['id']])
            ->actingAs($superAdmin)
            ->postJson('/api/v2/admin/evaluation-templates', $groupPayload)
            ->assertCreated()
            ->assertJsonPath('data.training_group_id', $group->id);

        $groupTemplateId = (int) $groupResponse->json('data.id');

        $updatePayload = [
            'name' => 'Plantilla por grupo actualizada',
            'description' => 'Versión ajustada.',
            'year' => now()->year,
            'training_group_id' => $group->id,
            'status' => 'active',
            'criteria' => [
                [
                    'dimension' => 'Táctica',
                    'name' => 'Lectura de juego',
                    'description' => 'Comprensión del contexto de juego.',
                    'score_type' => 'numeric',
                    'min_score' => 1,
                    'max_score' => 5,
                    'weight' => 1.3,
                    'sort_order' => 1,
                    'is_required' => true,
                ],
                [
                    'dimension' => 'Actitudinal',
                    'name' => 'Trabajo en equipo',
                    'description' => null,
                    'score_type' => 'scale',
                    'min_score' => null,
                    'max_score' => null,
                    'weight' => 1,
                    'sort_order' => 2,
                    'is_required' => false,
                ],
            ],
        ];

        $this->withSession(['admin.selected_school' => $this->school['id']])
            ->actingAs($superAdmin)
            ->putJson("/api/v2/admin/evaluation-templates/{$groupTemplateId}", $updatePayload)
            ->assertOk()
            ->assertJsonPath('data.name', 'Plantilla por grupo actualizada')
            ->assertJsonPath('data.criteria.1.name', 'Trabajo en equipo');

        $this->assertDatabaseHas('evaluation_templates', [
            'id' => $groupTemplateId,
            'name' => 'Plantilla por grupo actualizada',
            'training_group_id' => $group->id,
        ]);

        $this->assertDatabaseHas('evaluation_template_criteria', [
            'evaluation_template_id' => $groupTemplateId,
            'name' => 'Trabajo en equipo',
            'score_type' => 'scale',
        ]);
    }

    public function testInvalidScoreTypeIsRejected(): void
    {
        $superAdmin = $this->createSuperAdminForCurrentSchool();

        $payload = [
            'name' => 'Plantilla inválida',
            'description' => null,
            'year' => now()->year,
            'training_group_id' => null,
            'status' => 'draft',
            'criteria' => [
                [
                    'dimension' => 'Técnica',
                    'name' => 'Pase',
                    'description' => null,
                    'score_type' => 'boolean',
                    'min_score' => 1,
                    'max_score' => 5,
                    'weight' => 1,
                    'sort_order' => 1,
                    'is_required' => true,
                ],
            ],
        ];

        $this->withSession(['admin.selected_school' => $this->school['id']])
            ->actingAs($superAdmin)
            ->postJson('/api/v2/admin/evaluation-templates', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['criteria.0.score_type']);
    }

    public function testUsedTemplateCannotBeUpdatedOrDeletedButCanBeDuplicated(): void
    {
        $superAdmin = $this->createSuperAdminForCurrentSchool();
        $template = $this->createTemplateFixture();
        $this->attachUsageToTemplate($template, $superAdmin);

        $updatePayload = [
            'name' => 'Plantilla usada editada',
            'description' => 'No debería permitir edición.',
            'year' => now()->year,
            'training_group_id' => $template->training_group_id,
            'status' => 'active',
            'criteria' => [
                [
                    'dimension' => 'Técnica',
                    'name' => 'Pase largo',
                    'description' => null,
                    'score_type' => 'numeric',
                    'min_score' => 1,
                    'max_score' => 5,
                    'weight' => 1,
                    'sort_order' => 1,
                    'is_required' => true,
                ],
            ],
        ];

        $this->withSession(['admin.selected_school' => $this->school['id']])
            ->actingAs($superAdmin)
            ->putJson("/api/v2/admin/evaluation-templates/{$template->id}", $updatePayload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['template']);

        $this->withSession(['admin.selected_school' => $this->school['id']])
            ->actingAs($superAdmin)
            ->deleteJson("/api/v2/admin/evaluation-templates/{$template->id}")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['template']);

        $duplicateResponse = $this->withSession(['admin.selected_school' => $this->school['id']])
            ->actingAs($superAdmin)
            ->postJson("/api/v2/admin/evaluation-templates/{$template->id}/duplicate")
            ->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.version', 2)
            ->assertJsonPath('data.criteria.0.name', 'Pase');

        $copyId = (int) $duplicateResponse->json('data.id');

        $this->assertDatabaseHas('evaluation_templates', [
            'id' => $copyId,
            'name' => $template->name,
            'status' => 'draft',
            'version' => 2,
        ]);

        $this->assertSame(1, EvaluationTemplate::query()->findOrFail($copyId)->criteria()->count());
    }

    public function testStatusUpdateControlsWhatPlayerEvaluationsCanSelect(): void
    {
        $superAdmin = $this->createSuperAdminForCurrentSchool();

        $activeTemplate = $this->createTemplateFixture([
            'name' => 'Plantilla activa',
            'status' => 'active',
        ]);

        $inactiveTemplate = $this->createTemplateFixture([
            'name' => 'Plantilla inactiva',
            'status' => 'inactive',
        ]);

        $this->withSession(['admin.selected_school' => $this->school['id']])
            ->actingAs($superAdmin)
            ->patchJson("/api/v2/admin/evaluation-templates/{$inactiveTemplate->id}/status", [
                'status' => 'active',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'active');

        $this->withSession(['admin.selected_school' => $this->school['id']])
            ->actingAs($superAdmin)
            ->patchJson("/api/v2/admin/evaluation-templates/{$activeTemplate->id}/status", [
                'status' => 'inactive',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'inactive');

        $this->withSession(['admin.selected_school' => $this->school['id']])
            ->actingAs($superAdmin)
            ->getJson('/api/v2/player-evaluations/options')
            ->assertOk()
            ->assertJsonMissing(['name' => 'Plantilla activa'])
            ->assertJsonFragment(['name' => 'Plantilla inactiva']);
    }

    private function createSuperAdminForCurrentSchool(): User
    {
        $user = $this->createUser([
            'email' => sprintf('superadmin-%s@example.com', uniqid()),
            'school_id' => $this->school['id'],
        ], ['super-admin']);

        $schoolUser = new SchoolUser();
        $schoolUser->user_id = $user->id;
        $schoolUser->school_id = $this->school['id'];
        $schoolUser->save();

        return $user;
    }

    private function trainingGroup(): TrainingGroup
    {
        return TrainingGroup::query()->where('school_id', $this->school['id'])->firstOrFail();
    }

    private function createTemplateFixture(array $overrides = []): EvaluationTemplate
    {
        $template = EvaluationTemplate::create(array_merge([
            'name' => 'Plantilla base',
            'description' => 'Base de evaluación',
            'year' => now()->year,
            'training_group_id' => $this->trainingGroup()->id,
            'status' => 'active',
            'version' => 1,
            'created_by' => $this->user->id,
            'school_id' => $this->school['id'],
        ], $overrides));

        EvaluationTemplateCriterion::create([
            'evaluation_template_id' => $template->id,
            'code' => 'tecnica_pase',
            'dimension' => 'Técnica',
            'name' => 'Pase',
            'description' => 'Precisión en el pase.',
            'score_type' => 'numeric',
            'min_score' => 1,
            'max_score' => 5,
            'weight' => 1.2,
            'sort_order' => 1,
            'is_required' => true,
        ]);

        return $template;
    }

    private function attachUsageToTemplate(EvaluationTemplate $template, User $superAdmin): void
    {
        $group = $this->trainingGroup();
        $player = Player::factory()->create([
            'school_id' => $this->school['id'],
            'unique_code' => 'ET-001',
            'names' => 'Carlos',
            'last_names' => 'Ramirez',
        ]);

        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'training_group_id' => $group->id,
            'competition_group_id' => null,
            'school_id' => $this->school['id'],
        ]);

        $period = EvaluationPeriod::create([
            'name' => 'Corte 1',
            'code' => 'T1',
            'year' => now()->year,
            'starts_at' => now()->startOfMonth(),
            'ends_at' => now()->endOfMonth(),
            'sort_order' => 1,
            'is_active' => true,
            'school_id' => $this->school['id'],
        ]);

        PlayerEvaluation::create([
            'school_id' => $this->school['id'],
            'inscription_id' => $inscription->id,
            'evaluation_period_id' => $period->id,
            'evaluation_template_id' => $template->id,
            'evaluator_user_id' => $superAdmin->id,
            'evaluation_type' => 'periodic',
            'status' => 'draft',
            'evaluated_at' => now(),
            'general_comment' => null,
            'strengths' => null,
            'improvement_opportunities' => null,
            'recommendations' => null,
            'overall_score' => null,
        ]);
    }
}
