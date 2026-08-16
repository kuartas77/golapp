<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Rules\Base64Image;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

final class Base64ImageTest extends TestCase
{
    public function test_it_accepts_a_real_png_signature(): void
    {
        $payload = 'data:image/png;base64,'.base64_encode((string) file_get_contents(public_path('img/user.png')));

        $this->assertFalse(Validator::make(
            ['signature' => $payload],
            ['signature' => [new Base64Image]]
        )->fails());
    }

    public function test_it_rejects_invalid_and_oversized_signature_payloads(): void
    {
        $invalid = Validator::make(
            ['signature' => 'data:image/png;base64,'.base64_encode('not-an-image')],
            ['signature' => [new Base64Image]]
        );
        $oversized = Validator::make(
            ['signature' => 'data:image/png;base64,'.base64_encode(str_repeat('a', 1048577))],
            ['signature' => [new Base64Image]]
        );

        $this->assertTrue($invalid->fails());
        $this->assertTrue($oversized->fails());
    }
}
