<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Requests\API\Notification\PaymentInvoiceRequest;
use App\Http\Requests\API\Notification\UniformFormRequest;
use App\Http\Requests\API\Portal\GuardianForgotPasswordRequest;
use App\Http\Requests\API\Portal\GuardianProfileUpdateRequest;
use App\Http\Requests\API\Portal\GuardianResetPasswordRequest;
use App\Http\Requests\API\RegisterRequest;
use App\Http\Requests\BackOffice\SchoolCreateRequest;
use App\Http\Requests\BackOffice\SchoolUpdateRequest;
use App\Http\Requests\Evaluations\ComparePlayerEvaluationsRequest;
use App\Http\Requests\Evaluations\StorePlayerEvaluationRequest;
use App\Http\Requests\Groups\CompetitionGroupRequest;
use App\Http\Requests\IncidentStore;
use App\Http\Requests\InscriptionCustomChargeUpdateRequest;
use App\Http\Requests\InvoiceCustomItemRequest;
use App\Http\Requests\InvoiceStoreRequest;
use App\Http\Requests\NotificationStoreRequest;
use App\Http\Requests\Portal\PlayerPortalUpdateRequest;
use App\Http\Requests\SchoolOutings\SchoolOutingActivityRequest;
use App\Http\Requests\SchoolOutings\SchoolOutingContributionRequest;
use App\Http\Requests\SchoolOutings\SchoolOutingParticipantRequest;
use App\Http\Requests\SchoolOutings\SchoolOutingRequest;
use App\Http\Requests\SchoolOutings\SchoolOutingStatusRequest;
use App\Http\Requests\SetTournamentPaymentRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\TournamentUpdateRequest;
use App\Http\Requests\TrainingSessionsRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\User\UserStore as LegacyUserStoreRequest;
use App\Http\Requests\User\UserUpdate as LegacyUserUpdateRequest;
use App\Models\Player;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use ReflectionMethod;
use Tests\TestCase;

final class FormRequestsCoverageTest extends TestCase
{
    public function testGuardianForgotPasswordRequestNormalizesEmailAndRules(): void
    {
        $request = GuardianForgotPasswordRequest::create('/', 'POST', [
            'email' => '  TUTOR@EXAMPLE.TEST  ',
        ]);

        $this->prepare($request);

        $this->assertTrue($request->authorize());
        $this->assertSame('tutor@example.test', $request->input('email'));
        $this->assertSame(['required', 'string', 'email:rfc'], $request->rules()['email']);
    }

    public function testGuardianResetPasswordRequestNormalizesEmailAndPasswordRules(): void
    {
        $request = GuardianResetPasswordRequest::create('/', 'POST', [
            'email' => '  TUTOR@EXAMPLE.TEST  ',
        ]);

        $this->prepare($request);
        $rules = $request->rules();

        $this->assertTrue($request->authorize());
        $this->assertSame('tutor@example.test', $request->input('email'));
        $this->assertSame(['required', 'string'], $rules['token']);
        $this->assertSame(['required', 'string', 'email:rfc'], $rules['email']);
        $this->assertContains('required', $rules['password']);
        $this->assertContains('confirmed', $rules['password']);
        $this->assertContainsOnlyInstancesOf(Password::class, [$rules['password'][2]]);
    }

    public function testGuardianProfileUpdateRequestNormalizesEmailAndRequiresGuardianAuth(): void
    {
        $request = GuardianProfileUpdateRequest::create('/', 'PUT', [
            'email' => '  TUTOR@EXAMPLE.TEST  ',
        ]);

        $this->prepare($request);
        $rules = $request->rules();

        $this->assertFalse($request->authorize());
        $this->assertSame('tutor@example.test', $request->input('email'));
        $this->assertSame(['required', 'string', 'max:50'], $rules['names']);
        $this->assertSame(['nullable', 'string', 'max:50'], $rules['phone']);
        $this->assertContains('email:rfc', $rules['email']);
    }

    public function testRegisterRequestBuildsSlugAndRules(): void
    {
        $request = RegisterRequest::create('/', 'POST', [
            'name' => 'Escuela Norte FC',
        ]);

        $this->prepare($request);
        $rules = $request->rules();

        $this->assertTrue($request->authorize());
        $this->assertSame('escuela-norte-fc', $request->input('slug'));
        $this->assertContains('required', $rules['email']);
        $this->assertContains('confirmed', $rules['password']);
        $this->assertSame(['required', 'string'], $rules['name']);
        $this->assertSame(['required', 'string'], $rules['agent']);
        $this->assertSame(['required', 'string'], $rules['slug']);
    }

    public function testUniformFormRequestRequiresPlayerAndMapsAdditionalNotes(): void
    {
        $request = UniformFormRequest::create('/', 'POST', [
            'additionalNotes' => 'Enviar talla amplia',
        ]);

        $this->prepare($request);

        $this->assertFalse($request->authorize());

        $request->setUserResolver(fn () => new Player());
        $this->prepare($request);

        $this->assertTrue($request->authorize());
        $this->assertSame('Enviar talla amplia', $request->input('additional_notes'));
        $this->assertSame([
            'type' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'size' => ['required', 'string'],
            'additional_notes' => ['nullable', 'string'],
        ], $request->rules());
    }

    public function testPaymentInvoiceRequestRequiresPlayerAndLowercasesPaymentMethod(): void
    {
        $request = PaymentInvoiceRequest::create('/', 'POST', [
            'payment_method' => 'TRANSFER',
        ]);

        $this->prepare($request);

        $this->assertFalse($request->authorize());

        $request->setUserResolver(fn () => new Player());
        $this->prepare($request);
        $rules = $request->rules();

        $this->assertTrue($request->authorize());
        $this->assertSame('transfer', $request->input('payment_method'));
        $this->assertSame(['required', 'integer'], $rules['id']);
        $this->assertSame(['required', 'integer'], $rules['invoice_id']);
        $this->assertSame(['required', 'numeric'], $rules['amount']);
        $this->assertContains('required', $rules['payment_method']);
        $this->assertContains('file', $rules['image']);
    }

    public function testSchoolScopedRequestsMergeCurrentSchoolAndNormalizeValues(): void
    {
        $this->actingAs($this->user);

        $incident = IncidentStore::create('/', 'POST');
        $this->prepare($incident);
        $this->assertTrue($incident->authorize());
        $this->assertSame($this->school['id'], $incident->input('school_id'));
        $this->assertSame('required|string', $incident->rules()['incidence']);

        $invoice = InvoiceStoreRequest::create('/', 'POST');
        $this->prepare($invoice);
        $this->assertTrue($invoice->authorize());
        $this->assertSame($this->school['id'], $invoice->input('school_id'));
        $this->assertSame('required|array|min:1', $invoice->rules()['items']);

        $competitionGroup = CompetitionGroupRequest::create('/', 'POST', ['year' => '2012-2013']);
        $this->prepare($competitionGroup);
        $this->assertTrue($competitionGroup->authorize());
        $this->assertSame($this->school['id'], $competitionGroup->input('school_id'));
        $this->assertSame('2012-2013', $competitionGroup->input('category'));

        $tournament = TournamentUpdateRequest::create('/', 'PUT', ['name' => 'copa golapp']);
        $this->prepare($tournament);
        $this->assertTrue($tournament->authorize());
        $this->assertSame('COPA GOLAPP', $tournament->input('name'));
        $this->assertSame($this->school['id'], $tournament->input('school_id'));
    }

    public function testMoneyRequestsStripFormattingBeforeValidation(): void
    {
        $this->actingAs($this->user);

        $customItem = InvoiceCustomItemRequest::create('/', 'POST', [
            'item_type' => 'uniform',
            'item_name' => 'Uniforme local',
            'item_unit_price' => '$ 85.000 COP',
        ]);
        $this->prepare($customItem);

        $this->assertTrue($customItem->authorize());
        $this->assertSame('uniform', $customItem->input('type'));
        $this->assertSame('Uniforme local', $customItem->input('name'));
        $this->assertSame('85000', $customItem->input('unit_price'));

        $charge = InscriptionCustomChargeUpdateRequest::create('/', 'PATCH', [
            'value' => '$ 45.500',
        ]);
        $this->prepare($charge);

        $this->assertTrue($charge->authorize());
        $this->assertSame('45500', $charge->input('value'));
        $this->assertSame('required', $charge->rules()['status'][0]);
        $this->assertIsObject($charge->rules()['status'][1]);

        $tournamentPayment = SetTournamentPaymentRequest::create('/', 'PATCH', [
            'value' => '$ 120.000',
        ]);
        $this->prepare($tournamentPayment);

        $this->assertTrue($tournamentPayment->authorize());
        $this->assertSame('120000', $tournamentPayment->input('value'));
        $this->assertSame(['required'], $tournamentPayment->rules()['status']);
    }

    public function testNotificationStoreRequestCastsPlayerIdsAndExposesMessages(): void
    {
        $request = NotificationStoreRequest::create('/', 'POST', [
            'players' => ['10', 'ABC', 11],
        ]);
        $request->setUserResolver(fn () => $this->user);

        $this->prepare($request);

        $this->assertTrue($request->authorize());
        $this->assertSame($this->school['id'], $request->input('school_id'));
        $this->assertSame([10, 'ABC', 11], $request->input('players'));
        $this->assertSame('Debes seleccionar al menos un jugador.', $request->messages()['players.required']);
        $this->assertContains('distinct', $request->rules()['players.*']);
    }

    public function testTrainingSessionsRequestDefaultsDateHourYearAndUserContext(): void
    {
        $this->actingAs($this->user);

        $request = TrainingSessionsRequest::create('/', 'POST', [
            'date' => '2026-07-15',
        ]);
        $this->prepare($request);

        $this->assertTrue($request->authorize());
        $this->assertSame($this->school['id'], $request->input('school_id'));
        $this->assertSame($this->user->id, $request->input('user_id'));
        $this->assertSame(2026, $request->input('year'));
        $this->assertNotEmpty($request->input('hour'));
        $this->assertSame(['required', 'array', 'min:3'], $request->rules()['task_number']);
    }

    public function testPlayerPortalUpdateRequestDerivesCategoryAndSchool(): void
    {
        $this->actingAs($this->user);

        $request = PlayerPortalUpdateRequest::create('/', 'PUT', [
            'date_birth' => '2014-02-10',
        ]);
        $this->prepare($request);

        $this->assertTrue($request->authorize());
        $this->assertSame($this->school['id'], $request->input('school_id'));
        $this->assertSame(categoriesName(2014), $request->input('category'));
        $this->assertSame(['required', 'date_format:Y-m-d'], $request->rules()['date_birth']);
    }

    public function testSchoolOutingActivityRequestTrimsName(): void
    {
        $this->actingAs($this->user);

        $request = SchoolOutingActivityRequest::create('/', 'POST', [
            'name' => '  Refrigerio  ',
        ]);
        $this->prepare($request);

        $this->assertTrue($request->authorize());
        $this->assertSame('Refrigerio', $request->input('name'));
        $this->assertSame('required', $request->rules()['name'][0]);
        $this->assertSame('string', $request->rules()['name'][1]);
    }

    public function testSchoolOutingRequestsNormalizeMoneyAndNotes(): void
    {
        $this->actingAs($this->user);

        $outing = SchoolOutingRequest::create('/', 'POST', [
            'name' => '  Museo  ',
            'amount_per_player' => '$ 35.500',
            'notes' => '',
        ]);
        $this->prepare($outing);

        $this->assertTrue($outing->authorize());
        $this->assertSame('Museo', $outing->input('name'));
        $this->assertSame('35.500', $outing->input('amount_per_player'));
        $this->assertNull($outing->input('notes'));

        $contribution = SchoolOutingContributionRequest::create('/', 'POST', [
            'amount' => '$ 10.250',
            'notes' => '',
        ]);
        $this->prepare($contribution);

        $this->assertTrue($contribution->authorize());
        $this->assertSame('10.250', $contribution->input('amount'));
        $this->assertNull($contribution->input('notes'));
        $this->assertSame(['required', 'date'], $contribution->rules()['contribution_date']);

        $participant = new SchoolOutingParticipantRequest();
        $this->assertTrue($participant->authorize());
        $this->assertSame(['required', 'array', 'min:1'], $participant->rules()['inscription_ids']);

        $status = new SchoolOutingStatusRequest();
        $this->assertTrue($status->authorize());
        $this->assertSame('required', $status->rules()['status'][0]);
    }

    public function testEvaluationRequestsExposeComparisonAndStoreRules(): void
    {
        $this->actingAs($this->user);

        $compare = new ComparePlayerEvaluationsRequest();
        $this->assertTrue($compare->authorize());
        $this->assertContains('different:period_a_id', $compare->rules()['period_b_id']);

        $store = StorePlayerEvaluationRequest::create('/', 'POST');
        $this->prepare($store);

        $this->assertTrue($store->authorize());
        $this->assertSame($this->school['id'], $store->input('school_id'));
        $this->assertSame(['nullable', 'array'], $store->rules()['scores']);
        $this->assertContains('distinct', $store->rules()['scores.*.template_criterion_id']);
    }

    public function testBackOfficeSchoolRequestsBuildSlugAndRequireSuperAdmin(): void
    {
        $superAdmin = $this->createUser(['school_id' => $this->school['id']], ['super-admin']);
        $this->actingAs($superAdmin);

        $create = SchoolCreateRequest::create('/', 'POST', [
            'name' => 'Escuela Sur FC',
        ]);
        $this->prepare($create);

        $this->assertTrue($create->authorize());
        $this->assertSame('escuela-sur-fc', $create->input('slug'));
        $this->assertSame(['required', 'email'], $create->rules()['email']);

        $update = SchoolUpdateRequest::create('/', 'PUT', [
            'name' => 'Escuela Norte FC',
        ]);
        $this->prepare($update);

        $this->assertTrue($update->authorize());
        $this->assertSame('escuela-norte-fc', $update->input('slug'));
        $this->assertSame(['required', 'bool'], $update->rules()['is_enable']);
    }

    public function testBasicUserRequestsShareNameAndEmailRules(): void
    {
        foreach ([new StoreUserRequest(), new UpdateUserRequest()] as $request) {
            $this->assertTrue($request->authorize());
            $this->assertSame(['required'], $request->rules()['name']);
            $this->assertSame(['required', 'string', 'email:rfc,dns'], $request->rules()['email']);
        }
    }

    public function testLegacyUserRequestsKeepRolIdAndExistingEmailRules(): void
    {
        $store = new LegacyUserStoreRequest();
        $this->assertTrue($store->authorize());
        $this->assertSame(['required'], $store->rules()['name']);
        $this->assertSame(['required', 'email', 'unique:users'], $store->rules()['email']);
        $this->assertSame(['required'], $store->rules()['rol_id']);

        $update = new LegacyUserUpdateRequest();
        $this->assertTrue($update->authorize());
        $this->assertSame('required', $update->rules()['name']);
        $this->assertSame('required|email|exists:users,email', $update->rules()['email']);
    }

    private function prepare(FormRequest $request): void
    {
        $method = new ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);
    }
}
