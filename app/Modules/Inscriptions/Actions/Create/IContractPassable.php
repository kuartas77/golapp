<?php

declare(strict_types=1);

namespace App\Modules\Inscriptions\Actions\Create;

interface IContractPassable
{
    public function handle(Passable $passable, \Closure $next);

}
