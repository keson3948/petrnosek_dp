<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;
use Illuminate\Queue\SerializesModels;

class RecordsAutoClosedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Collection $closedRecords,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Automaticky ukončené výrobní operace',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.records-auto-closed',
        );
    }
}
