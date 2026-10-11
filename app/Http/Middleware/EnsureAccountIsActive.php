<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->status === 'active') {
            return $next($request);
        }

        if ($request->expectsJson()) {
            $request->user()?->currentAccessToken()?->delete();
            return response()->json(['message' => 'La cuenta no está activa.'], 403);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'Tu cuenta se encuentra suspendida. Contacta al soporte.');
    }
}