<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractType;
use App\Models\Inscription;
use App\Models\People;
use App\Models\Player;
use App\Models\School;
use App\Models\SchoolUser;
use App\Models\User;
use App\Modules\Inscriptions\Actions\Create\CreateContractAction;
use App\Modules\Inscriptions\Actions\Create\Passable;
use App\Modules\Inscriptions\Notifications\InscriptionToSchoolNotification;
use App\Notifications\GuardianEmailVerificationCodeNotification;
use App\Service\Contracts\ContractTemplateService;
use App\Service\Portal\GuardianEmailVerificationService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

final class ContractsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->ensureContractTypes();
        Storage::disk('local')->makeDirectory('tmp');
    }

    public function test_contracts_permission_middleware_blocks_and_allows_admin_endpoints(): void
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

    public function test_admin_contracts_update_uses_active_school_scope_and_recalculates_parameters(): void
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
            ->assertJsonPath('data.preview_url', route('admin.contracts.preview', ['contractTypeCode' => 'inscription']))
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

    public function test_admin_contracts_index_only_returns_preview_url_for_configured_templates(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $this->createConfiguredContract($school, 'inscription');

        $response = $this->actingAs($this->user)
            ->getJson('/api/v2/admin/contracts')
            ->assertOk();

        $types = collect($response->json('types'))->keyBy('code');

        $this->assertSame(
            route('admin.contracts.preview', ['contractTypeCode' => 'inscription']),
            $types->get('inscription')['preview_url']
        );
        $this->assertNull($types->get('affiliate')['preview_url']);
    }

    public function test_admin_contracts_includes_and_stores_generic_database_types_without_publishing_them_to_portal(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $school->forceFill([
            'create_contract' => true,
            'is_enable' => true,
            'inscriptions_enabled' => true,
        ])->save();
        School::forgetCachedSchool($school->id);

        $customType = new ContractType;
        $customType->code = 'custom_policy';
        $customType->name = 'Politica personalizada';
        $customType->save();

        $indexResponse = $this->actingAs($this->user)
            ->getJson('/api/v2/admin/contracts')
            ->assertOk();

        $types = collect($indexResponse->json('types'))->keyBy('code');

        $this->assertTrue($types->has('custom_policy'));
        $this->assertSame('Politica personalizada', $types->get('custom_policy')['label']);
        $this->assertSame('Plantilla personalizada', $types->get('custom_policy')['description']);
        $this->assertFalse($types->get('custom_policy')['portal']['requires_acceptance']);
        $this->assertNull($types->get('custom_policy')['preview_url']);

        $this->actingAs($this->user)
            ->putJson('/api/v2/admin/contracts/custom_policy', [
                'name' => 'Politica portal',
                'header' => '<p>[SCHOOL_NAME]</p>',
                'body' => '<p>[TUTOR_DOC_EXP]</p>',
                'footer' => '<p>[DATE]</p>',
            ])
            ->assertOk()
            ->assertJsonPath('data.code', 'custom_policy')
            ->assertJsonPath('data.configured', true)
            ->assertJsonPath('data.preview_url', route('admin.contracts.preview', ['contractTypeCode' => 'custom_policy']));

        $this->assertDatabaseHas('contracts', [
            'school_id' => $school->id,
            'contract_type_id' => $customType->id,
            'name' => 'Politica portal',
            'parameters' => 'SCHOOL_NAME,TUTOR_DOC_EXP,DATE',
        ]);

        $this->actingAs($this->user)
            ->get(route('admin.contracts.preview', ['contractTypeCode' => 'custom_policy']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $portalResponse = $this->getJson("/api/v2/portal/escuelas/{$school->slug}/data")
            ->assertOk();

        $this->assertSame([], $portalResponse->json('data.contracts.available'));
    }

    public function test_portal_school_data_only_returns_configured_contracts(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $school->forceFill([
            'create_contract' => true,
            'is_enable' => true,
            'inscriptions_enabled' => true,
        ])->save();
        School::forgetCachedSchool($school->id);

        $this->createConfiguredContract($school, 'inscription');
        $this->createConfiguredContract($school, 'image_rights');

        $response = $this->getJson("/api/v2/portal/escuelas/{$school->slug}/data")
            ->assertOk();

        $availableContracts = $response->json('data.contracts.available');

        $this->assertCount(2, $availableContracts);
        $this->assertSame('inscription', $availableContracts[0]['code']);
        $this->assertTrue($availableContracts[0]['requires_tutor_signature']);
        $this->assertFalse($availableContracts[0]['requires_player_signature']);
        $this->assertSame('contrato_insc', $availableContracts[0]['acceptance_field']);
        $this->assertSame('image_rights', $availableContracts[1]['code']);
        $this->assertSame('contrato_image_rights', $availableContracts[1]['acceptance_field']);
        $this->assertTrue($availableContracts[1]['requires_tutor_signature']);
        $this->assertFalse($availableContracts[1]['requires_player_signature']);
    }

    public function test_portal_inscription_validation_only_requires_available_contract_fields(): void
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
            route('api.v2.portal.school.inscription.store', [$school->slug]),
            $this->portalInscriptionPayload()
        );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['signatureTutor', 'contrato_insc']);
        $response->assertJsonMissingValidationErrors(['signatureAlumno', 'contrato_aff']);
    }

    public function test_public_contract_preview_streams_configured_template_and_missing_types_return404(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $school->forceFill([
            'create_contract' => true,
            'is_enable' => true,
        ])->save();
        School::forgetCachedSchool($school->id);

        $this->createConfiguredContract($school, 'inscription');

        $this->get(route('api.v2.portal.school.contract.show', [$school->slug, 'inscription']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get(route('api.v2.portal.school.contract.show', [$school->slug, 'affiliate']))
            ->assertNotFound();
    }

    public function test_admin_contract_preview_streams_configured_template_even_when_portal_flow_is_inactive(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $school->forceFill([
            'create_contract' => false,
            'is_enable' => true,
        ])->save();
        School::forgetCachedSchool($school->id);

        $this->createConfiguredContract($school, 'inscription');

        $this->actingAs($this->user)
            ->get(route('admin.contracts.preview', ['contractTypeCode' => 'inscription']))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_create_contract_action_only_generates_configured_contracts(): void
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
        $this->createConfiguredContract($school, 'image_rights');

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
            'signatureTutor' => 'data:image/png;base64,'.base64_encode(
                file_get_contents(public_path('img/user.png'))
            ),
        ]);
        $passable->setSchool();
        $passable->setPlayer($player);
        $inscription = Inscription::factory()->create([
            'player_id' => $player->id,
            'unique_code' => $player->unique_code,
            'school_id' => $school->id,
            'year' => now()->year,
            'competition_group_id' => null,
        ]);
        $passable->setInscription($inscription);
        $passable->setTutor([
            'name' => $tutor->names,
            'email' => $tutor->email,
        ]);

        app(CreateContractAction::class)->handle($passable, fn (Passable $value) => $value);

        $paths = $passable->getPaths();

        $this->assertArrayHasKey('contracts', $paths);
        $this->assertArrayNotHasKey('sign_tutor', $paths);
        $this->assertArrayNotHasKey('sign_player', $paths);
        $this->assertArrayHasKey('inscription', $paths['contracts']);
        $this->assertArrayHasKey('image_rights', $paths['contracts']);
        $this->assertArrayNotHasKey('affiliate', $paths['contracts']);

        $contractPath = array_values($paths['contracts']['inscription'])[0];

        Storage::disk('local')->assertExists($contractPath);

        $generatedFiles = Storage::disk('local')->files(dirname($contractPath));
        $this->assertCount(3, $generatedFiles);

        $nonPdfFiles = array_values(array_filter(
            $generatedFiles,
            fn (string $file): bool => ! str_ends_with($file, '.pdf')
        ));
        $this->assertCount(1, $nonPdfFiles);
        $this->assertSame('MANIFIESTO_SHA256.txt', basename($nonPdfFiles[0]));

        $inscription->refresh();
        $this->assertNotNull($inscription->signed_at);
        $this->assertSame(
            hash_file('sha256', Storage::disk('local')->path($contractPath)),
            $inscription->signed_document_hashes['inscription']
        );
        $imageRightsPath = array_values($paths['contracts']['image_rights'])[0];
        $this->assertSame(
            hash_file('sha256', Storage::disk('local')->path($imageRightsPath)),
            $inscription->signed_document_hashes['image_rights']
        );

        $manifest = Storage::disk('local')->get($nonPdfFiles[0]);
        $this->assertStringContainsString('Inscripción: '.$inscription->unique_code, $manifest);
        $this->assertStringContainsString('Algoritmo: SHA-256', $manifest);
        $this->assertStringContainsString(basename($contractPath), $manifest);
        $this->assertStringContainsString($inscription->signed_document_hashes['inscription'], $manifest);
        $this->assertStringContainsString(basename($imageRightsPath), $manifest);
        $this->assertStringContainsString($inscription->signed_document_hashes['image_rights'], $manifest);
        $this->assertStringNotContainsString('signature_ip_address', $manifest);
        $this->assertStringNotContainsString('signature_user_agent', $manifest);

        $message = (new InscriptionToSchoolNotification($inscription, $school))->toMail(new \stdClass);
        $this->assertSame([config('mail.from.address'), $school->name], $message->from);

        $zipPath = Storage::disk('local')->path(
            "tmp/zips/{$school->slug}-{$inscription->unique_code}.zip"
        );
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($zipPath));
        $this->assertSame(3, $zip->numFiles);
        $this->assertNotFalse($zip->locateName('MANIFIESTO_SHA256.txt'));
        $this->assertNotFalse($zip->locateName(basename($contractPath)));
        $this->assertNotFalse($zip->locateName(basename($imageRightsPath)));

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $this->assertDoesNotMatchRegularExpression('/\.(png|jpe?g)$/i', $zip->getNameIndex($index));
        }

        $zip->close();
    }

    public function test_tutor_document_expedition_placeholder_and_variables_are_available(): void
    {
        $school = School::query()->findOrFail($this->school['id']);
        $player = Player::factory()->create([
            'school_id' => $school->id,
        ]);
        $tutor = People::factory()->create([
            'tutor' => true,
            'document_expedition_place' => 'Cali',
        ]);
        $player->people()->attach($tutor->id);

        $service = app(ContractTemplateService::class);
        $placeholders = collect($service->placeholderCatalog('inscription'))->keyBy('key');

        $this->assertTrue($placeholders->has('TUTOR_DOC_EXP'));
        $this->assertSame('[TUTOR_DOC_EXP]', $placeholders->get('TUTOR_DOC_EXP')['token']);

        $variables = $service->buildPlayerVariables($school, $player);
        $previewVariables = $service->buildPreviewVariables($school);

        $this->assertSame('Cali', $variables['TUTOR_DOC_EXP']);
        $this->assertSame('MEDELLIN', $previewVariables['TUTOR_DOC_EXP']);
    }

    public function test_portal_inscription_requires_and_stores_tutor_document_expedition_place(): void
    {
        config([
            'recaptchav3.sitekey' => null,
            'recaptchav3.secret' => null,
        ]);

        $school = School::query()->findOrFail($this->school['id']);
        $school->forceFill([
            'create_contract' => false,
            'is_enable' => true,
            'inscriptions_enabled' => true,
        ])->save();
        School::forgetCachedSchool($school->id);

        $missingResponse = $this->postJson(
            route('api.v2.portal.school.inscription.store', [$school->slug]),
            array_diff_key($this->portalInscriptionPayload(), ['tutor_doc_exp' => true])
        );

        $missingResponse->assertStatus(422);
        $missingResponse->assertJsonValidationErrors(['tutor_doc_exp']);

        $payload = $this->portalInscriptionPayload([
            'identification_document' => '1002003999',
            'email' => 'jugador.exp@example.com',
            'tutor_num_doc' => '900800799',
            'tutor_email' => 'acudiente.exp@example.com',
            'tutor_doc_exp' => 'Bogota',
        ]);

        $this->postJson(
            route('api.v2.portal.school.inscription.store', [$school->slug]),
            $this->withGuardianVerificationToken($school, $payload)
        )->assertOk();

        $this->assertDatabaseHas('peoples', [
            'identification_card' => '900800799',
            'document_expedition_place' => 'Bogota',
        ]);
    }

    private function ensureContractTypes(): void
    {
        $definitions = [
            'contract' => 'Contrato',
            'affiliate' => 'Afiliacion',
            'image_rights' => 'AUTORIZACION USO DE IMAGEN',
        ];

        foreach ($definitions as $code => $name) {
            $type = ContractType::query()->firstWhere('code', $code) ?? new ContractType;
            $type->code = $code;
            $type->name = $name;
            $type->save();
        }
    }

    private function createConfiguredContract(School $school, string $code, array $overrides = []): Contract
    {
        $type = app(ContractTemplateService::class)->resolveType($code);

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

    private function portalInscriptionPayload(array $overrides = []): array
    {
        $payload = array_merge([
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
            'tutor_doc_exp' => 'Medellin',
            'tutor_relationship' => 'Madre',
            'tutor_phone' => '3009876543',
            'tutor_work' => 'Empresa Demo',
            'tutor_position_held' => 'Analista',
            'tutor_email' => 'acudiente.prueba@example.com',
            'data_processing_policy_accepted' => true,
            'year' => now()->format('Y'),
        ], $overrides);

        People::query()->updateOrCreate(
            ['identification_card' => $payload['tutor_num_doc']],
            [
                'names' => $payload['tutor_name'],
                'tutor' => true,
                'relationship' => People::MOTHER,
                'email' => $payload['tutor_email'],
                'email_verified_at' => now(),
            ]
        );

        return $payload;
    }

    private function withGuardianVerificationToken(School $school, array $payload): array
    {
        Notification::fake();

        $service = app(GuardianEmailVerificationService::class);
        $service->requestCode($school, $payload['tutor_num_doc'], $payload['tutor_email'], '127.0.0.1');

        $code = null;
        Notification::assertSentOnDemand(
            GuardianEmailVerificationCodeNotification::class,
            function (GuardianEmailVerificationCodeNotification $notification) use (&$code): bool {
                $code = $notification->code;

                return true;
            }
        );

        $confirmation = $service->confirmCode(
            $school,
            $payload['tutor_num_doc'],
            $payload['tutor_email'],
            (string) $code,
            '127.0.0.1'
        );

        return array_merge($payload, [
            'guardian_email_verification_token' => $confirmation['token'],
        ]);
    }
}
