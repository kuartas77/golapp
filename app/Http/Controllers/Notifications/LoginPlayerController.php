<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\LoginPlayerResource;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginPlayerController extends Controller
{

    protected function guard()
    {
        return Auth::guard('players');
    }
/**
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            'uniqueCode' => 'required',
            'password' => 'required',
        ]);

        $player = Player::query()
            ->where('unique_code', $request->input('uniqueCode'))
            ->where('identification_document', $request->input('password'))
            ->whereHas('inscription')->first();

        if(!$player || !Hash::check($request->input('password'), $player->password)) {

            throw ValidationException::withMessages([
                'uniqueCode' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->generateResponse($player);
    }

    public function refresh(Request $request): LoginPlayerResource
    {
        $player = $request->user();
        $player->tokens()->delete();
        return $this->generateResponse($player);
    }

    private function generateResponse(Player $player): LoginPlayerResource
    {
        $player->tokens()->delete();


        $player->abilities = [
            'notification-index',
            'request-index',
            'request-store',
            'payment-index',
            'payment-update',
        ];

        $player->load(['schoolData', 'inscription.trainingGroup']);

        return new LoginPlayerResource($player);
    }
}
