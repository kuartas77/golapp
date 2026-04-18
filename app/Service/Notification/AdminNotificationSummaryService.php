<?php

declare(strict_types=1);

namespace App\Service\Notification;

use App\Models\PaymentRequest;
use App\Models\School;
use App\Models\UniformRequest;
use Illuminate\Support\Facades\Cache;

class AdminNotificationSummaryService
{
    public function forSchool(School $school): array
    {
        $paymentRequests = Cache::remember(
            "admin.notification.payment_request.{$school->id}",
            now()->addSeconds(30),
            fn () => PaymentRequest::query()
                ->where('school_id', $school->id)
                ->whereHas('invoice', fn ($query) => $query->where('status', '<>', 'paid'))
                ->count()
        );

        $uniformRequests = Cache::remember(
            "admin.notification.uniform_request.{$school->id}",
            now()->addSeconds(30),
            fn () => UniformRequest::query()
                ->where('school_id', $school->id)
                ->where('status', 'PENDING')
                ->count()
        );

        return [
            'payment_requests' => $paymentRequests,
            'uniform_requests' => $uniformRequests,
            'total' => $paymentRequests + $uniformRequests,
        ];
    }
}
