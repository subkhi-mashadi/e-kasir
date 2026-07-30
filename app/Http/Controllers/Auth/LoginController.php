<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            Log::warning('Failed login attempt', [
                'email' => $request->email,
                'ip'    => $request->ip(),
                'ua'    => $request->userAgent(),
            ]);
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Akun tidak aktif.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        // Super admin → Filament panel
        if ($user->isSuperAdmin()) {
            return redirect('/admin');
        }

        // Cashier/manager with fixed branch → POS or app
        if ($user->branch_id) {
            session(['branch_id' => $user->branch_id]);
        }

        // Owner without branch → choose branch
        if (! $user->branch_id && $user->hasRole('owner')) {
            return redirect()->route('branch.select');
        }

        return redirect()->intended(route('app.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
