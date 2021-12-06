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
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InscriptionController extends Controller
{

    /**
     * @var InscriptionRepository
     */
    private $repository;

    public function __construct(InscriptionRepository $repository)
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
        abort_unless(isAdmin(), 401);
        $inscription = $this->repository->setInscription($request);
        if (is_null($inscription) || $inscription->getDirty() > 0) {
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
        abort_unless(isAdmin(), 401);

        return response()->json($this->repository->searchInsUniqueCode($id));
    }

    /**
     * @param InscriptionUpdateRequest $request
     * @param Inscription $inscription
     * @return JsonResponse
     */
    public function update(InscriptionUpdateRequest $request, Inscription $inscription): JsonResponse
    {
        abort_unless(isAdmin(), 401);

        $inscription = $this->repository->setInscription($request, false, $inscription);

        if (is_null($inscription) || $inscription->getDirty() > 0) {
            return response()->json([__('messages.ins_update_success')]);
        } else {
            return response()->json([__('messages.ins_create_failure')], 422);
        }
    }
}
