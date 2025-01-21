<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Actions\Create;

use Closure;
use App\Modules\Inscriptions\Actions\Create\Passable;
use App\Models\TrainingGroup;
use App\Models\School;
use App\Models\Player;
use App\Models\Inscription;

final class CreateInscriptionAction implements IContractPassable
{
    private Player $player;

    private School $school;

    private Inscription $inscription;

    private array $attributes;

    public function handle(Passable $passable, Closure $next)
    {
        $this->school = $passable->getSchool();

        $this->player = $passable->getPlayer();

        $this->attributes = $this->setAttributes($passable);

        $this->upsertInscription();

        $passable->setInscription($this->inscription->load(['school']));

        return $next($passable);
    }

    private function upsertInscription(): void
    {
        $this->inscription = Inscription::query()->withTrashed()->updateOrCreate([
            'unique_code' => $this->attributes['unique_code'],
            'year' => $this->attributes['year'],
            'school_id' => $this->school->id
        ], $this->attributes);
    }

    private function setAttributes(Passable $passable): array
    {
        $startDate = in_array(now()->month, [11, 12]) ? now()->month(1)->day(15)->addYear() : now();

        return [
            'player_id' => $this->player->id,
            'unique_code' => $this->player->unique_code,
            'year' => $startDate->year,
            'start_date' => $startDate->format('Y-m-d'),
            'category' => $this->player->category,
            'training_group_id' => TrainingGroup::orderBy('id')->firstWhere('school_id', $this->school->id)->id,
            'competition_group_id' => null,
            'photos' => true,
            'copy_identification_document' => $passable->getPropertyFromData('player_document') !== null,
            'eps_certificate' => $passable->getPropertyFromData('medical_certificate') !== null,
            'medic_certificate' => $passable->getPropertyFromData('medical_certificate') !== null,
            'study_certificate' => $passable->getPropertyFromData('tutor_document') !== null,
            'overalls' => false,
            'ball' => false,
            'bag' => false,
            'presentation_uniform' => false,
            'competition_uniform' => false,
            'tournament_pay' => false,
            'scholarship' => false,
            'period_one' => null,
            'period_two' => null,
            'period_three' => null,
            'period_four' => null,
            'pre_inscription' => true,
            'school_id' => $this->school->id
        ];
    }
}
