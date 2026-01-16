<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckSessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $lastActivity = Session::get('last_activity');
            $timeout = config('session.lifetime') * 60; // Convert to seconds
            
            // If last activity is set and session has expired
            if ($lastActivity && (time() - $lastActivity) > $timeout) {
                Auth::logout();
                Session::flush();
                return redirect('/login')->with('error', 'Your session has expired. Please login again.');
            }
            
            // Update last activity time
            Session::put('last_activity', time());
        }
        
        return $next($request);
    }
}
