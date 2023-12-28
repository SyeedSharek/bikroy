<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'name' => 'Naimur Express',
            'email' => 'your@email.com',
            'image' => 'path/to/your/image.jpg',
            'header' => 'Sample Header',
            'footer' => 'Sample Footer',
            'about' => "# Welcome to Your App Name

At Your App Name, we believe in simplifying the process of buying and selling. Our classified app is designed to connect individuals and communities, making it easy for you to discover great deals and opportunities in your local area.

## What Sets Us Apart?

- **User-Friendly Interface:** Our app features an intuitive and user-friendly interface, ensuring a seamless experience for both buyers and sellers.

- **Wide Range of Categories:** Whether you're looking for electronics, furniture, real estate, jobs, or services, Your App Name has a diverse range of categories to meet your needs.

- **Local Focus, Global Reach:** While we emphasize local connections, our platform also allows you to reach a broader audience, making it ideal for both local transactions and expanding your reach.

## Our Mission

We are on a mission to empower individuals to make smart choices when it comes to buying and selling. By providing a reliable platform, we aim to foster a sense of community and trust among our users.

## How It Works

1. **Post Your Ad:** Easily create a listing for your item or service with a few simple steps. Add details, images, and contact information to attract potential buyers.

2. **Discover Great Deals:** Browse through a variety of listings in your area. Find the perfect item or service that suits your needs.

3. **Connect and Communicate:** Contact sellers directly through our secure messaging system. Arrange meetings, negotiate prices, and finalize the deal.

## Our Commitment to Security and Privacy

Your safety and privacy are our top priorities. We employ the latest security measures to ensure a secure and trustworthy environment for all users.

## Get Started Today

Join the Your App Name community and experience the convenience of buying and selling with ease. Whether you're a buyer or a seller, we're here to make your classified experience remarkable.

Thank you for choosing Your App Name!

[Download Now - App Store](#your-download-link) | [Google Play](#your-download-link)",
            'old_limit' => '2000',
            'new_limit' => '3000',
            'boosting_price' => '50.00',
            'boosting_discount_price' => '45.00',
            'terms_conditions' => "",
            'support_policy' => 'Sample support policy.',
            'privacy_policy' => 'Sample privacy policy.',
            'maintenance_mode' => false,
            'light_color' => '',
            'dark_color' => '',
            'facebook' => 'https://www.facebook.com/your_page',
            'instagram' => 'https://www.instagram.com/your_page',
            'twitter' => 'https://twitter.com/your_page',
            'wtsapp' => 'https://wa.me/your_phonenumber',

        ]);
    }
}
