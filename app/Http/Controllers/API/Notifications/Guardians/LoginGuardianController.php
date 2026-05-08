<?php

namespace App\Http\Controllers\API\Notifications\Guardians;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Portal\GuardianLoginRequest;
use App\Http\Resources\API\Notification\Guardians\LoginGuardianResource;
use App\Models\People;
use App\Service\Notification\TopicService;
use App\Service\Portal\GuardianAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginGuardianController extends Controller
{
    public function __construct(private GuardianAccessService $guardianAccessService)
    {
    }

    public function login(GuardianLoginRequest $request): JsonResponse
    {
        $guardians = People::query()
            ->where('tutor', true)
            ->where('email', $request->validated('email'))
            ->get();

        if ($guardians->count() !== 1) {
            throw ValidationException::withMessages([
                'email' => ['No fue posible identificar una cuenta única para este correo.'],
            ]);
        }

        /** @var People $guardian */
        $guardian = $guardians->first();

        if (blank($guardian->password) || !Hash::check((string) $request->validated('password'), $guardian->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        if (!$this->guardianAccessService->hasEligiblePlayers($guardian)) {
            throw ValidationException::withMessages([
                'email' => ['Tu acceso está temporalmente bloqueado porque no tienes jugadores vigentes este año o se ha deshabilitado la plataforma de acudientes.'],
            ]);
        }

        $guardian->forceFill(['last_login_at' => now()])->save();

        return $this->generateResponse($request, $guardian);
    }

    public function refresh(Request $request): JsonResponse
    {
        /** @var People $guardian */
        $guardian = $request->user();

        abort_unless($guardian instanceof People, 401, 'No hay una sesión de acudiente activa.');

        return $this->generateResponse($request, $guardian);
    }

    public function logout(Request $request): JsonResponse
    {
        /** @var People|null $guardian */
        $guardian = $request->user();

        if ($guardian instanceof People) {
            $guardian->tokens()->delete();
        }

        return response()->json([
            'data' => [
                'message' => 'Logout exitoso.',
            ],
        ]);
    }

    private function generateResponse(Request $request, People $guardian): JsonResponse
    {
        $guardian->tokens()->delete();

        $guardian->abilities = [
            'notification-index',
            'request-index',
            'request-store',
            'payment-index',
            'payment-update',
        ];

        $guardian->notification_players = $this->guardianAccessService->eligiblePlayersQuery($guardian)
            ->with(['schoolData', 'inscription.trainingGroup', 'inscription.competitionGroup'])
            ->orderBy('players.names')
            ->orderBy('players.last_names')
            ->get();
        $guardian->notification_topics = $guardian->notification_players
            ->flatMap(fn ($player) => TopicService::generatePlayerTopics($player))
            ->unique()
            ->values()
            ->all();

        return response()->json([
            'data' => (new LoginGuardianResource($guardian))->resolve($request),
        ]);
    }
}
