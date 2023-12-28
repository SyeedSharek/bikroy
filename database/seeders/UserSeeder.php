<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'User ',
            'email' => 'user@gmail.com',
            'email_verified_at' => now(),
            'phone' => fake()->phoneNumber(),
            'phone_verified_at' => now(),
            'password' => Hash::make('123'),
            'address' => fake()->address(),
            'postal_code' => fake()->randomNumber(5),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => fake()->country(),
            'is_banned' => fake()->boolean(),
            'status' => fake()->boolean(true),
        ]);

        $data = [];
        for ($i = 1; $i < 10; $i++) {
            $data[] = [
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@gmail.com',
                'email_verified_at' => now(),
                'phone' => fake()->phoneNumber(),
                'phone_verified_at' => now(),
                'password' => Hash::make('123'),
                'address' => fake()->address(),
                'postal_code' => fake()->randomNumber(5),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'country' => fake()->country(),
                'is_banned' => fake()->boolean(),
                'status' => fake()->boolean(true),
            ];
        }

        $chunks = array_chunk($data, 10);
        foreach ($chunks as $chunk) {
            User::insert($chunk);
        }
    }
}