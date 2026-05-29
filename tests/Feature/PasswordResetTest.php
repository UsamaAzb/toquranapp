<?php

namespace Tests\Feature;

use App\Mail\ChildPasswordResetLinkMail;
use App\Mail\ParentPasswordResetLinkMail;
use App\Mail\PasswordResetLinkMail;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Features;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\Support\InteractsWithFamilyLifecycleTables;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use InteractsWithFamilyLifecycleTables;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createFamilyLifecycleTables();
    }

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        Mail::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', [
            'email' => $user->email,
        ])->assertSessionHasNoErrors();

        Mail::assertSent(PasswordResetLinkMail::class, function (PasswordResetLinkMail $mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->user->is($user)
                && str_contains($mail->resetUrl, '/reset-password/')
                && str_contains($mail->resetUrl, 'email='.rawurlencode($user->email));
        });
    }

    public function test_parent_reset_password_link_uses_week14_parent_email(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        Mail::fake();

        $parentUser = User::factory()->create([
            'email' => 'parent@example.test',
            'name' => 'Salem Parent',
        ]);
        $parent = ParentModel::create([
            'first_name' => 'Salem',
            'last_name' => 'Parent',
            'email' => 'parent@example.test',
            'phone' => '+201000000000',
            'user_id' => $parentUser->id,
            'active' => true,
        ]);

        $this->post('/forgot-password', [
            'email' => $parentUser->email,
        ])->assertSessionHasNoErrors();

        Mail::assertSent(ParentPasswordResetLinkMail::class, function (ParentPasswordResetLinkMail $mail) use ($parent, $parentUser) {
            return $mail->hasTo($parent->email)
                && $mail->parent->is($parent)
                && $mail->user->is($parentUser)
                && str_contains($mail->resetUrl, 'email='.rawurlencode($parentUser->email));
        });
    }

    public function test_child_reset_password_link_is_sent_to_parent_email(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        Mail::fake();

        $parentUser = User::factory()->create([
            'email' => 'salem.parent@example.test',
            'name' => 'Salem Parent',
        ]);
        $parent = ParentModel::create([
            'first_name' => 'Salem',
            'last_name' => 'Parent',
            'email' => 'salem.parent@example.test',
            'phone' => '+201000000000',
            'user_id' => $parentUser->id,
            'active' => true,
        ]);
        $childUser = User::factory()->create([
            'email' => 'sa152@toquran.org',
            'name' => 'Safeya',
        ]);
        $student = Student::create([
            'first_name' => 'Safeya',
            'last_name' => 'Student',
            'parent_id' => $parent->id,
            'student_email' => $childUser->email,
            'user_id' => $childUser->id,
            'status' => 'active',
            'account_status' => 'active',
        ]);

        $this->post('/forgot-password', [
            'email' => $childUser->email,
        ])->assertSessionHasNoErrors();

        Mail::assertSent(ChildPasswordResetLinkMail::class, function (ChildPasswordResetLinkMail $mail) use ($parent, $student, $childUser) {
            return $mail->hasTo($parent->email)
                && $mail->parent->is($parent)
                && $mail->student->is($student)
                && $mail->user->is($childUser)
                && str_contains($mail->resetUrl, 'email='.rawurlencode($childUser->email));
        });
    }

    public function test_successful_forgot_password_request_shows_confirmation_instead_of_live_form(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        Mail::fake();

        $user = User::factory()->create();

        $this->followingRedirects()
            ->from('/forgot-password')
            ->post('/forgot-password', [
                'email' => $user->email,
            ])
            ->assertStatus(200)
            ->assertSee('Reset link sent')
            ->assertDontSee('id="formAuthentication"', false)
            ->assertDontSee('Send Reset Link');
    }

    public function test_mailer_failure_returns_field_error_instead_of_server_error(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new TransportException('Mailbox does not exist.'));

        $user = User::factory()->create();

        $this->from('/forgot-password')
            ->post('/forgot-password', [
                'email' => $user->email,
            ])
            ->assertRedirect('/forgot-password')
            ->assertSessionHasErrors(['email']);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->get('/reset-password/'.$token.'?email='.rawurlencode($user->email));

        $response->assertStatus(200);
        $response->assertSee('assets/img/logo/logo.png', false);
        $response->assertDontSee('app-brand-text demo', false);
        $response->assertSee('Choose a password you can remember, then confirm it below.');
        $response->assertDontSee('previously used passwords');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_password_reset_link_can_be_used_from_logged_in_browser(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        $loggedInUser = User::factory()->create();
        $resetTarget = User::factory()->create();
        $token = Password::broker()->createToken($resetTarget);

        $this->actingAs($loggedInUser);

        $this->get('/reset-password/'.$token.'?email='.rawurlencode($resetTarget->email))
            ->assertStatus(200)
            ->assertSee('Reset Password');

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $resetTarget->email,
            'password' => 'UpdatedPass123',
            'password_confirmation' => 'UpdatedPass123',
        ])->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($loggedInUser);
        $this->assertTrue(Hash::check('UpdatedPass123', $resetTarget->fresh()->password));
    }

    public function test_forgot_password_request_can_be_used_from_logged_in_browser(): void
    {
        if (! Features::enabled(Features::resetPasswords())) {
            $this->markTestSkipped('Password updates are not enabled.');
        }

        Mail::fake();

        $loggedInUser = User::factory()->create();
        $resetTarget = User::factory()->create();

        $this->actingAs($loggedInUser)
            ->get('/forgot-password?email='.$resetTarget->email)
            ->assertStatus(200)
            ->assertSee('Reset Your Password')
            ->assertSee('value="'.$resetTarget->email.'"', false);

        $this->post('/forgot-password', [
            'email' => $resetTarget->email,
        ])->assertSessionHasNoErrors();

        Mail::assertSent(PasswordResetLinkMail::class, function (PasswordResetLinkMail $mail) use ($resetTarget) {
            return $mail->hasTo($resetTarget->email)
                && $mail->user->is($resetTarget);
        });
        $this->assertAuthenticatedAs($loggedInUser);
    }
}
