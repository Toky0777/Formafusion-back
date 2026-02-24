<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OpportuniteMail extends Mailable
{
  use Queueable, SerializesModels;

  public $etp_name;
  public $ville_op;
  public $etp_email;
  public $etp_phone;
  public $dateDeb;
  public $dateFin;
  public $nb_appr;
  public $ref_name;
  public $ref_firstname;
  public $note;
  public $prix;
  public $source;
  public $statut;
  public $cours_name;

  /**
   * Create a new message instance.
   */
  public function __construct(
    $etp_name,
    $ville_op,
    $etp_email,
    $etp_phone,
    $dateDeb,
    $dateFin,
    $nb_appr,
    $ref_name,
    $ref_firstname,
    $note,
    $prix,
    $source,
    $statut,
    $cours_name,
  ) {
    $this->etp_name = $etp_name;
    $this->ville_op = $ville_op;
    $this->etp_email = $etp_email;
    $this->etp_phone = $etp_phone;
    $this->dateDeb = $dateDeb;
    $this->dateFin = $dateFin;
    $this->nb_appr = $nb_appr;
    $this->ref_name = $ref_name;
    $this->ref_firstname = $ref_firstname;
    $this->note = $note;
    $this->prix = $prix;
    $this->source = $source;
    $this->statut = $statut;
    $this->cours_name = $cours_name;
  }

  /**
   * Build the message.
   */
  public function build()
  {
    return $this->subject('Nouvelle opportunité')
      ->view('emails.opportunite_email')
      ->with([
        'etp_name' => $this->etp_name,
        'ville_op' => $this->ville_op,
        'etp_email' => $this->etp_email,
        'etp_phone' => $this->etp_phone,
        'dateDeb' => $this->dateDeb,
        'dateFin' => $this->dateFin,
        'nb_appr' => $this->nb_appr,
        'ref_name' => $this->ref_name,
        'ref_firstname' => $this->ref_firstname,
        'note' => $this->note,
        'prix' => $this->prix,
        'source' => $this->source,
        'statut' => $this->statut,
        'cours_name' => $this->cours_name,
      ]);
  }
}
