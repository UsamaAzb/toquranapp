<?php

namespace App\Mail;

use App\Models\ParentModel;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ParentPasswordResetLinkMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ParentModel $parent,
        public User $user,
        public string $resetUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your To Quran parent password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.parent-password-reset-link',
            with: [
                'parent' => $this->parent,
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
                'expiry' => config('auth.passwords.users.expire', 60),
            ],
        );
    }
}
