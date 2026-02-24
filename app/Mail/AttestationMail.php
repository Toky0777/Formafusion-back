<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AttestationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $att;
    public $localFilePath;

    public function __construct($att, $localFilePath)
    {
        $this->att = $att;
        $this->localFilePath = $localFilePath;
    }

    public function build()
    {
        $mail = $this->view('emails.send_rapports')
            ->subject('Votre rapport est disponible')
            ->with([
                'nom' => $this->att->emp_name,
                'prenom' => $this->att->emp_firstname,
                'nom_attestation' => $this->att->file_name,
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
