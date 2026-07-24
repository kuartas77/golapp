<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RowCard extends Component
{
    public function __construct(
        public string $colInside = '12',
        public ?string $colOutside = null,
        public string $margin = ''
    ) {
    }

    public function render(): View
    {
        return view('components.row-card');
    }
}
