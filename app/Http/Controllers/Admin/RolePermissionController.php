<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSchoolRole;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => Str::before($permission->name, '.') ?: 'general');

        $schoolRoleSummary = Schema::hasTable('user_school_roles')
            ? UserSchoolRole::query()
                ->selectRaw('role_name, status, count(*) as aggregate')
                ->groupBy('role_name', 'status')
                ->orderBy('role_name')
                ->get()
            : collect();

        return view('admin.roles-permissions.index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'schoolRoleSummary' => $schoolRoleSummary,
        ]);
    }
}
