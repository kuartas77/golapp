<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Admin\SchoolPermissionsUpdateRequest;
use App\Http\Requests\API\Admin\SuperAdminSchoolStoreRequest;
use App\Http\Requests\API\Admin\SuperAdminSchoolUpdateRequest;
use App\Http\Resources\API\SchoolCollection;
use App\Models\School;
use App\Service\Admin\SuperAdminSchoolService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SchoolController extends Controller
{
    public function __construct(private SuperAdminSchoolService $superAdminSchoolService)
    {
    }

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
    public function store(SuperAdminSchoolStoreRequest $request): JsonResponse
    {
        $school = $this->superAdminSchoolService->store($request);

        return response()->json([
            'success' => true,
            'message' => 'Escuela creada correctamente.',
            'school' => [
                'id' => $school->id,
                'slug' => $school->slug,
                'name' => $school->name,
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show(School $school): JsonResponse
    {
        abort_unless(auth()->check() && auth()->user()->hasRole('super-admin'), Response::HTTP_FORBIDDEN);

        return response()->json($this->superAdminSchoolService->formData($school));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(SuperAdminSchoolUpdateRequest $request, School $school): JsonResponse
    {
        $school = $this->superAdminSchoolService->update($request, $school);

        return response()->json([
            'success' => true,
            'message' => 'Escuela actualizada correctamente.',
            'school' => [
                'id' => $school->id,
                'slug' => $school->slug,
                'name' => $school->name,
            ],
        ]);
    }

    public function options(): JsonResponse
    {
        abort_unless(auth()->check() && auth()->user()->hasRole('super-admin'), Response::HTTP_FORBIDDEN);

        return response()->json($this->superAdminSchoolService->options());
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
