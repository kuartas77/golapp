<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Requests\API\Notification\PaymentInvoiceRequest;
use App\Http\Requests\API\Notification\UniformFormRequest;
use App\Http\Requests\API\Portal\GuardianForgotPasswordRequest;
use App\Http\Requests\API\Portal\GuardianProfileUpdateRequest;
use App\Http\Requests\API\Portal\GuardianResetPasswordRequest;
use App\Http\Requests\API\RegisterRequest;
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

    private function prepare(FormRequest $request): void
    {
        $method = new ReflectionMethod($request, 'prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);
    }
}
