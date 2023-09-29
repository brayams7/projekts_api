<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InviteMemberToWorkspace extends Mailable
{
    use Queueable, SerializesModels;
    public string $url;
    public string $nameWorkspace;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $url, string $nameWorkspace)
    {
        $this->url=$url;
        $this->nameWorkspace= $nameWorkspace;
    }

    /**
     * Get the message envelope.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invite Member To Workspace',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.invite-member-to-workspace',

        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}
