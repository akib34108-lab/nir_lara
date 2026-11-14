<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $roles = [
            'Super Admin',
            'Company Admin',
            'Project Manager',
            'HR Manager',
            'Client',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        // Create Permissions

        $modules = [
            'dashboard' => ['view'],

            'users' => ['list', 'create', 'edit', 'delete'],

            'employees' => ['list', 'create', 'view', 'edit', 'delete'],

            'departments' => ['list', 'create', 'edit', 'delete'],

            'designations' => ['list', 'create', 'edit', 'delete'],

            'projects' => ['list', 'create', 'view', 'edit', 'delete'],

            'materials' => ['list', 'create', 'view', 'edit', 'delete'],

            'clients' => ['list', 'create', 'view', 'edit', 'delete'],
        ];

        foreach ($modules as $module => $actions) {

            foreach ($actions as $action) {

                Permission::firstOrCreate([
                    'name' => $module . '-' . $action,
                    'guard_name' => 'web',
                ]);

            }
        }

        // Assign Permissions to Roles

        $allPermissions = Permission::pluck('name')->toArray();

        $rolePermissions = [

            'Super Admin' => $allPermissions,

            'Company Admin' => $allPermissions,

            'Project Manager' => [
                'dashboard-view',
                'projects-list',
                'projects-view',
                'projects-edit',
                'materials-list',
                'materials-view',
                'clients-list',
                'clients-view',
            ],

            'HR Manager' => [
                'dashboard-view',
                'employees-list',
                'employees-create',
                'employees-view',
                'employees-edit',
                'employees-delete',
                'departments-list',
                'departments-create',
                'departments-edit',
                'departments-delete',
                'designations-list',
                'designations-create',
                'designations-edit',
                'designations-delete',
            ],

            'Client' => [
                'dashboard-view',
                'projects-list',
                'projects-view',
            ],

        ];

        foreach ($rolePermissions as $roleName => $permissions) {

            $role = Role::findByName($roleName, 'web');

            $role->syncPermissions($permissions);

        }
        $user = User::first();

        if ($user && !$user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
        }

    }
}
