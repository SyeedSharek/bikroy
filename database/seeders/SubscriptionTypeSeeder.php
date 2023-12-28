<?php

namespace Database\Seeders;

use App\Models\SubscriptionType;
use Illuminate\Database\Seeder;

class SubscriptionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubscriptionType::create([
            'name'        => 'Weekly',
            'price'       => '10',
            'period_type' => 'week',
            'time_period' => 1,
        ]);
        SubscriptionType::create([
            'name'        => 'Monthly',
            'price'       => '50',
            'period_type' => 'month',
            'time_period' => 1,
        ]);
        SubscriptionType::create([
            'name'        => 'Half Yearly',
            'price'       => '300',
            'period_type' => 'month',
            'time_period' => 6,
        ]);
        SubscriptionType::create([
            'name'        => 'Yearly',
            'price'       => '500',
            'period_type' => 'year',
            'time_period' => 1,
        ]);
    }
}
