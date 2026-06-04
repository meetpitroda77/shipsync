<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->hasActiveSubscription()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $user ? $user->getCannotCreateShipmentReason() : 'Authentication required',
                    'subscription_status' => $user ? $user->subscription_status : 'inactive',
                    'requires_subscription' => true,
                ], 403);
            }

            return redirect()->route('subscription.plans')
                ->with('error', 'You need an active subscription to access this page.');
        }

        return $next($request);
    }
}
