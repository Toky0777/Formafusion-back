<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CardCollab extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public string $statut = "Nouveau", public string $nom = "Entreprise sans nom", public string $mail = "exemple@gmail.com", public string $adresse = "--", public string $img = "")
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
        return view('components.card-collab');
    }
}
