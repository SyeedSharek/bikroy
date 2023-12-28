<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadminRole = Role::create([
            'name' => 'superadmin',
            'guard_name' => 'admin',
        ]);

        $managerRole = Role::create([
            'name' => 'manager',
            'guard_name' => 'admin',
        ]);

        $userRole = Role::create([
            'name' => 'user',
            'guard_name' => 'api',
        ]);

        $permissions = [
            'user list',

            'adminuser list',
            'create adminuser',
            'edit adminuser',
            'delete adminuser',

            'customeruser list',

            'role list',
            'create role',
            'edit role',
            'delete role',

            'category list',
            'create category',
            'edit category',
            'delete category',

            'subcategory list',
            'create subcategory',
            'edit subcategory',
            'delete subcategory',

            'brand list',
            'create brand',
            'edit brand',
            'delete brand',

            'location list',
            'create location',
            'edit location',
            'delete location',

            'area list',
            'create area',
            'edit area',
            'delete area',

            'subscription list',
            'create subscription',
            'edit subscription',
            'delete subscription',

            'setting list',
            'create setting',
            'edit setting',
            'delete setting',

            'post list',
            'report list',

            'general setting',
            'database',
            'smtp config',
            'payment config',

            'manage faq',

            'contact view',
            'delete contact',

            'slider list',
            'slider create',
            'slider update',
            'slider delete'
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'admin',
            ]);
        }

        $superadminRole->syncPermissions(Permission::all());
        $superadmin = Admin::first();
        $superadmin->assignRole($superadminRole);

        $managerPermissions = Permission::whereNotIn('name', ['delete adminuser'])->get();
        $managerRole->givePermissionTo($managerPermissions);
        $manager = Admin::find(2);
        $manager->assignRole($managerRole);

        $users = User::all();
        foreach ($users as $user) {
            $user->assignRole($userRole);
        }
    }
}
