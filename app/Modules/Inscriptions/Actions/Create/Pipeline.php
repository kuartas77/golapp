<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Actions\Create;

use Illuminate\Pipeline\Pipeline as BasePipeline;

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
                InviteGuardianAction::class,
                CreateContractAction::class,
                SendDocumentsAction::class,
            ])
            ->thenReturn();
    }
}
