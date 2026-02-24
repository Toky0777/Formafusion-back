<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ProfileCard extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public string $disponibilite="", public string $nom="", public string $post="", public string $competence="", public string $img="")
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
        return view('components.profile-card');
    }
}
