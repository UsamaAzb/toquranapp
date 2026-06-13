<?php

namespace App\Http\Middleware;

use App\Services\Library\LegacyLibraryAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLegacyLibraryAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user, 403);

        if ($user->hasAnyRole(['teacher', 'admin', 'super_admin'])) {
            abort_unless(app(LegacyLibraryAccessService::class)->canAccessLegacyLibrary($user), 403);

            return $next($request);
        }

        abort(403);
    }
}
