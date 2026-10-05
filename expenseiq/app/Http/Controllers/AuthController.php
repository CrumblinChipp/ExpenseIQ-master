<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show Login Page
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Login User
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {

            return back()
                ->withErrors([
                    'email' => 'Invalid email or password.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        if (!Auth::user()->is_onboarded) {
            return redirect()->route('welcome.step1');
        }

return redirect()->route('dashboard');
    }

    /**
     * Show Register Page
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Register User
     */
    public function register(Request $request)
    {
        $validated = $request->validate([

            'first_name' => 'required|string|max:255',

            'last_name' => 'required|string|max:255',

            'email' => 'required|email|unique:users,email',

            'password' => 'required|confirmed|min:8',
// 

        ]);

        $user = User::create([

            'first_name' => $validated['first_name'],

            'last_name' => $validated['last_name'],

            'nickname' => null,

            'email' => $validated['email'],

            // Auto-hashed dahil sa User model casts
            'password' => $validated['password'],

        ]);

       Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('welcome.step1');
    }

    /**
     * Logout User
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}