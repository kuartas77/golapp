<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Admin\GroupAssignmentCompetitionBoardRequest;
use App\Http\Requests\API\Admin\GroupAssignmentCompetitionMoveRequest;
use App\Http\Requests\API\Admin\GroupAssignmentTrainingBoardRequest;
use App\Http\Requests\API\Admin\GroupAssignmentTrainingMoveRequest;
use App\Service\Admin\GroupAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GroupAssignmentController extends Controller
{
    public function __construct(private readonly GroupAssignmentService $groupAssignmentService)
    {
    }

    public function trainingBoard(GroupAssignmentTrainingBoardRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->groupAssignmentService->getTrainingBoard(
                $request->integer('origin_group_id') ?: null,
                $request->integer('target_group_id') ?: null
            ),
        ]);
    }

    public function moveTraining(GroupAssignmentTrainingMoveRequest $request): JsonResponse
    {
        $updated = $this->groupAssignmentService->moveTraining(
            $request->integer('inscription_id'),
            $request->integer('target_group_id')
        );

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'No fue posible mover el deportista al grupo seleccionado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'success' => true,
            'message' => 'El deportista se movió correctamente.',
        ]);
    }

    public function competitionBoard(GroupAssignmentCompetitionBoardRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->groupAssignmentService->getCompetitionBoard(
                $request->integer('competition_group_id') ?: null
            ),
        ]);
    }

    public function moveCompetition(GroupAssignmentCompetitionMoveRequest $request): JsonResponse
    {
        $status = $this->groupAssignmentService->moveCompetition(
            $request->integer('inscription_id'),
            $request->integer('competition_group_id'),
            $request->boolean('assign')
        );

        [$success, $message] = match ($status) {
            1 => [true, 'El integrante se agregó al grupo correctamente.'],
            2 => [true, 'El integrante ya no pertenece al grupo seleccionado.'],
            4 => [false, 'El integrante ya existe en el grupo seleccionado.'],
            default => [false, 'No fue posible actualizar el grupo de competencia.'],
        };

        return response()->json([
            'success' => $success,
            'code' => $status,
            'message' => $message,
        ], $success ? Response::HTTP_OK : Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
