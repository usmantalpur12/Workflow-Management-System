<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictIP
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        if ($user->ip_address && $request->ip() !== $user->ip_address) {
            abort(403, 'Unauthorized IP address');
        }
        return $next($request);
    }
}