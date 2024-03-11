<?php

namespace App\Http\Controllers\Inscription;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscription\InscriptionRequest;
use App\Http\Requests\Inscription\InscriptionUpdateRequest;
use App\Models\Inscription;
use App\Repositories\InscriptionRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InscriptionController extends Controller
{

    public function __construct(private InscriptionRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('inscription.index');
    }

    /**
     * @param InscriptionRequest $request
     * @return JsonResponse
     */
    public function store(InscriptionRequest $request): JsonResponse
    {
        abort_unless(isAdmin() || isSchool(), 401);

        $inscription = $this->repository->createInscription(requestData: $request->validated());

        if ($inscription) {
            return response()->json([__('messages.ins_create_success')]);
        } else {
            return response()->json([__('messages.ins_create_failure')], 422);
        }
    }


    /**
     * @param $id
     * @return JsonResponse
     */
    public function edit($id): JsonResponse
    {
        abort_unless(isAdmin() || isSchool(), 401);

        return response()->json($this->repository->searchInsUniqueCode($id));
    }

    /**
     * @param InscriptionUpdateRequest $request
     * @param Inscription $inscription
     * @return JsonResponse
     */
    public function update(InscriptionUpdateRequest $request, Inscription $inscription): JsonResponse
    {
        abort_unless(isAdmin() || isSchool(), 401);

        $inscription = $this->repository->updateInscription(requestData: $request->validated(), inscription: $inscription);

        if ($inscription) {
            return response()->json([__('messages.ins_update_success')]);
        } else {
            return response()->json([__('messages.ins_create_failure')], 422);
        }
    }

    public function destroy(Inscription $inscription): RedirectResponse
    {
        $this->repository->disable($inscription);
        return back();
    }
}
