<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IndividualDevisMail extends Mailable
{
  use Queueable, SerializesModels;

  public $name;
  public $firstname;
  public $situationPro;
  public $email;
  public $phone;
  public $modalite;
  public $financement;
  public $dateDeb;
  public $dateFin;
  public $lieu_formation;
  public $note;

  /**
   * Create a new message instance.
   */
  public function __construct(
    $name,
    $firstname,
    $situationPro,
    $email,
    $phone,
    $modalite,
    $financement,
    $dateDeb,
    $dateFin,
    $lieu_formation,
    $note
  ) {
    $this->name = $name;
    $this->firstname = $firstname;
    $this->situationPro = $situationPro;
    $this->email = $email;
    $this->phone = $phone;
    $this->modalite = $modalite;
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
    return $this->subject('Demande de devis pour un particulier')
      ->view('emails.demande_devis_individual')
      ->with([
        'name' => $this->name,
        'firstname' => $this->firstname,
        'situationPro' => $this->situationPro,
        'email' => $this->email,
        'phone' => $this->phone,
        'modalite' => $this->modalite,
        'financement' => $this->financement,
        'dateDeb' => $this->dateDeb,
        'dateFin' => $this->dateFin,
        'lieu_formation' => $this->lieu_formation,
        'note' => $this->note,
      ]);
  }
}
