<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!in_array($user->role, $roles)) {
            return redirect($this->redirectToDashboard($user->role));
        }

        return $next($request);
    }

    protected function redirectToDashboard($role)
    {
        return match ($role) {
            'admin' => route('admin'),
            'customer' => route('customer'),
            'staff' => route('staff'),
            'agent' => route('agent'),
            default => route('login'),
        };
    }

}