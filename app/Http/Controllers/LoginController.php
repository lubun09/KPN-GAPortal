<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Proses login manual (NON SSO)
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $input = $request->username;

        // Cari user berdasarkan username atau email
        $user = User::where('username', $input)
                    ->orWhere('email', $input)
                    ->first();

        if (!$user) {
            return back()->withErrors([
                'username' => 'Username atau password salah (login melalui Apps Darwinbox).'
            ]);
        }

        // 🔐 BLOK LOGIN MANUAL UNTUK AKUN SSO
        if ($user->login_type === 'sso') {
            return back()->withErrors([
                'username' => 'Akun ini hanya bisa login melalui Darwinbox.'
            ]);
        }

        // Cek password hash
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'username' => 'Username atau password salah (login melalui Apps Darwinbox)'
            ]);
        }

        // Login sukses
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
