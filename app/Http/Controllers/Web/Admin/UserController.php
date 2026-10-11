<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::with('roles');

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($x) use ($s) {
                $x->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('membership_number', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            $q->role($request->role);
        }

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        return view('admin.users.index', [
            'users' => $q->latest()->paginate(20),
            'roles' => Role::pluck('name'),
        ]);
    }

    public function create()
    {
        return view('admin.users.create', [
            'roles' => Role::pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:20|unique:users,phone',
            'password'   => 'required|string|min:8|confirmed',
            'role'       => 'required|exists:roles,name',
            'status'     => 'required|in:active,inactive,suspended',
            'gender'     => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'address'    => 'nullable|string',
            'city'       => 'nullable|string',
            'state'      => 'nullable|string',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['membership_number'] = 'GYM-' . strtoupper(\Str::random(6));
        $data['email_verified_at'] = now();
        $data['is_verified'] = true;

        $user = User::create($data);
        $user->assignRole($data['role']);
        $user->getOrCreateWallet();

        ActivityLogger::log('User created', $user, ['role' => $data['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->full_name} created successfully.");
    }

    public function show(User $user)
    {
        $user->load([
            'roles', 'wallet', 'memberships.plan', 'bookings',
            'payments', 'tickets', 'feedbacks',
        ]);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::pluck('name'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone'      => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'password'   => 'nullable|string|min:8|confirmed',
            'role'       => 'required|exists:roles,name',
            'status'     => 'required|in:active,inactive,suspended',
            'gender'     => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'address'    => 'nullable|string',
            'city'       => 'nullable|string',
            'state'      => 'nullable|string',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $role = $data['role'];
        unset($data['role']);

        $user->update($data);
        $user->syncRoles([$role]);

        ActivityLogger::log('User updated', $user);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->full_name;
        ActivityLogger::log('User deleted', $user);
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$name} deleted.");
    }
}