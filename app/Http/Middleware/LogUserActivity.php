<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {

        if (Auth::check()) {

            /** @var \App\Models\User $user */
            $user = Auth::user();

            $user->last_activity_at = now();
            $user->save();
        }

        return $next($request);
    }
}
