<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DevisMail extends Mailable
{
    use Queueable, SerializesModels;

    public $etp_name;
    public $etp_email;
    public $etp_phone;
    public $ref_name;
    public $ref_firstname;
    public $project_type;
    public $modalite;
    public $nb_appr;
    public $financement;
    public $dateDeb;
    public $dateFin;
    public $lieu_formation;
    public $note;

    /**
     * Create a new message instance.
     */
    public function __construct(
        $etp_name,
        $etp_email,
        $etp_phone,
        $ref_name,
        $ref_firstname,
        $project_type,
        $modalite,
        $nb_appr,
        $financement,
        $dateDeb,
        $dateFin,
        $lieu_formation,
        $note
    ) {
        $this->etp_name = $etp_name;
        $this->etp_email = $etp_email;
        $this->etp_phone = $etp_phone;
        $this->ref_name = $ref_name;
        $this->ref_firstname = $ref_firstname;
        $this->project_type = $project_type;
        $this->modalite = $modalite;
        $this->nb_appr = $nb_appr;
        $this->financement = $financement;
        $this->dateDeb = $dateDeb;
        $this->dateFin = $dateFin;
        $this->lieu_formation = $lieu_formation;
        $this->note = $note;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Demande de devis')
            ->view('emails.demande_devis')
            ->with([
                'etp_name' => $this->etp_name,
                'etp_email' => $this->etp_email,
                'etp_phone' => $this->etp_phone,
                'ref_name' => $this->ref_name,
                'ref_firstname' => $this->ref_firstname,
                'project_type' => $this->project_type,
                'modalite' => $this->modalite,
                'nb_appr' => $this->nb_appr,
                'financement' => $this->financement,
                'dateDeb' => $this->dateDeb,
                'dateFin' => $this->dateFin,
                'lieu_formation' => $this->lieu_formation,
                'note' => $this->note,
            ]);
    }
}
