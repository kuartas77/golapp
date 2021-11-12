<?php

namespace App\Events;

use App\Models\Inscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InscriptionAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Inscription
     */
    public $inscription;

    /**
     * Create a new event instance.
     *
     * @param Inscription $inscription
     */
    public function __construct(Inscription $inscription)
    {
        $this->inscription = $inscription;
    }

}
