<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Location;
use App\Models\SubCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->text(30);
        return [
            'title' => $title,
            'slug' => Str::lower(Str::replace(' ', '-', $title)),
            'brand_id' => Brand::inRandomOrder()->first()->id,
            'category_id' => Category::inRandomOrder()->first()->id,
            'subcategory_id' => SubCategory::inRandomOrder()->first()->id,
            'location_id' => Location::inRandomOrder()->first()->id,
            'area_id' => Area::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'is_new' => fake()->boolean(),
            'description' => fake()->text(),
            'price' => fake()->randomNumber(3),
            'images' => '["1699159161.png"]',
            'is_sold' => fake()->boolean(),
            'status' => fake()->boolean(true),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
