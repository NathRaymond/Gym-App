<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json([
            'roles' => Role::with('permissions')->get(),
            'permissions' => Permission::all()->groupBy(fn($p) => explode('.', $p->name)[0]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $data['name']]);
        if (!empty($data['permissions'])) $role->syncPermissions($data['permissions']);

        ActivityLogger::log('Role created', $role, ['name' => $role->name]);

        return response()->json(['message' => 'Role created', 'role' => $role->load('permissions')], 201);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        if (isset($data['name'])) $role->update(['name' => $data['name']]);
        if (isset($data['permissions'])) $role->syncPermissions($data['permissions']);

        ActivityLogger::log('Role updated', $role);

        return response()->json(['message' => 'Role updated', 'role' => $role->fresh()->load('permissions')]);
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['super-admin', 'admin', 'member'])) {
            return response()->json(['message' => 'Cannot delete system role'], 403);
        }
        ActivityLogger::log('Role deleted', $role);
        $role->delete();
        return response()->json(['message' => 'Role deleted']);
    }
}