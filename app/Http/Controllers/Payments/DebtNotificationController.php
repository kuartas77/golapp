<?php

declare(strict_types=1);

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Http\Requests\DebtNotificationIndexRequest;
use App\Http\Requests\SendDebtNotificationsRequest;
use App\Service\Payment\DebtNotificationService;
use Illuminate\Http\JsonResponse;

class DebtNotificationController extends Controller
{
    public function __construct(private readonly DebtNotificationService $debtNotifications) {}

    public function index(DebtNotificationIndexRequest $request): JsonResponse
    {
        return $this->debtNotifications->datatableRows($request);
    }

    public function send(SendDebtNotificationsRequest $request): JsonResponse
    {
        $result = $this->debtNotifications->send(
            getSchool($request->user()),
            (string) $request->validated('month'),
            $request->validated('payment_ids')
        );

        return response()->json([
            'message' => $this->resultMessage($result),
            'data' => $result,
        ]);
    }

    private function resultMessage(array $result): string
    {
        if ($result['queued_count'] === 0) {
            return 'Los deportistas seleccionados ya recibieron una notificación de deuda hoy.';
        }

        $message = $result['queued_count'] === 1
            ? 'La notificación de deuda fue encolada correctamente.'
            : "Las {$result['queued_count']} notificaciones de deuda fueron encoladas correctamente.";

        if ($result['skipped_count'] > 0) {
            $message .= " Se omitieron {$result['skipped_count']} porque ya habían sido enviadas hoy.";
        }

        return $message;
    }
}
