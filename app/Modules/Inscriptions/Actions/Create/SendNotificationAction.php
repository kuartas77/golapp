<?php

namespace App\Modules\Inscriptions\Actions\Create;

use Illuminate\Support\Facades\Notification;
use Closure;
use App\Notifications\InscriptionNotification;
use App\Modules\Inscriptions\Jobs\DeleteDocuments;
use App\Models\Player;
use App\Models\Inscription;

class SendNotificationAction implements IContractPassable
{
    private Player $player;
    private Inscription $inscription;
    private array $paths = [];

    public function handle(Passable $passable, Closure $next)
    {
        $this->paths = $passable->getPaths();

        $this->player = $passable->getPlayer();

        $this->inscription = $passable->getInscription();

        $this->sendNotification($passable);

        return $next($passable);
    }

    private function sendNotification($passable)
    {
        $destinations = [];
        $playerMail = data_get($this->player, 'email');
        $tutorMail = $passable->getPropertyFromData('tutor_email');

        if (checkEmail($playerMail)) {
            $destinations[$playerMail] = $this->player->name . ' ' . $this->player->last_names;
        }

        if (checkEmail($tutorMail)) {
            $destinations[$tutorMail] = $passable->getPropertyFromData('tutor_name');
        }

        if (!empty($destinations)) {

            $contracts = [
                $this->paths['contract_one'],
                $this->paths['contract_two']
            ];

            Notification::route('mail', $destinations)->notify(
                (new InscriptionNotification($this->inscription, $contracts))->onQueue('emails')
            );

            dispatch(new DeleteDocuments($this->player->unique_code))->delay(now()->addDay())->onQueue('emails');
        }
    }
}
