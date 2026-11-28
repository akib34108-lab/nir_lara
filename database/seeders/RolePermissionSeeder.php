<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

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
            'Shareholder',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        // Create Permissions

        $modules = [
            'central' => ['view'],

            'dashboard' => ['view'],

            'users' => ['list', 'create', 'edit', 'delete'],

            'employees' => ['list', 'create', 'view', 'edit', 'delete'],

            'departments' => ['list', 'create', 'edit', 'delete'],

            'designations' => ['list', 'create', 'edit', 'delete'],

            'projects' => ['list', 'create', 'view', 'edit', 'delete'],

            'materials' => ['list', 'create', 'view', 'edit', 'delete'],

            'category' => ['list', 'create', 'edit', 'delete'],

            'supplier' => ['list', 'create', 'edit', 'delete'],

            'purchase-request' => ['list', 'create', 'edit', 'delete', 'approve', 'reject'],

            'purchase-order' => ['list', 'create', 'edit', 'delete', 'approve'],

            'grn' => ['list', 'create', 'edit', 'delete', 'confirm'],

            'inspection' => ['list', 'create', 'edit', 'approve'],

            'inventory' => ['list', 'view'],

            'material-issue' => ['list', 'create', 'approve'],

            'material-return' => ['list', 'create', 'approve'],

            'stock-transfer' => ['list', 'create', 'approve', 'receive'],

            'consumption' => ['list', 'create', 'edit'],

            'stock-adjustment' => ['list', 'create', 'approve'],

            'material-report' => ['view', 'export'],

            'clients' => ['list', 'create', 'view', 'edit', 'delete'],

            'sites' => ['list', 'create', 'view', 'edit', 'delete'],

            'buildings' => ['list', 'create', 'view', 'edit', 'delete'],

            'floors' => ['list', 'create', 'view', 'edit', 'delete'],

            'amenities' => ['list', 'create', 'view', 'edit', 'delete'],

            'floor-plans' => ['list', 'create', 'view', 'edit', 'delete'],

            'roles' => ['list', 'create', 'edit', 'delete'],

            'permissions' => ['list', 'create', 'edit', 'delete'],

            'activity-log' => ['list'],

            'milestones' => ['create', 'edit', 'delete'],
        ];

        foreach ($modules as $module => $actions) {

            foreach ($actions as $action) {

                Permission::firstOrCreate([
                    'name' => $module.'-'.$action,
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
                'category-list',
                'supplier-list',
                'purchase-request-list',
                'purchase-request-create',
                'purchase-request-edit',
                'purchase-request-approve',
                'purchase-request-reject',
                'purchase-order-list',
                'purchase-order-create',
                'purchase-order-edit',
                'purchase-order-approve',
                'grn-list',
                'grn-create',
                'grn-confirm',
                'inspection-list',
                'inspection-create',
                'inventory-list',
                'material-issue-list',
                'material-issue-create',
                'material-issue-approve',
                'material-return-list',
                'material-return-create',
                'material-return-approve',
                'stock-transfer-list',
                'stock-transfer-create',
                'stock-transfer-approve',
                'stock-transfer-receive',
                'consumption-list',
                'consumption-create',
                'stock-adjustment-list',
                'stock-adjustment-create',
                'stock-adjustment-approve',
                'material-report-view',
                'clients-list',
                'clients-view',
                'milestones-create',
                'milestones-edit',
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

            'Shareholder' => [
                'dashboard-view',
                'sites-list',
                'sites-view',
                'buildings-list',
                'buildings-view',
                'floors-list',
                'floors-view',
                'amenities-list',
                'amenities-view',
                'floor-plans-list',
                'floor-plans-view',
            ],

        ];

        foreach ($rolePermissions as $roleName => $permissions) {

            $role = Role::findByName($roleName, 'web');

            $role->syncPermissions($permissions);

        }
        $user = User::first();

        if ($user && ! $user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
        }

    }
}
