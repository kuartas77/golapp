<?php

namespace App\View\Components;

use Illuminate\View\Component;

class RowCard extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public ?string $colOutside = null, public string $colInside, public string $margin = 'm-b-0')
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.row-card');
    }
}
