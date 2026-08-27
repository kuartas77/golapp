<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\ExportClaimNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class NotifyUserOfCompletedExport implements ShouldQueue
{
    use Queueable, SerializesModels;

    private string $schoolName = '';

    public function __construct(private User $user, private string $filename, string $schoolName = '')
    {
        $this->schoolName = $schoolName;
    }

    public function handle(): void
    {
        $this->user->notify(new ExportClaimNotification($this->filename, $this->schoolName));
    }
}
