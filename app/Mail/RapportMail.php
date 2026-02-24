<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RapportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $referent;
    public $rapport;
    public $localFilePath;

    public function __construct($referent, $rapport, $localFilePath)
    {
        $this->referent = $referent;
        $this->rapport = $rapport;
        $this->localFilePath = $localFilePath;
    }

    public function build()
    {
        $mail = $this->view('emails.send_rapports')
            ->subject('Votre rapport est disponible')
            ->with([
                'nom' => $this->referent->ref_name,
                'prenom' => $this->referent->ref_firstname,
                'nom_rapport' => $this->rapport->filename,
            ]);

        if (file_exists($this->localFilePath)) {
            $mail->attach($this->localFilePath, [
                'as' => basename($this->localFilePath), // Nom du fichier
                'mime' => 'application/octet-stream',
            ]);
        } else {
            dd("Fichier introuvable : ", $this->localFilePath);
        }

        return $mail;
    }
}
