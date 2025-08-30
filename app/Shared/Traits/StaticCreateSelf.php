<?php

declare(strict_types=1);

namespace App\Shared\Traits;

use Illuminate\Http\Request;

trait StaticCreateSelf
{
    public static function fromArray(array $values): self
    {
        $dto = new self;

        foreach ($values as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->$key = $value;
            }
        }

        return $dto;
    }

    public static function fromRequest(Request $request): self
    {
        return self::fromArray($request->validated());
    }
}
