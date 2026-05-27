<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\Exception\ExceptionInterface as MailerException;

$authenticatedHome = static function (Request $request): string {
    $user = $request->user();

    if (! $user) {
        return route('login');
    }

    if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
        return route('admin.bookings.livewire');
    }

    if ($user->hasRole('customer_support')) {
        return route('admin.bookings.transferred');
    }

    if ($user->hasRole('teacher')) {
        return route('teacher.classes');
    }

    if ($user->hasRole('student')) {
        return route('student.workplace');
    }

    if ($user->hasRole('parent')) {
        return route('parent.students');
    }

    return url('/');
};

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware) use ($authenticatedHome): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'legacy_library_access' => \App\Http\Middleware\EnsureLegacyLibraryAccess::class,
        ]);

        $middleware->redirectGuestsTo(
            fn (Request $request) => route('login')
        );

        $middleware->redirectUsersTo($authenticatedHome);
    })
    ->withExceptions(function (Exceptions $exceptions) use ($authenticatedHome): void {
        $exceptions->render(function (MailerException $e, Request $request) {
            if (! $request->routeIs('password.email')) {
                return null;
            }

            $message = 'Unable to send the reset link to this email address. Please check the address or contact support.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $message]);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'))
                ->with('message', 'Your session has expired, please login again.');
        });

        $exceptions->render(function (HttpException $e, Request $request) use ($authenticatedHome) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'CSRF token mismatch.'], 419);
            }

            if ($request->user()) {
                return redirect($authenticatedHome($request))
                    ->with('message', 'You are already signed in.');
            }

            return redirect()->guest(route('login'))
                ->with('message', 'Your session has expired, please login again.');
        });
    })->create();
