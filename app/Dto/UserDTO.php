<?php

declare(strict_types=1);

namespace App\Dto;

use App\Shared\Traits\StaticCreateSelf;
use App\Shared\Traits\ToArray;

final class UserDTO
{
    use StaticCreateSelf;
    use ToArray;

    public readonly ?int $id;
    public readonly string $name;
    public readonly string $email;
    public readonly int $school_id;
}
