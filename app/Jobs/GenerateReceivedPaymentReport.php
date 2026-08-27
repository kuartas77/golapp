<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\School;
use App\Models\User;
use App\Notifications\ReceivedPaymentReportNotification;
use App\Service\Reports\ReceivedPaymentReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class GenerateReceivedPaymentReport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 900;

    public function __construct(
        public int $schoolId,
        public int $userId,
        public array $filters,
    ) {
        $this->onQueue('golapp_default');
    }

    public function handle(ReceivedPaymentReportService $service): void
    {
        $school = School::query()->findOrFail($this->schoolId);
        $user = User::query()->findOrFail($this->userId);
        $attachment = $service->pdfAttachment(
            $this->filters + ['school_id' => $school->id],
            $school,
        );

        $user->notify(new ReceivedPaymentReportNotification($attachment, $school->name));
    }
}
