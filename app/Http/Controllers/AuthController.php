<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();
            
            // Set last activity time
            $request->session()->put('last_activity', time());
            
            // Check if user is HR admin and has a department
            if ($user->role === 'hr-admin' && $user->hrDepartment()) {
                $request->session()->regenerate();
                return redirect()->intended('hr');
            }
            
            if ($user->ip_address && $request->ip() !== $user->ip_address) {
                Auth::logout();
                return back()->withErrors(['ip' => 'Access restricted to registered IP.']);
            }
            
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}