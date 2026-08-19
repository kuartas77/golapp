<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class SpanishTranslationsTest extends TestCase
{
    public function test_framework_and_application_messages_are_translated_to_spanish(): void
    {
        $this->app->setLocale('es');

        $this->assertSame('Saludos,', __('Regards,'));
        $this->assertSame('Verificación realizada correctamente.', __('Verified successfully'));
        $this->assertSame('¡Enlace de verificación enviado!', __('Verification link sent!'));
        $this->assertSame('Error', __('messages.error'));
        $this->assertSame(
            '¡Se Ha Presentado Un Error!. Ha Sido Reportada La Falla',
            __('messages.match_fail'),
        );
    }
}
