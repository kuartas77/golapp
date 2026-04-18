<?php

declare(strict_types=1);

namespace App\Repositories\API;

use App\Models\User;
use Illuminate\Http\Request;

class UserRepository
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUsersPaginate(Request $request)
    {
        $perPage = max(1, min((int) $request->input('per_page', 15), 100));
        $query = $this->user->query()->with(['roles', 'profile', 'school']);

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->input('school_id'));
        } elseif ($request->user()) {
            $query->where('school_id', $request->user()->school_id);
        }

        return $query->paginate($perPage);
    }
}
