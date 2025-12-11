<?php

namespace Database\Seeders;

use App\Models\Ad;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AdSeeder extends Seeder
{
    public function run()
    {
        // Sample Sidebar Ad (Desktop only)
        Ad::create([
            'title' => 'Special Visa Processing Service',
            'body' => 'Get your visa processed in just 48 hours! Our expert team ensures quick approval with 98% success rate.',
            'image_url' => null,
            'cta_url' => '/services/visa-processing',
            'cta_text' => 'Apply Now',
            'placement' => 'sidebar',
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addDays(30),
            'priority' => 90,
            'status' => 'active',
            'meta' => [
                'pages' => ['user_dashboard', 'visa_applications'],
                'roles' => ['user'],
                'devices' => ['desktop'],
            ],
        ]);

        // Sample Inline Ad (All devices)
        Ad::create([
            'title' => 'Free Visa Consultation',
            'body' => 'Schedule a free consultation with our visa experts. Available 24/7 to answer all your questions.',
            'image_url' => null,
            'cta_url' => '/contact',
            'cta_text' => 'Book Now',
            'placement' => 'inline',
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addDays(60),
            'priority' => 80,
            'status' => 'active',
            'meta' => [
                'pages' => ['blog_posts', 'faq'],
                'roles' => ['user', 'agency'],
                'devices' => ['desktop', 'tablet', 'mobile'],
            ],
        ]);

        // Sample Empty State Ad
        Ad::create([
            'title' => 'Start Your Journey Today',
            'body' => 'Explore thousands of job opportunities abroad. Upload your CV and let employers find you.',
            'image_url' => null,
            'cta_url' => '/jobs',
            'cta_text' => 'Browse Jobs',
            'placement' => 'empty_state',
            'start_at' => Carbon::now(),
            'end_at' => null,
            'priority' => 70,
            'status' => 'active',
            'meta' => [
                'pages' => ['jobs_list'],
                'roles' => [],
                'devices' => [],
            ],
        ]);

        // Sample Sticky Bottom Ad (Mobile only)
        Ad::create([
            'title' => 'Download Our Mobile App',
            'body' => 'Track your visa application on the go. Get instant notifications.',
            'image_url' => null,
            'cta_url' => 'https://play.google.com/store',
            'cta_text' => 'Get App',
            'placement' => 'sticky_bottom',
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addDays(90),
            'priority' => 60,
            'status' => 'active',
            'meta' => [
                'pages' => [],
                'roles' => ['user'],
                'devices' => ['mobile'],
            ],
        ]);

        // Sample Banner Ad
        Ad::create([
            'title' => '🎉 Limited Time Offer: 20% Off All Services',
            'body' => 'Use code VISA20 at checkout. Valid until end of month.',
            'image_url' => null,
            'cta_url' => '/services',
            'cta_text' => 'View Services',
            'placement' => 'banner',
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addDays(15),
            'priority' => 100,
            'status' => 'active',
            'meta' => [
                'pages' => [],
                'roles' => [],
                'devices' => [],
            ],
        ]);

        // Sample Draft Ad
        Ad::create([
            'title' => 'Summer Travel Packages',
            'body' => 'Exclusive packages to popular destinations. Book early and save up to 30%.',
            'image_url' => null,
            'cta_url' => '/packages',
            'cta_text' => 'Explore Packages',
            'placement' => 'inline',
            'start_at' => null,
            'end_at' => null,
            'priority' => 50,
            'status' => 'draft',
            'meta' => [],
        ]);

        // Sample Paused Ad
        Ad::create([
            'title' => 'Weekend Workshop: Visa Application Tips',
            'body' => 'Join our free workshop this Saturday. Learn from visa experts.',
            'image_url' => null,
            'cta_url' => '/events',
            'cta_text' => 'Register',
            'placement' => 'sidebar',
            'start_at' => Carbon::now()->subDays(5),
            'end_at' => Carbon::now()->addDays(10),
            'priority' => 75,
            'status' => 'paused',
            'meta' => [
                'pages' => ['user_dashboard'],
                'roles' => ['user'],
                'devices' => ['desktop'],
            ],
        ]);
    }
}
