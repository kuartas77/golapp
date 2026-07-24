<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BreadCrumb extends Component
{
    public function __construct(
        public string $title,
        public int $option = 0,
        public int $birthdays = 0
    ) {
    }

    public function render(): View
    {
        return view('components.bread-crumb');
    }
}
