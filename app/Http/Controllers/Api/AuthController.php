<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone|max:20',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['membership_number'] = 'GYM-' . strtoupper(\Str::random(6));
        $user = User::create($data);
        $user->assignRole('member');
        $user->getOrCreateWallet();

        ActivityLogger::log('New user registered', $user, ['email' => $user->email]);

        $token = $user->createToken('auth')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        $user = Auth::user();
        if ($user->status !== 'active') {
            Auth::logout();
            return response()->json(['message' => 'Account is not active.'], 403);
        }

        $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);
        $token = $user->createToken('auth')->plainTextToken;

        ActivityLogger::log('User logged in', $user);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load('roles'),
            'token' => $token,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load(['roles', 'wallet']),
            'permissions' => $request->user()->getAllPermissions()->pluck('name'),
        ]);
    }

    public function logout(Request $request)
    {
        ActivityLogger::log('User logged out', $request->user());
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}