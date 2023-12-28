<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\Admin::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'phone' => '123456789',
            'is_superadmin' => true,
            'password' => Hash::make('12345678'),
        ]);

        \App\Models\Admin::create([
            'name' => 'mng',
            'email' => 'manager@gmail.com',
            'phone' => '223456789',
            'is_superadmin' => false,
            'password' => Hash::make('12345678'),
        ]);

        $this->call([
            UserSeeder::class,
            CategoryTableSeeder::class,
            SubCategorySeeder::class,
            BrandSeeder::class,
            LocationSeeder::class,
            AreaSeeder::class,
            SubscriptionTypeSeeder::class,
            RolePermissionSeeder::class,
            ProductSeeder::class,
            SettingSeeder::class,
        ]);
    }
}
