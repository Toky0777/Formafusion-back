<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CoursMail extends Mailable
{
    use Queueable, SerializesModels;

    public $apprenant;
    public $ressource;
    public $localFilePath;

    public function __construct($apprenant, $ressource, $localFilePath)
    {
        $this->apprenant = $apprenant;
        $this->ressource = $ressource;
        $this->localFilePath = $localFilePath;
    }

    public function build()
    {
        $mail = $this->view('emails.send_cours')
            ->subject('Votre cours est disponible 📚')
            ->with([
                'nom' => $this->apprenant->emp_name,
                'prenom' => $this->apprenant->emp_firstname,
                'nom_cours' => $this->ressource->module_ressource_name,
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
