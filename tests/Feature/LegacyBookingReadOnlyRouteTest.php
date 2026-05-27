<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Middleware\RoleMiddleware;
use Tests\TestCase;

class LegacyBookingReadOnlyRouteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['session.driver' => 'array']);

        $this->withoutMiddleware(RoleMiddleware::class);
        $this->actingAs(User::factory()->create());
    }

    public function test_old_singular_booking_page_redirects_to_livewire_booking_admin(): void
    {
        $this->get(route('admin.booking'))
            ->assertRedirect(route('admin.bookings.livewire'));

        $this->get(route('admin.bookings.legacy'))
            ->assertRedirect(route('admin.bookings.livewire'));
    }

    public function test_old_singular_booking_endpoints_are_retired(): void
    {
        $routes = [
            ['getJson', route('admin.booking.data')],
            ['getJson', route('admin.booking.show', ['id' => 123])],
            ['getJson', route('admin.booking.showJson', ['booking' => 123])],
            ['deleteJson', route('admin.booking.destroy', ['booking' => 123])],
            ['putJson', route('admin.booking.update', ['booking' => 123])],
            ['postJson', route('admin.booking.transfer', ['booking' => 123])],
            ['putJson', route('admin.booking.children.update', ['booking' => 123, 'bookingChild' => 456])],
            ['postJson', route('admin.booking.children.transfer', ['booking' => 123, 'bookingChild' => 456])],
        ];

        foreach ($routes as [$method, $url]) {
            $this->{$method}($url)
                ->assertStatus(410)
                ->assertJson([
                    'message' => 'Legacy booking DataTables endpoints have been retired. Use the Livewire booking admin.',
                ]);
        }
    }
}
