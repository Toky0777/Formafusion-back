<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WinOpMail extends Mailable
{
  public $etp_name;
  public $idProjet;
  public $dateDeb;
  public $dateFin;
  public $lieu;

  use Queueable, SerializesModels;

  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct($etp_name, $idProjet, $dateDeb, $dateFin, $lieu)
  {
    $this->etp_name = $etp_name;
    $this->idProjet = $idProjet;
    $this->dateDeb = $dateDeb;
    $this->dateFin = $dateFin;
    $this->lieu = $lieu;
  }

  public function build()
  {
    return $this->from('formation@numerika.top')
      ->subject('Opportunité gagnée')
      ->view('emails.win_opportunite')
      ->with([
        'etp_name' => $this->etp_name,
        'dateDeb' => Carbon::parse($this->dateDeb)->locale('fr')->translatedFormat('j M y'),
        'dateFin' => Carbon::parse($this->dateFin)->locale('fr')->translatedFormat('j M y'),
        'lieu' => $this->lieu,
      ]);
  }
}
