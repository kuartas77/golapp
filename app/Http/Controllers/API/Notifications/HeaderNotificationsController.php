<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\Notifications;

use App\Http\Controllers\Controller;
use App\Service\Notification\AdminNotificationSummaryService;
use Illuminate\Http\JsonResponse;

class HeaderNotificationsController extends Controller
{
    public function __construct(private AdminNotificationSummaryService $notificationSummaryService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json(
            $this->notificationSummaryService->forSchool(getSchool(auth()->user()))
        );
    }
}
