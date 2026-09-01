<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\UserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\API\ProfileResource;
use App\Http\Resources\API\UserCollection;
use App\Http\Resources\API\UserResource;
use App\Models\User;
use App\Repositories\UserRepository;
use App\Support\SchoolModuleAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
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
            ->when(isSchool(), fn ($query) => $query->where('school_id', getSchool(auth()->user())->id))
            ->when($request->orderBy, fn ($query) => $query->orderBy($request->orderBy, $request->order))
            ->orderByRaw('-school_id ASC');

        return new UserCollection($users->paginate($request->per_page));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): UserResource
    {
        $user = $this->userRepository->create($request);

        return new UserResource($user);
    }

    public function roleOptions(): JsonResponse
    {
        $labels = [
            'school' => 'School',
            'instructor' => 'Instructor',
            User::ASSISTANT => 'Auxiliar administrativo',
            User::VIEWER => 'Visualizador',
        ];

        $roles = Role::query()
            ->whereIn('name', array_keys($labels))
            ->orderByRaw("CASE name WHEN 'school' THEN 1 WHEN 'instructor' THEN 2 WHEN 'assistant' THEN 3 ELSE 4 END")
            ->get(['id', 'name'])
            ->map(fn (Role $role) => [
                'value' => $role->id,
                'name' => $role->name,
                'label' => $labels[$role->name],
            ])
            ->values();

        return response()->json(['data' => $roles]);
    }

    public function moduleOptions(): JsonResponse
    {
        $school = getSchool(auth()->user());

        $modules = collect(SchoolModuleAccess::catalog())
            ->map(fn (array $definition, string $key) => [
                'key' => $key,
                'label' => $definition['label'],
                'group' => $definition['group'],
                'school_enabled' => $school->hasSchoolPermission($key),
            ])
            ->values();

        return response()->json(['data' => $modules]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): UserResource
    {
        $this->authorizeCurrentSchoolUser($user);

        return new UserResource($user->load(['profile', 'school', 'roles', 'permissions']));
    }

    public function profile(User $user): JsonResponse
    {
        $this->authorizeCurrentSchoolUser($user);

        $profile = $user->profile()->firstOrCreate([]);

        return (new ProfileResource($profile->load('user'), false))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $this->authorizeCurrentSchoolUser($user);
        $this->userRepository->update($user, $request);

        return new UserResource($user->load(['profile', 'school', 'roles', 'permissions']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): Response
    {
        $this->authorizeCurrentSchoolUser($user);
        $user->delete();

        return response()->noContent();
    }

    private function authorizeCurrentSchoolUser(User $user): void
    {
        $schoolId = (int) getSchool(auth()->user())->id;

        abort_unless(
            DB::table('schools_user')
                ->where('school_id', $schoolId)
                ->where('user_id', $user->id)
                ->exists(),
            403
        );
    }
}
