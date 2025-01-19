<?php

namespace App\Modules\Inscriptions\Actions\Create;

interface IContractPassable
{
    public function handle(Passable $passable, \Closure $next);

}
