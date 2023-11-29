<?php

namespace App\Repositories\API;

use App\Models\User;
use App\Traits\ErrorTrait;
use Illuminate\Http\Request;

class UserRepository
{
    use ErrorTrait;

    private User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function getUsersPaginate(Request $request)
    {

    }
}
