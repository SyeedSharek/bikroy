<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;
    public $newsletter;
    public $subscriber;
    public $socialMedia;
    /**
     * Create a new message instance.
     */
    public function __construct($newsletter, $subscriber, $socialMedia)
    {
        $this->newsletter = $newsletter;
        $this->subscriber = $subscriber;
        $this->socialMedia = $socialMedia;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Newsletter Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletterMail',
            with: [
                'newsletter' => $this->newsletter,
                'subscriber' => $this->subscriber,
                'media' => $this->socialMedia,
            ],
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
}
