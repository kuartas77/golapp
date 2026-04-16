<?php

declare(strict_types=1);

namespace App\Http\Resources\API\Portal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuardianUserResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'names' => $this->names,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'profession' => $this->profession,
            'business' => $this->business,
            'position' => $this->position,
            'identification_card' => $this->identification_card,
            'email_verified_at' => optional($this->email_verified_at)?->toISOString(),
            'last_login_at' => optional($this->last_login_at)?->toISOString(),
        ];
    }
}
