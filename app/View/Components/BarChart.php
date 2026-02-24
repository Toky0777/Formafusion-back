<?php

namespace App\View\Components;

use Illuminate\View\Component;

class BarChart extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public string $nom="Excel", public string $percent="0", public string $score="0/10", public string $couleur="gray")
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
        return view('components.bar-chart');
    }
}
