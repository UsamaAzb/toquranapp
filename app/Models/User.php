<?php

namespace App\Models;

use App\Mail\ChildPasswordResetLinkMail;
use App\Mail\ParentPasswordResetLinkMail;
use App\Mail\PasswordResetLinkMail;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'recoverable_password_encrypted',
        'first_name',
        'last_name',
        'phone',
        'country',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'decryp_password',
        'recoverable_password_encrypted',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'recoverable_password_encrypted' => 'encrypted',
        ];
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function parent_user()
    {
        return $this->hasOne(ParentModel::class, 'user_id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->loadMissing(['parent_user', 'student.parent.user']);

        $resetUrl = $this->passwordResetUrl($token);

        if ($this->student?->parent) {
            $parent = $this->student->parent;
            $recipient = $parent->email ?: $parent->user?->email;

            if ($recipient) {
                Mail::to($recipient, $parent->display_name)
                    ->send(new ChildPasswordResetLinkMail($parent, $this->student, $this, $resetUrl));

                return;
            }
        }

        if ($this->parent_user) {
            $parent = $this->parent_user;
            $recipient = $parent->email ?: $this->email;

            Mail::to($recipient, $parent->display_name)
                ->send(new ParentPasswordResetLinkMail($parent, $this, $resetUrl));

            return;
        }

        Mail::to($this->getEmailForPasswordReset(), $this->name)
            ->send(new PasswordResetLinkMail($this, $resetUrl));
    }

    private function passwordResetUrl(string $token): string
    {
        return url(route('password.reset', [
            'token' => $token,
            'email' => $this->getEmailForPasswordReset(),
        ], false));
    }
}
