<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Enums\EventStatus;

class SendInvitationCalendar extends Mailable
{
    use Queueable, SerializesModels;
    public $inviterName;
    public $eventDateTime;
    public $eventTitle;
    public $eventDescription;
    public $eventLocation;
    /**
     * Create a new message instance.
     */
    public $customer_name;

    public function __construct(
        $inviterName,
        Carbon $eventDateTime = null,
        string $eventTitle = null,
        string $eventDescription = null,
        string $eventLocation = null
    ) {
        $this->inviterName = $inviterName;
        $this->eventDateTime = $eventDateTime ?? Carbon::now()->addDay();
        $this->eventTitle = $eventTitle ?? 'Invitation à rejoindre la plateforme';
        $this->eventDescription = $eventDescription ?? 'Vous avez été invité(e) à rejoindre notre plateforme.';
        $this->eventLocation = $eventLocation;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'INVITATION GOOGLE CALENDAR',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.calendar',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $calendar = Calendar::create()
            ->event(
                Event::create()
                    ->name($this->eventTitle)
                    ->description($this->eventDescription)
                    ->startsAt($this->eventDateTime)
                    ->endsAt($this->eventDateTime->copy()->addHour())
                    //->organizer($this->inviterName, config('mail.from.address'))
                    ->organizer('contact@forma-fusion.com', 'Formafusion')
                    ->alertMinutesBefore(60, "Rappel: {$this->eventTitle}")
                    ->status(EventStatus::confirmed())
                //->when($this->eventLocation, function ($event) {
                //     $event->address($this->eventLocation);
                // }
                // )
            );

        return [
            Attachment::fromData(
                fn() => $calendar->get(),
                'invitation.ics'
            )
                ->withMime('text/calendar; charset=UTF-8; method=REQUEST')
        ];
    }
}
