<?php

namespace App\Modules\Inscriptions\Actions\Create;

use App\Models\Inscription;
use App\Models\Player;
use App\Models\School;

final class Passable
{
    private Player $player;

    private Inscription $inscription;

    private School $school;

    private array $filePaths = [];

    private array $tutor = [];

    public function __construct(private readonly array $data)
    {
        //
    }

    public function getPropertyFromData(string $key): mixed
    {
        return data_get($this->data, $key);
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getInscription(): Inscription
    {
        return $this->inscription;
    }

    public function getSchool(): School
    {
        return $this->school;
    }

    public function getPaths(): array
    {
        return $this->filePaths;
    }

    public function getTutor(): array
    {
        return $this->tutor;
    }

    public function setPlayer(Player $player): void
    {
        $this->player = $player;
    }

    public function setInscription(Inscription $inscription): void
    {
        $this->inscription = $inscription;
    }

    public function setSchool(): void
    {
        $this->school = $this->getPropertyFromData('school_data');
    }

    public function setPaths(array $filePaths): void
    {
        $this->filePaths = $filePaths;
    }

    public function setTutor(array $tutor)
    {
        $this->tutor = $tutor;
    }
}
