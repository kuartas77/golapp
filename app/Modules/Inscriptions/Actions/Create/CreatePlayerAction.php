<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Actions\Create;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Closure;
use App\Traits\UploadFile;
use App\Models\Player;
use App\Models\School;

final class CreatePlayerAction implements IContractPassable
{
    use UploadFile;

    private Player $player;

    private School $school;

    private array $attributes = [];

    public function handle(Passable $passable, Closure $next)
    {
        $this->school = $passable->getSchool();

        $this->getPlayer($passable);

        $this->setAttributes($passable);

        $this->upsertPlayer();

        $passable->setPlayer($this->player);

        return $next($passable);
    }

    private function getPlayer(Passable $passable)
    {
        $this->player = Player::query()
            ->where('identification_document', $passable->getPropertyFromData('identification_document'))
            ->where('school_id', $this->school->id)
            ->firstOr(callback: fn(): Player => new Player());
    }

    private function setAttributes(Passable $passable)
    {
        $uniqueCode = $this->player->exists ? $this->player->unique_code: $this->createUniqueCode($passable);
        $this->attributes = [
            'unique_code' => $uniqueCode,
            'names' => $passable->getPropertyFromData('names'),
            'last_names' => $passable->getPropertyFromData('last_names'),
            'gender' => $passable->getPropertyFromData('gender'),
            'date_birth' => $passable->getPropertyFromData('date_birth'),
            'place_birth' => $passable->getPropertyFromData('place_birth'),
            'identification_document' => $passable->getPropertyFromData('identification_document'),
            'rh' => $passable->getPropertyFromData('rh'),
            'photo' => $passable->getPropertyFromData('photo'),
            'category' => $passable->getPropertyFromData('category'),
            'position_field' => $passable->getPropertyFromData('position_field'),
            'dominant_profile' => $passable->getPropertyFromData('dominant_profile'),
            'school' => $passable->getPropertyFromData('school'),
            'degree' => $passable->getPropertyFromData('degree'),
            'address' => $passable->getPropertyFromData('address'),
            'municipality' => $passable->getPropertyFromData('municipality'),
            'neighborhood' => $passable->getPropertyFromData('neighborhood'),
            'zone' => $passable->getPropertyFromData('zone'),
            'commune' => $passable->getPropertyFromData('commune'),
            'phones' => $passable->getPropertyFromData('mobile'),
            'email' => $passable->getPropertyFromData('email'),
            'mobile' => $passable->getPropertyFromData('mobile'),
            'eps' => $passable->getPropertyFromData('eps'),
            'document_type' => $passable->getPropertyFromData('document_type'),
            'medical_history' => $passable->getPropertyFromData('medical_history'),
            'jornada' => $passable->getPropertyFromData('jornada'),
            'student_insurance' => $passable->getPropertyFromData('student_insurance'),
            'password' => Hash::make($this->player->identification_document),
            'school_id' => $this->school->id
        ];
    }

    private function createUniqueCode(Passable $passable): mixed
    {
        $year = $passable->getPropertyFromData('year');

        return createUniqueCode((string)$this->school->id, $year);
    }

    private function upsertPlayer(): void
    {
        foreach ($this->attributes as $attribute => $value) {
            if($attribute == 'photo' && $value instanceof UploadedFile){
                $this->attributes['photo'] = $this->uploadFile($value, $this->school->slug, 'players');
            }
        }

        $this->player = Player::query()->withTrashed()->updateOrCreate([
            'identification_document' => $this->attributes['identification_document'],
            'unique_code' => $this->attributes['unique_code'],
            'school_id' => $this->attributes['school_id']
        ], $this->attributes);
    }
}
