<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Column extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public string $couleur="#69BFF1", public string $titre="Brouillon", public string $nombre="0")
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
        return view('components.column');
    }
}
