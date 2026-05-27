<?php

namespace App\Mail;

use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChildPasswordResetLinkMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ParentModel $parent,
        public Student $student,
        public User $user,
        public string $resetUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Password reset for {$this->student->first_name}'s To Quran account",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.child-password-reset-link',
            with: [
                'parent' => $this->parent,
                'student' => $this->student,
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
                'expiry' => config('auth.passwords.users.expire', 60),
            ],
        );
    }
}
