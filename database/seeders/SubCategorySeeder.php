<?php

namespace Database\Seeders;

use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class SubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // SubCategory::create([
        //     'name'        => 'Mobile',
        //     'category_id' => 1,
        // ]);
        // SubCategory::create([
        //     'name'        => 'Laptop',
        //     'category_id' => 1,
        // ]);
        // SubCategory::create([
        //     'name'        => 'Bike',
        //     'category_id' => 2,
        // ]);
        for ($i = 0; $i < 10; $i++) {
            $int = rand(1, 10);
            SubCategory::create([
                'name' => fake()->name(),
                'category_id' => $int,
            ]);
        }

        // SubCategory::factory(25)->create();
    }
}