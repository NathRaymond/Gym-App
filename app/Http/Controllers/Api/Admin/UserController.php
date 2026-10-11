<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::with('roles');
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(fn($x) => $x->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")
                ->orWhere('membership_number', 'like', "%{$s}%"));
        }
        if ($request->filled('role')) $q->role($request->role);
        if ($request->filled('status')) $q->where('status', $request->status);
        return response()->json($q->latest()->paginate($request->get('per_page', 15)));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'password' => 'required|min:8',
            'role' => 'required|exists:roles,name',
            'status' => 'nullable|in:active,inactive,suspended',
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => $data['status'] ?? 'active',
            'email_verified_at' => now(),
            'membership_number' => 'GYM-' . strtoupper(\Str::random(6)),
        ]);
        $user->assignRole($data['role']);
        $user->getOrCreateWallet();

        ActivityLogger::log('User created', $user, ['role' => $data['role']]);

        return response()->json(['message' => 'User created', 'user' => $user->load('roles')], 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|unique:users,phone,' . $user->id,
            'status' => 'sometimes|in:active,inactive,suspended',
            'password' => 'nullable|min:8',
            'role' => 'nullable|exists:roles,name',
        ]);

        if (!empty($data['password'])) $data['password'] = Hash::make($data['password']);
        else unset($data['password']);

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
            unset($data['role']);
        }

        $user->update($data);
        ActivityLogger::log('User updated', $user);

        return response()->json(['message' => 'User updated', 'user' => $user->fresh()->load('roles')]);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) return response()->json(['message' => 'Cannot delete yourself'], 403);
        ActivityLogger::log('User deleted', $user);
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}