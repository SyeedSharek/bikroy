<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Area::create([
        //     'name'        => 'Dhanmondi',
        //     'location_id' => 1,
        // ]);
        // Area::create([
        //     'name'        => 'Bashundhora',
        //     'location_id' => 1,
        // ]);
        // Area::create([
        //     'name'        => 'Chawkbazar',
        //     'location_id' => 2,
        // ]);
        // Area::create([
        //     'name'        => 'Kotwali',
        //     'location_id' => 2,
        // ]);

        Area::factory(20)->create();
    }
}
