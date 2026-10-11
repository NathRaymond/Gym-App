<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthWebController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            activity()->causedBy($user)->log('Web login');

            $redirect = $this->redirectPathFor($user);

            // ✅ AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => true,
                    'message'  => 'Login successful',
                    'redirect' => $redirect,
                ]);
            }

            // Regular form
            return redirect()->intended($redirect);
        }

        // ❌ Wrong credentials
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Email or password is not correct.',
                'errors'  => [
                    'email' => ['Email or password is not correct.'],
                ],
            ], 422);
        }

        return back()
            ->withErrors(['email' => 'Email or password is not correct.'])
            ->withInput($request->only('email'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|unique:users,phone',
            'password'   => 'required|min:8|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['membership_number'] = 'GYM-' . strtoupper(\Str::random(6));

        $user = User::create($data);
        $user->assignRole('member');
        $user->getOrCreateWallet();

        Auth::login($user);

        return redirect()->route('member.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function redirectPathFor($user): string
    {
        return match (true) {
            $user->hasAnyRole(['super-admin', 'admin', 'manager']) => route('admin.dashboard'),
            $user->hasRole('front-desk') => route('frontdesk.dashboard'),
            $user->hasRole('trainer')    => route('trainer.dashboard'),
            default                       => route('member.dashboard'),
        };
    }
}