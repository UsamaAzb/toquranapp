<?php

namespace Tests\Feature\CoreLms;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LibraryCompatibilityTest extends TestCase
{
    public function test_representative_legacy_library_routes_keep_auth_and_owner_access_middleware(): void
    {
        foreach ([
            'course/sat',
            'course/grammar',
            'course/background',
            'reading/listen-read',
            'videos/ted',
            'tv_series/friends',
        ] as $uri) {
            $this->assertLegacyLibraryRouteProtected($uri);
        }
    }

    public function test_legacy_tutriols_path_spelling_remains_registered_and_protected(): void
    {
        $this->assertLegacyLibraryRouteProtected('tutriols/level-up');
        $this->assertLegacyLibraryRouteProtected('tutriols/level-up/{slug}');
    }

    private function assertLegacyLibraryRouteProtected(string $uri): void
    {
        $route = collect(Route::getRoutes()->getRoutes())
            ->first(fn ($route): bool => $route->uri() === $uri);

        $this->assertNotNull($route, "Expected legacy Library route [{$uri}] to remain registered.");

        $middleware = $route->gatherMiddleware();

        $this->assertContains('auth', $middleware, "Expected [{$uri}] to require auth.");
        $this->assertContains('role:teacher|admin|super_admin', $middleware, "Expected [{$uri}] to keep the To Quran staff-only legacy Library gate.");
        $this->assertContains('legacy_library_access', $middleware, "Expected [{$uri}] to keep legacy Library access middleware.");
    }
}
