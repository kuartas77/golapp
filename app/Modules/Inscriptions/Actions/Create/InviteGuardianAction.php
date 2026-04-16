<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Actions\Create;

use App\Service\Portal\GuardianInvitationService;
use Closure;

final class InviteGuardianAction implements IContractPassable
{
    public function __construct(private GuardianInvitationService $guardianInvitationService)
    {
    }

    public function handle(Passable $passable, Closure $next)
    {
        $guardian = $passable->getGuardian();

        if ($guardian && $passable->shouldInviteGuardian()) {
            $this->guardianInvitationService->invite($guardian);
        }

        return $next($passable);
    }
}
