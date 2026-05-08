<?php

namespace App\Http\Controllers\API\Notifications\Guardians\Concerns;

use App\Models\People;
use Illuminate\Http\Request;

trait ResolvesGuardianPlayers
{
    private function guardian(Request $request): People
    {
        /** @var People $guardian */
        $guardian = $request->user();

        abort_unless($guardian instanceof People && $guardian->tutor, 401, 'No hay una sesión de acudiente activa.');

        return $guardian;
    }

    private function eligiblePlayers(Request $request)
    {
        return $this->guardianAccessService->eligiblePlayersQuery($this->guardian($request))
            ->with([
                'schoolData',
                'inscription.trainingGroup',
            ])
            ->orderBy('players.names')
            ->orderBy('players.last_names')
            ->get();
    }
}
