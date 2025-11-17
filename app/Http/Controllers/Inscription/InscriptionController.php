<?php

namespace App\Http\Controllers\Inscription;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inscription\InscriptionRequest;
use App\Http\Requests\Inscription\InscriptionUpdateRequest;
use App\Models\Inscription;
use App\Repositories\InscriptionRepository;
use Illuminate\Http\JsonResponse;
class InscriptionController extends Controller
{

    public function __construct(private InscriptionRepository $repository, private $response = [])
    {
        $this->repository = $repository;
    }

    /**
     * @param InscriptionRequest $request
     * @return JsonResponse
     */
    public function store(InscriptionRequest $request): JsonResponse
    {
        abort_unless(isAdmin() || isSchool(), 401);

        $inscription = $this->repository->createInscription(requestData: $request->validated());

        $this->response['success'] = $inscription;
        $this->response['message'] = $inscription ? __('messages.ins_create_success') : __('messages.ins_create_failure');
        return response()->json($this->response);
    }


    /**
     * @param $id
     * @return JsonResponse
     */
    public function edit($id): JsonResponse
    {
        abort_unless(isAdmin() || isSchool(), 401);

        $this->response = $this->repository->searchInsUniqueCode($id);

        return response()->json($this->response);
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

        $this->response['success'] = $inscription;
        $this->response['message'] = $inscription ? __('messages.ins_update_success') : __('messages.ins_create_failure');
        return response()->json($this->response);
    }

    public function destroy(Inscription $inscription): JsonResponse
    {
        abort_unless(isAdmin() || isSchool(), 401);

        $delete = $this->repository->disable($inscription);

        $this->response['success'] = $delete;
        $this->response['message'] = $delete ? __('messages.ins_delete_success') : __('messages.ins_create_failure');
        return response()->json($this->response);
    }
}
