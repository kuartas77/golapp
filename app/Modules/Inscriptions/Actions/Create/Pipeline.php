<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Actions\Create;

use Illuminate\Pipeline\Pipeline as BasePipeline;
use App\Modules\Inscriptions\Actions\Create\SendDocumentsAction;
use App\Modules\Inscriptions\Actions\Create\Passable;
use App\Modules\Inscriptions\Actions\Create\CreatePlayerAction;
use App\Modules\Inscriptions\Actions\Create\CreatePeoplePlayerAction;
use App\Modules\Inscriptions\Actions\Create\CreateInscriptionAction;
use App\Modules\Inscriptions\Actions\Create\CreateContractAction;

final class Pipeline
{
    public static function execute(array $data): void
    {
        $passable = app(abstract: Passable::class, parameters: ['data' => $data]);

        $passable->setSchool();

        app(BasePipeline::class)
            ->send($passable)
            ->through([
                CreatePlayerAction::class,
                CreatePeoplePlayerAction::class,
                CreateInscriptionAction::class,
                CreateContractAction::class,
                SendDocumentsAction::class
            ])
            ->thenReturn();
    }
}
