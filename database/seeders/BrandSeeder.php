<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Brand::create([
        //     'name' => 'Samsung',
        //     'category_id' => '1',
        //     'subcategory_id' => '1',
        // ]);
        // Brand::create([
        //     'name' => 'HP',
        //     'category_id' => '2',
        //     'subcategory_id' => '2',
        // ]);
        // Brand::create([
        //     'name' => 'Toyota',
        //     'category_id' => '1',
        //     'subcategory_id' => '4',
        // ]);
        // Brand::create([
        //     'name' => 'Suzuki',
        //     'category_id' => '2',
        //     'subcategory_id' => '3',
        // ]);

        Brand::factory(50)->create();
    }
}
