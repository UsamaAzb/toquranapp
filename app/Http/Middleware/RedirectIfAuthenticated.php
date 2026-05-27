<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated as BaseRedirectIfAuthenticated;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated extends BaseRedirectIfAuthenticated
{
    /**
     * Password reset flows are often opened from an already signed-in browser.
     * Let the reset flow continue instead of redirecting to that active account.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if ($request->routeIs('password.request', 'password.email', 'password.reset', 'password.update')) {
            return $next($request);
        }

        return parent::handle($request, $next, ...$guards);
    }
}
