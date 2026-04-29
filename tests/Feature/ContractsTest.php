<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\People;
use App\Models\Player;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\User;
use App\Modules\Inscriptions\Actions\Create\CreateContractAction;
use App\Modules\Inscriptions\Actions\Create\Passable;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ContractsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->ensureContractTypes();
        Storage::disk('local')->makeDirectory('tmp');
    }

    public function testContractsPermissionMiddlewareBlocksAndAllowsAdminEndpoints(): void
    {
        $school = School::query()->findOrFail($this->school['id']);

        $this->setSchoolPermissions($school, [
            'school.module.contracts' => false,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/contracts')
            ->assertForbidden();

        $this->setSchoolPermissions($school, [
            'school.module.contracts' => true,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/v2/admin/contracts')
            ->assertOk()
            ->assertJsonPath('school.id', $school->id);
    }

    public function testAdminContractsUpdateUsesActiveSchoolScopeAndRecalculatesParameters(): void
    {
        $secondarySchool = School::query()->findOrFail($this->createSchool([
            'name' => 'Escuela Secundaria Contratos',
            'slug' => 'escuela-secundaria-contratos',
            'email' => 'secondary-contracts@example.com',
        ])['id']);
        $superAdmin = $this->createSuperAdminForSchool($this->school['id']);

        $response = $this->withSession(['admin.selected_school' => $secondarySchool->id])
            ->actingAs($superAdmin)
            ->putJson('/api/v2/admin/contracts/inscription', [
                'name' => 'Contrato portal',
                'header' => '<p>[SCHOOL_NAME]</p>',
                'body' => '<p>[TUTOR_NAME]</p><p>[PLAYER_FULLNAMES]</p>',
                'footer' => '<p>[DATE]</p>',
            ])
            ->assertOk()
            ->assertJsonPath('data.code', 'inscription')
            ->assertJsonPath('data.configured', true)
            ->assertJsonPath('data.preview_url', route('api.v2.admin.contracts.preview', ['contractTypeCode' => 'inscription']))
            ->assertJsonPath('data.template.name', 'Contrato portal');

        $contract = Contract::query()
            ->where('school_id', $secondarySchool->id)
            ->firstOrFail();

        $this->assertSame('SCHOOL_NAME,TUTOR_NAME,PLAYER_FULLNAMES,DATE', $contract->parameters);
        $this->assertContains('[PLAYER_FULLNAMES]', $response->json('data.template.used_parameters'));

        $this->assertDatabaseMissing('contracts', [
            'school_id' => $this->school['id'],
            'name' => 'Contrato portal',
        ]);

        $indexResponse = $this->withSession(['admin.selected_school' => $secondarySchool->id])
            ->actingAs($superAdmin)
            ->getJson('/api/v2/admin/contracts')
            ->assertOk();

        $this->assertSame($secondarySchool->id, $indexResponse->json('school.id'));
        $this->assertContains('[PLAYER_FULLNAMES]', $indexResponse->json('types.0.template.used_parameters'));
    }

    public function testAdminContractsIndexOnlyReturnsPreviewUrlForConfiguredTemplates(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $this->createConfiguredContract($school, 'inscription');

        $response = $this->actingAs($this->user)
            ->getJson('/api/v2/admin/contracts')
            ->assertOk();

        $types = collect($response->json('types'))->keyBy('code');

        $this->assertSame(
            route('api.v2.admin.contracts.preview', ['contractTypeCode' => 'inscription']),
            $types->get('inscription')['preview_url']
        );
        $this->assertNull($types->get('affiliate')['preview_url']);
    }

    public function testPortalSchoolDataOnlyReturnsConfiguredContracts(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $school->forceFill([
            'create_contract' => true,
            'is_enable' => true,
            'inscriptions_enabled' => true,
        ])->save();
        School::forgetCachedSchool($school->id);

        $this->createConfiguredContract($school, 'inscription');

        $response = $this->getJson("/api/v2/portal/escuelas/{$school->slug}/data")
            ->assertOk();

        $availableContracts = $response->json('data.contracts.available');

        $this->assertCount(1, $availableContracts);
        $this->assertSame('inscription', $availableContracts[0]['code']);
        $this->assertTrue($availableContracts[0]['requires_tutor_signature']);
        $this->assertFalse($availableContracts[0]['requires_player_signature']);
        $this->assertSame('contrato_insc', $availableContracts[0]['acceptance_field']);
    }

    public function testPortalInscriptionValidationOnlyRequiresAvailableContractFields(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $school->forceFill([
            'create_contract' => true,
            'is_enable' => true,
            'inscriptions_enabled' => true,
        ])->save();
        School::forgetCachedSchool($school->id);

        $this->createConfiguredContract($school, 'inscription');

        $response = $this->postJson(
            route('portal.school.inscription.store', [$school->slug]),
            $this->portalInscriptionPayload()
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['signatureTutor', 'contrato_insc']);
        $response->assertJsonMissingValidationErrors(['signatureAlumno', 'contrato_aff']);
    }

    public function testPublicContractPreviewStreamsConfiguredTemplateAndMissingTypesReturn404(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $school->forceFill([
            'create_contract' => true,
            'is_enable' => true,
        ])->save();
        School::forgetCachedSchool($school->id);

        $this->createConfiguredContract($school, 'inscription');

        $this->get(route('portal.school.contract.show', [$school->slug, 'inscription']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get(route('portal.school.contract.show', [$school->slug, 'affiliate']))
            ->assertNotFound();
    }

    public function testAdminContractPreviewStreamsConfiguredTemplateEvenWhenPortalFlowIsInactive(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $school->forceFill([
            'create_contract' => false,
            'is_enable' => false,
        ])->save();
        School::forgetCachedSchool($school->id);

        $this->createConfiguredContract($school, 'inscription');

        $this->actingAs($this->user)
            ->get('/api/v2/admin/contracts/inscription/preview')
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function testCreateContractActionOnlyGeneratesConfiguredContracts(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $school->forceFill([
            'create_contract' => true,
            'is_enable' => true,
        ])->save();
        School::forgetCachedSchool($school->id);

        $this->createConfiguredContract($school, 'inscription', [
            'header' => '<p>[SCHOOL_NAME]</p>',
            'body' => '<p>[PLAYER_FULLNAMES]</p><p>[TUTOR_NAME]</p>',
            'footer' => '<p>[DATE]</p>',
        ]);

        $player = Player::factory()->create([
            'school_id' => $school->id,
            'unique_code' => 'CONTRACT1001',
            'identification_document' => '123456789',
        ]);
        $tutor = People::factory()->create([
            'tutor' => true,
            'email' => 'tutor-contracts@example.com',
            'mobile' => '3001234567',
        ]);
        $player->people()->attach($tutor->id);

        $passable = new Passable([
            'school_data' => $school,
            'year' => now()->format('Y'),
        ]);
        $passable->setSchool();
        $passable->setPlayer($player);
        $passable->setTutor([
            'name' => $tutor->names,
            'email' => $tutor->email,
        ]);

        app(CreateContractAction::class)->handle($passable, fn (Passable $value) => $value);

        $paths = $passable->getPaths();

        $this->assertArrayHasKey('contracts', $paths);
        $this->assertArrayHasKey('inscription', $paths['contracts']);
        $this->assertArrayNotHasKey('affiliate', $paths['contracts']);

        $contractPath = array_values($paths['contracts']['inscription'])[0];

        Storage::disk('local')->assertExists($contractPath);
    }

    private function ensureContractTypes(): void
    {
        $definitions = [
            'contract' => 'Contrato',
            'affiliate' => 'Afiliacion',
        ];

        foreach ($definitions as $code => $name) {
            $type = ContractType::query()->firstWhere('code', $code) ?? new ContractType();
            $type->code = $code;
            $type->name = $name;
            $type->save();
        }
    }

    private function createConfiguredContract(School $school, string $code, array $overrides = []): Contract
    {
        $type = app(\App\Service\Contracts\ContractTemplateService::class)->resolveType($code);

        return Contract::query()->create(array_merge([
            'school_id' => $school->id,
            'contract_type_id' => $type['contract_type_id'],
            'name' => $type['label'],
            'parameters' => 'SCHOOL_NAME,TUTOR_NAME,PLAYER_FULLNAMES,DATE',
            'header' => '<p>[SCHOOL_NAME]</p>',
            'body' => '<p>[TUTOR_NAME]</p><p>[PLAYER_FULLNAMES]</p>',
            'footer' => '<p>[DATE]</p>',
        ], $overrides));
    }

    private function setSchoolPermissions(School $school, array $overrides): void
    {
        $permissions = array_merge($school->getResolvedSchoolPermissions(), $overrides);

        $school->forceFill([
            'school_permissions' => School::normalizeSchoolPermissions($permissions),
        ])->save();

        School::forgetCachedSchool($school->id);
    }

    private function createSuperAdminForSchool(int $schoolId): User
    {
        return $this->createSchoolScopedUser(
            $schoolId,
            ['super-admin'],
            sprintf('superadmin-contracts-%s@example.com', uniqid())
        );
    }

    private function createSchoolScopedUser(int $schoolId, array $roles, string $email): User
    {
        $user = $this->createUser([
            'email' => $email,
            'school_id' => $schoolId,
        ], $roles);

        SchoolUser::query()->create([
            'user_id' => $user->id,
            'school_id' => $schoolId,
        ]);

        return $user;
    }

    private function portalInscriptionPayload(): array
    {
        return [
            'names' => 'Jugador',
            'last_names' => 'Prueba',
            'date_birth' => '2014-05-11',
            'place_birth' => 'Medellin',
            'identification_document' => '1002003004',
            'document_type' => 'TI',
            'gender' => 'M',
            'email' => 'jugador.prueba@example.com',
            'mobile' => '3001234567',
            'medical_history' => 'Ninguno',
            'school' => 'Colegio de prueba',
            'degree' => '6',
            'jornada' => 'Manana',
            'address' => 'Calle 1 # 2 - 3',
            'municipality' => 'Medellin',
            'neighborhood' => 'Laureles',
            'rh' => 'O+',
            'eps' => 'Sura',
            'student_insurance' => 'Seguro escolar',
            'tutor_name' => 'Acudiente Prueba',
            'tutor_num_doc' => '900800700',
            'tutor_relationship' => 'Madre',
            'tutor_phone' => '3009876543',
            'tutor_work' => 'Empresa Demo',
            'tutor_position_held' => 'Analista',
            'tutor_email' => 'acudiente.prueba@example.com',
            'year' => now()->format('Y'),
        ];
    }

}
