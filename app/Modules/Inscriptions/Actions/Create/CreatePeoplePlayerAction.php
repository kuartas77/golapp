<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Actions\Create;

use Closure;
use App\Repositories\PeopleRepository;
use App\Models\Player;
use App\Models\People;

final class CreatePeoplePlayerAction implements IContractPassable
{
    private Player $player;

    private array $attributes;

    public function handle(Passable $passable, Closure $next)
    {
        $this->player = $passable->getPlayer();

        $this->attributes = $this->setAttributes($passable);

        $passable->setTutor($this->attributes);

        $existingGuardian = People::query()->firstWhere('identification_card', $this->attributes['identification_card']);

        $peopleRepository = app(PeopleRepository::class);

        $guardian = $peopleRepository->createOrUpdatePeople($this->attributes);

        $this->player->people()->syncWithoutDetaching([$guardian->id]);

        $passable->setGuardian($guardian);
        $passable->setShouldInviteGuardian(
            checkEmail($guardian->email)
            && blank($existingGuardian?->password)
        );
        $passable->setPlayer($this->player);

        return $next($passable);
    }

    private function setAttributes(Passable $passable): array
    {
        return [
            'names' => $passable->getPropertyFromData('tutor_name'),
            'identification_card' => $passable->getPropertyFromData('tutor_doc'),
            'tutor' => true,
            'relationship' => $passable->getPropertyFromData('tutor_relationship'),
            'phone' => null,
            'email' => $passable->getPropertyFromData('tutor_email'),
            'mobile' => $passable->getPropertyFromData('tutor_phone'),
            'profession' => null,
            'business' => $passable->getPropertyFromData('tutor_work'),
            'position' => $passable->getPropertyFromData('tutor_position_held'),
        ];
    }

}
