<?php

// CodeRabbit module review: Core (Module 1)

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            return redirect()->route('admin.bookings.livewire');
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.bookings.livewire');
        }

        if ($user->hasRole('customer_support')) {
            return redirect()->route('admin.bookings.transferred');
        }

        if ($user->hasRole('teacher')) {
            return redirect()->route('teacher.classes');
        }
        if ($user->hasRole('student')) {
            return redirect()->route('student.workplace');
        }
        if ($user->hasRole('parent')) {
            return redirect()->route('parent.students');
        }

        return redirect('/');
    }
}
