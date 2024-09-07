<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\ExportClaimNotification;

class NotifyUserOfCompletedExport implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(private User $user, private string $filename)
    {

    }

    public function handle()
    {
        $this->user->notify(new ExportClaimNotification($this->filename));
    }
}
