<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Admin\SchoolPermissionsUpdateRequest;
use App\Http\Resources\API\SchoolCollection;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $schools = School::withCount([
            'users', 'inscriptions', 'players', 'payments', 'assists', 'skillControls', 'matches', 'tournaments', 'trainingGroups', 'competitionGroups', 'incidents'
        ])->when($request->orderBy, fn($query) => $query->orderBy($request->orderBy, $request->order))
            ->orderByRaw('-id ASC');

        return new SchoolCollection($schools->paginate($request->per_page));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id): \Illuminate\Http\RedirectResponse
    {
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        return back();
    }

    public function permissions(School $school): JsonResponse
    {
        return response()->json([
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
                'slug' => $school->slug,
            ],
            'permissions' => $school->getResolvedSchoolPermissions(),
            'catalog' => collect(School::permissionCatalog())
                ->map(fn (array $permission, string $key) => [
                    'key' => $key,
                    'label' => $permission['label'] ?? $key,
                    'description' => $permission['description'] ?? '',
                    'group' => $permission['group'] ?? 'General',
                    'default' => (bool) ($permission['default'] ?? false),
                ])
                ->values(),
        ]);
    }

    public function updatePermissions(SchoolPermissionsUpdateRequest $request, School $school): JsonResponse
    {
        $school->forceFill([
            'school_permissions' => School::normalizeSchoolPermissions($request->validated('permissions')),
        ])->save();

        School::forgetCachedSchool($school->id);
        \Illuminate\Support\Facades\Cache::forget('admin.schools');

        return response()->json([
            'success' => true,
            'permissions' => $school->fresh()->getResolvedSchoolPermissions(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id): \Illuminate\Http\RedirectResponse
    {
        return back();
    }
}
