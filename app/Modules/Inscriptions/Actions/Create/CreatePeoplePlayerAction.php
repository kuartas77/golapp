<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Actions\Create;

use Closure;
use App\Models\People;
use App\Repositories\PeopleRepository;
use App\Models\Player;

final class CreatePeoplePlayerAction implements IContractPassable
{
    private Player $player;

    private array $attributes;

    public function handle(Passable $passable, Closure $next)
    {
        $this->player = $passable->getPlayer();

        $this->attributes = $this->setAttributes($passable);

        $passable->setTutor($this->attributes[0]);

        $guardianAttributes = $this->attributes[0];
        $existingGuardian = People::query()->firstWhere('identification_card', $guardianAttributes['identification_card']);

        $peopleRepository = app(PeopleRepository::class, ['model' => new People()]);

        $peopleIds = $peopleRepository->getPeopleIds($this->attributes);

        $this->player->people()->sync($peopleIds);
        $guardian = People::query()->find($peopleIds->first());

        if ($guardian) {
            $passable->setGuardian($guardian);
            $passable->setShouldInviteGuardian(
                checkEmail($guardian->email)
                && blank($existingGuardian?->password)
            );
        }

        $passable->setPlayer($this->player);

        return $next($passable);
    }

    private function setAttributes(Passable $passable): array
    {
        $people = [];

        $people[] = [
            'names' => $passable->getPropertyFromData('tutor_name'),
            'identification_card' => $passable->getPropertyFromData('tutor_doc'),
            'tutor' => true,
            'relationship' => $passable->getPropertyFromData('tutor_relationship'),
            'phone' => null,
            'email' => $passable->getPropertyFromData('tutor_email'),
            'mobile' => $passable->getPropertyFromData('tutor_phone'),
            'profession' => $passable->getPropertyFromData('tutor_position_held'),
            'business' => $passable->getPropertyFromData('tutor_work'),
            'position' => null,
        ];

        if($dadName = $passable->getPropertyFromData('dad_name')){
            $people[] = [
                'names' => $dadName,
                'identification_card' => $passable->getPropertyFromData('dad_doc'),
                'tutor' => false,
                'relationship' => $passable->getPropertyFromData('relationship_dad'),
                'phone' => null,
                'email' => null,
                'mobile' => $passable->getPropertyFromData('dad_phone'),
                'profession' => null,
                'business' => $passable->getPropertyFromData('dad_work'),
                'position' => null,
            ];
        }

        if($momName = $passable->getPropertyFromData('mom_name')){
            $people[] = [
                'names' => $momName,
                'identification_card' => $passable->getPropertyFromData('mom_doc'),
                'tutor' => false,
                'relationship' => $passable->getPropertyFromData('relationship_mom'),
                'phone' => null,
                'email' => null,
                'mobile' => $passable->getPropertyFromData('mom_phone'),
                'profession' => null,
                'business' => $passable->getPropertyFromData('mom_work'),
                'position' => null,
            ];
        }

        return $people;
    }

}
