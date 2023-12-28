<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Product;
use Illuminate\Database\Seeder;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Location;
use App\Models\SubCategory;
use App\Models\User;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i < 1000; $i++) {
            $int = rand(1, 10);
            $category = Category::find($int);
            $subcategory = SubCategory::where('category_id', $category->id)->first();
            $location = Location::inRandomOrder()->first();
            $area = Area::where('location_id', $location->id)->first();
            if ($subcategory && $area) {
                $title = fake()->sentence();
                Product::create([
                    'title' => $title,
                    'slug' => str_replace(".", "", str_replace(" ", "_", $title . '_' . uniqid())),
                    'brand_id' => Brand::inRandomOrder()->first()->id,
                    'category_id' => $category->id,
                    'subcategory_id' => $subcategory->id,
                    'location_id' => $location->id,
                    'area_id' => $area->id,
                    'user_id' => User::latest()->first()->id,
                    'is_new' => rand(0, 1),
                    'description' => "<style>body {background-color: linen;}h1 {color: maroon;margin-left: 40px;}</style></head><body><h1>This is a heading</h1><p>This is a paragraph.</p></p>"
                    ,
                    'price' => 1000 * $i,
                    'images' => "null",
                    'is_sold' => rand(0, 1),
                    'status' => 1,
                    'is_boost' => rand(0, 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        // Product::factory(1000)->create();

        Product::whereNotNull('title')->update(['images' => ["https://www.popsci.com/uploads/2023/09/19/best-budget-laptops-working-from-home.jpg?auto=webp", "uploads/products/placeholder.png", "uploads/products/placeholder.png"]]);
    }
}
