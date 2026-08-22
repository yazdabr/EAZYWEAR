<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt([
            'name' => $credentials['username'],
            'password' => $credentials['password'],
        ], $remember)) {
            $request->session()->regenerate();

            if ($request->user()->role === 'management') {
                return redirect()->route('admin.transactions');
            }

            return redirect()->route('admin.dashboard');
        }

        return back()
            ->withErrors([
                'username' => 'Username atau password yang Anda masukkan salah.',
            ])
            ->withInput([
                'username' => $request->username,
                'remember' => $request->boolean('remember'),
            ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda berhasil keluar dari sistem.');
    }
}