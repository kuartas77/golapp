<?php

namespace App\Http\Controllers\API\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Notification\UniformFormRequest;
use App\Http\Resources\API\Notification\UniformRequest\UniformRequestCollection;
use App\Http\Resources\API\Notification\UniformRequest\UniformRequestResource;
use App\Http\Resources\API\Notification\UniformRequest\UniformRequestStatistcsResource;
use App\Models\UniformRequest as ModelsUniformRequest;
use App\Repositories\UniformRequestRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UniformRequestController extends Controller
{
    public function __construct(private UniformRequestRepository $repository)
    {

    }

    public function statistics(): UniformRequestStatistcsResource
    {
        return new UniformRequestStatistcsResource($this->repository->uniformRequestPlayer());
    }

    public function index(): UniformRequestCollection
    {
        return new UniformRequestCollection($this->repository->uniformRequestPlayer());
    }

    public function store(UniformFormRequest $request)
    {
        $uniformRequest = $this->repository->store($request->validated());

        return new UniformRequestResource($uniformRequest);
    }

    public function show(ModelsUniformRequest $uniformRequest): UniformRequestResource
    {
        return new UniformRequestResource($uniformRequest);
    }

    public function cancel(ModelsUniformRequest $uniformRequest): JsonResponse
    {
        $success = $this->repository->cancel($uniformRequest);
        return response()->json(['success' => $success]);
    }
}
