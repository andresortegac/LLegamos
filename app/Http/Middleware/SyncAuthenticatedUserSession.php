<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SyncAuthenticatedUserSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $request->session()->put('user', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]);
        }

        return $next($request);
    }
}