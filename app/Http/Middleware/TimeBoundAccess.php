<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TimeBoundAccess
{
    public function handle(Request $request, Closure $next)
    {
        $hour = Carbon::now()->hour;
        if ($hour < 9 || $hour > 17) {
            abort(403, 'Access restricted to office hours (9 AM - 5 PM)');
        }
        return $next($request);
    }
}