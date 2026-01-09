<?php

namespace App\Http\Controllers\Parent;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * Show parent login form
     */
    public function showLogin()
    {
        return Inertia::render('Parent/Login');
    }

    /**
     * Handle parent login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Check if user is a parent
            if ($user->role !== UserRole::PARENT) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => 'Akun ini bukan akun orang tua.',
                ]);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('parent.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau password salah.',
        ]);
    }

    /**
     * Handle parent logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('parent.login');
    }
}
