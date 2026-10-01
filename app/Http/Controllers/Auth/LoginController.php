<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

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

        $throttleKey = Str::transliterate(
            Str::lower($credentials['username']) . '|' . $request->ip()
        );

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withErrors([
                    'username' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . ceil($seconds / 60) . ' menit.',
                ])
                ->withInput([
                    'username' => $request->username,
                    'remember' => $request->boolean('remember'),
                ]);
        }

        if (Auth::attempt([
            'name' => $credentials['username'],
            'password' => $credentials['password'],
        ], $remember)) {

            RateLimiter::clear($throttleKey);

            $request->session()->regenerate();

            $user = $request->user();

            if ($user->role === 'production') {
                return redirect()->route('admin.production-reports');
            }

            if ($user->role === 'management') {
                return redirect()->route('admin.transactions');
            }

            if ($user->role === 'super_admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('home');
        }

        RateLimiter::hit($throttleKey);

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