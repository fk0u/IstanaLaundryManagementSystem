<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions grouped by category
        $permissions = [
            // POS & Orders
            'orders.view', 'orders.create', 'orders.update', 'orders.delete', 'orders.refund',
            // Production
            'production.view', 'production.update', 'production.bulk_update',
            // CRM
            'customers.view', 'customers.create', 'customers.update', 'customers.delete', 'loyalty.manage',
            // Finance
            'journals.view', 'journals.create', 'journals.post', 'journals.reverse', 'accounting_periods.close',
            // Inventory
            'inventory.view', 'inventory.create', 'inventory.update', 'purchase_requests.approve',
            // Reports
            'reports.sales', 'reports.production', 'reports.finance', 'reports.export',
            // Master Data
            'services.manage', 'branches.manage', 'users.manage', 'roles.manage',
            // HR
            'employees.manage', 'payroll.manage', 'attendances.manage',
            // Fixed Assets
            'assets.manage', 'depreciation.process',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign existing permissions

        // 1. Developer
        $roleDeveloper = Role::findOrCreate('Developer');
        $roleDeveloper->givePermissionTo(Permission::all());

        // 2. Owner
        $roleOwner = Role::findOrCreate('Owner');
        $roleOwner->givePermissionTo([
            'orders.view', 'orders.create', 'orders.update', 'orders.refund',
            'production.view', 'production.update', 'production.bulk_update',
            'customers.view', 'customers.create', 'customers.update', 'loyalty.manage',
            'journals.view', 'journals.create', 'journals.post', 'journals.reverse', 'accounting_periods.close',
            'inventory.view', 'inventory.create', 'inventory.update', 'purchase_requests.approve',
            'reports.sales', 'reports.production', 'reports.finance', 'reports.export',
            'services.manage', 'branches.manage', 'users.manage', 'roles.manage',
            'employees.manage', 'payroll.manage', 'attendances.manage',
            'assets.manage', 'depreciation.process',
        ]);

        // 3. Super Admin
        $roleSuperAdmin = Role::findOrCreate('Super_Admin');
        $roleSuperAdmin->givePermissionTo([
            'orders.view', 'orders.create', 'orders.update', 'orders.refund',
            'production.view', 'production.update', 'production.bulk_update',
            'customers.view', 'customers.create', 'customers.update', 'loyalty.manage',
            'inventory.view', 'inventory.create', 'inventory.update', 'purchase_requests.approve',
            'reports.sales', 'reports.production', 'reports.export',
            'services.manage', 'branches.manage', 'users.manage',
            'employees.manage', 'attendances.manage',
            'assets.manage',
        ]);

        // 4. Branch Admin
        $roleBranchAdmin = Role::findOrCreate('Branch_Admin');
        $roleBranchAdmin->givePermissionTo([
            'orders.view', 'orders.create', 'orders.update',
            'production.view', 'production.update', 'production.bulk_update',
            'customers.view', 'customers.create', 'customers.update', 'loyalty.manage',
            'inventory.view', 'inventory.create', 'inventory.update',
            'reports.sales', 'reports.production', 'reports.export',
            'employees.manage', 'attendances.manage',
            'assets.manage',
        ]);

        // 5. Workshop Admin
        $roleWorkshopAdmin = Role::findOrCreate('Workshop_Admin');
        $roleWorkshopAdmin->givePermissionTo([
            'production.view', 'production.update', 'production.bulk_update',
            'inventory.view', 'inventory.update',
            'reports.production', 'reports.export',
        ]);

        // 6. Cashier
        $roleCashier = Role::findOrCreate('Cashier');
        $roleCashier->givePermissionTo([
            'orders.view', 'orders.create', 'orders.update',
            'production.view', 'production.update',
            'customers.view', 'customers.create', 'customers.update', 'loyalty.manage',
            'inventory.view',
        ]);

        // 7. Workshop Staff
        $roleWorkshopStaff = Role::findOrCreate('Workshop_Staff');
        $roleWorkshopStaff->givePermissionTo([
            'production.view', 'production.update',
        ]);

        // 8. CS Marketing
        $roleCSMarketing = Role::findOrCreate('CS_Marketing');
        $roleCSMarketing->givePermissionTo([
            'orders.view',
            'customers.view', 'customers.create', 'customers.update', 'loyalty.manage',
            'reports.sales',
        ]);

        // 9. Finance
        $roleFinance = Role::findOrCreate('Finance');
        $roleFinance->givePermissionTo([
            'orders.view',
            'journals.view', 'journals.create', 'journals.post', 'journals.reverse', 'accounting_periods.close',
            'payroll.manage',
            'reports.sales', 'reports.finance', 'reports.export',
            'assets.manage', 'depreciation.process',
        ]);
    }
}
