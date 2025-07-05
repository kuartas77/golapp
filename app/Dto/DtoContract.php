<?php

namespace App\Dto;

interface DtoContract
{
    public static function fromArray(array $data): DtoContract;
    public function toArray(): array;
}
