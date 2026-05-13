<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KasirAuthController extends Controller
{
    public function showLogin()
    {
        if (auth()->check() && in_array(auth()->user()->user_group, ['Kasir', 'Admin'])) {
            return redirect()->route('kasir.index');
        }
        return view('kasir.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (auth()->attempt($request->only('email', 'password'))) {
            $user = auth()->user();

            if (!in_array($user->user_group, ['Kasir', 'Admin'])) {
                auth()->logout();
                return back()->withErrors(['email' => 'Akun ini tidak memiliki akses kasir.'])->withInput();
            }

            $request->session()->regenerate();
            return redirect()->intended(route('kasir.index'));
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('kasir.login');
    }
}
