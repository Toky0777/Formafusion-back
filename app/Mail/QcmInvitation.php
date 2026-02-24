<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class QcmInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;
    public $employee;
    public $qcm;

    /**
     * Create a new message instance.
     *
     * @param QcmInvitation $invitation
     * @param object $employee
     * @param object $qcm
     */
    public function __construct($invitation, $employee, $qcm)
    {
        $this->invitation = $invitation;
        $this->employee = $employee;
        $this->qcm = $qcm;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Qcm Invitation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'TestingCenter.emails.qcm-invitation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the message. (Structure of the mail message)
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
            ->subject('Invitation QCM - ' . $this->qcm->intituleQCM)
            ->to($this->employee->email, $this->employee->firstName . ' ' . $this->employee->name)
            ->view('TestingCenter.emails.qcm-invitation');
    }
}
