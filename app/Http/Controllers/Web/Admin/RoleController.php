<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return view('admin.roles.index', [
            'roles' => Role::with('permissions')->withCount('users')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.roles.create', [
            'permissions' => Permission::all()->groupBy(fn($p) => explode('.', $p->name)[0]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $data['name']]);
        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        ActivityLogger::log('Role created', $role, ['name' => $role->name]);

        return redirect()->route('admin.roles.index')->with('success', "Role '{$role->name}' created.");
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::all()->groupBy(fn($p) => explode('.', $p->name)[0]),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('roles')->ignore($role->id)],
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if (in_array($role->name, ['super-admin', 'admin', 'member'])) {
            return back()->with('error', 'System roles cannot be renamed.');
        }

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        ActivityLogger::log('Role updated', $role);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['super-admin', 'admin', 'member', 'front-desk', 'trainer', 'manager'])) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        ActivityLogger::log('Role deleted', $role);
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }
}