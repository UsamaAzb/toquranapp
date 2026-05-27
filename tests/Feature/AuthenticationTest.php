<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('<title>To Quran | Login</title>', false);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_users_can_authenticate_with_username_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'name' => 'SA152',
            'email' => 'SA152@toquran.org',
        ]);

        $response = $this->post('/login', [
            'email' => 'SA152',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_users_can_authenticate_with_child_style_login_email_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'name' => 'SD101',
            'email' => 'SD101@toquran.org',
        ]);

        $response = $this->post('/login', [
            'email' => 'sd101@toquran.org',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_admin_users_are_redirected_to_the_livewire_booking_queue_after_login(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::create(['name' => 'admin']);

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.bookings.livewire', absolute: false));
    }

    public function test_stale_login_post_redirects_authenticated_user_to_current_home(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $role = Role::findOrCreate('customer_support');

        $user = User::factory()->create();
        $user->assignRole($role);

        Route::middleware('web')->post('/__tests/token-mismatch', function (): void {
            throw new TokenMismatchException();
        });

        $response = $this->actingAs($user)->post('/__tests/token-mismatch');

        $response->assertRedirect(route('admin.bookings.transferred'));
        $response->assertSessionHas('message', 'You are already signed in.');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
