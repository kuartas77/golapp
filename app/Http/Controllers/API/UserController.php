<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\UserRequest;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\API\UserResource;
use App\Http\Resources\API\UserCollection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{

    public function __construct(private UserRepository $userRepository)
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(UserRequest $request)
    {
        // event(new Registered(auth()->user()));
 
        $users = User::query()
        ->when(isSchool(), fn($query) => $query->where('school_id', auth()->user()->school_id))
        ->when($request->orderBy, fn($query) => $query->orderBy($request->orderBy, $request->order))
        ->orderByRaw('-school_id ASC');
        
        return new UserCollection($users->paginate($request->per_page));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUserRequest $request
     * @return UserResource
     */
    public function store(StoreUserRequest $request): UserResource
    {
        $user = $this->userRepository->create($request);
        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return UserResource
     */
    public function show(User $user): UserResource
    {
        return new UserResource($user->load(['profile','school']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return UserResource
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $this->userRepository->update($user, $request);
        return new UserResource($user->refresh());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return Response
     */
    public function destroy(User $user): Response
    {
        $user->delete();
        return response()->noContent();
    }


}
