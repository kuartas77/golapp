<?php

declare(strict_types=1);

namespace App\Service\Portal;

use App\Models\People;
use App\Notifications\GuardianPasswordResetNotification;
use Illuminate\Support\Facades\Password;

class GuardianInvitationService
{
    public function hasUniqueTutorEmail(People $guardian): bool
    {
        if (!checkEmail($guardian->email)) {
            return false;
        }

        return !People::query()
            ->where('tutor', true)
            ->where('email', $guardian->email)
            ->whereKeyNot($guardian->id)
            ->exists();
    }

    public function invite(People $guardian): bool
    {
        if (!$this->hasUniqueTutorEmail($guardian)) {
            return false;
        }

        $token = Password::broker('guardians')->createToken($guardian);

        $guardian->forceFill([
            'invited_at' => now(),
        ])->save();

        $guardian->notify(new GuardianPasswordResetNotification($guardian, $token, true));

        return true;
    }
}
