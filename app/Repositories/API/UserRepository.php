<?php

declare(strict_types=1);

namespace App\Repositories\API;

use App\Models\User;
use App\Traits\ErrorTrait;
use Illuminate\Http\Request;

class UserRepository
{
    use ErrorTrait;

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUsersPaginate(Request $request)
    {

    }
}
