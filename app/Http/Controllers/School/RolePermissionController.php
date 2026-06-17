<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Services\RolePermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    public function __construct(
        private RolePermissionService $permissions
    ) {}

    public function index(): View
    {
        return view('school.role-permissions.index', [
            'roleNames' => $this->permissions->roleNames(),
            'permissionCatalog' => $this->permissions->groupedPermissionCatalog(),
            'matrix' => $this->permissions->roleMatrix(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role_name' => ['required', 'string', Rule::in($this->permissions->roleNames())],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $this->permissions->syncRolePermissions(
            $data['role_name'],
            $data['permissions'] ?? []
        );

        return back()->with('success', __('ui.role_permissions_updated'));
    }
}
