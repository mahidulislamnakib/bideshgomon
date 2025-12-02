<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\ServiceModule;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "📦 Creating Package Comparison Data...\n";

        // Get services
        $visa = ServiceModule::where('slug', 'tourist-visa')->first();
        $travel = ServiceModule::where('slug', 'tour-packages')->first();
        $education = ServiceModule::where('slug', 'university-admission-assistance')->first();

        $packages = [
            // Visa Packages
            [
                'service_module_id' => $visa?->id,
                'name' => 'Basic Visa Processing',
                'slug' => 'basic-visa-processing',
                'description' => 'Essential visa processing service for single-entry tourist visas. Perfect for first-time travelers with straightforward applications.',
                'short_description' => 'Basic visa processing for single-entry tourist visas',
                'price' => 3500.00,
                'original_price' => null,
                'currency' => 'BDT',
                'price_unit' => 'fixed',
                'features' => [
                    'Document verification',
                    'Application form assistance',
                    'Embassy submission',
                    'Basic application tracking',
                    'Email support',
                ],
                'specifications' => [
                    'Processing Time' => '15-20 business days',
                    'Validity' => '3 months',
                    'Entry Type' => 'Single Entry',
                    'Support' => 'Email only',
                ],
                'inclusions' => [
                    'Document checklist',
                    'Form filling guidance',
                    'Embassy submission',
                    'Application tracking',
                ],
                'exclusions' => [
                    'Embassy fees',
                    'Translation services',
                    'Courier charges',
                    'Priority processing',
                ],
                'is_popular' => false,
                'is_featured' => false,
                'is_active' => true,
                'badge_text' => null,
                'badge_color' => 'blue',
                'sort_order' => 1,
            ],
            [
                'service_module_id' => $visa?->id,
                'name' => 'Standard Visa Package',
                'slug' => 'standard-visa-package',
                'description' => 'Most popular visa package with faster processing and dedicated support. Includes document preparation and interview guidance for confident application submission.',
                'short_description' => 'Most popular package with faster processing',
                'price' => 5500.00,
                'original_price' => 6500.00,
                'currency' => 'BDT',
                'price_unit' => 'fixed',
                'features' => [
                    'Document verification & preparation',
                    'Application form completion',
                    'Embassy submission with priority',
                    'Real-time status tracking',
                    'Interview preparation guide',
                    'Phone + Email support',
                    'Cover letter drafting',
                ],
                'specifications' => [
                    'Processing Time' => '10-12 business days',
                    'Validity' => '6 months',
                    'Entry Type' => 'Multiple Entry',
                    'Support' => 'Phone & Email',
                ],
                'inclusions' => [
                    'Complete document preparation',
                    'Cover letter',
                    'Interview preparation materials',
                    'Priority embassy submission',
                    'Dedicated support agent',
                ],
                'exclusions' => [
                    'Embassy fees',
                    'Translation services (charged separately)',
                    'Travel insurance',
                ],
                'is_popular' => true,
                'is_featured' => true,
                'is_active' => true,
                'badge_text' => 'Most Popular',
                'badge_color' => 'green',
                'sort_order' => 2,
            ],
            [
                'service_module_id' => $visa?->id,
                'name' => 'Premium Visa Concierge',
                'slug' => 'premium-visa-concierge',
                'description' => 'VIP visa processing service with white-glove treatment. Includes personal case manager, express processing, interview coaching, and guaranteed submission within 48 hours.',
                'short_description' => 'VIP service with personal case manager',
                'price' => 12000.00,
                'original_price' => 15000.00,
                'currency' => 'BDT',
                'price_unit' => 'fixed',
                'features' => [
                    'Personal case manager assigned',
                    'Complete document preparation',
                    'Professional translations included',
                    'Express embassy submission (48h)',
                    'Live application tracking dashboard',
                    '1-on-1 interview coaching session',
                    'Cover letter + itinerary planning',
                    '24/7 WhatsApp support',
                    'Document courier pickup',
                    'Post-approval travel planning',
                ],
                'specifications' => [
                    'Processing Time' => '5-7 business days',
                    'Validity' => '1 year (request)',
                    'Entry Type' => 'Multiple Entry',
                    'Support' => '24/7 WhatsApp + Phone',
                ],
                'inclusions' => [
                    'Personal case manager',
                    'Professional translations',
                    'Document courier',
                    'Interview coaching (1 hour)',
                    'Travel planning consultation',
                    'Itinerary preparation',
                    'Priority customer support',
                ],
                'exclusions' => [
                    'Embassy fees',
                    'Travel insurance policy',
                ],
                'is_popular' => false,
                'is_featured' => true,
                'is_active' => true,
                'badge_text' => 'Premium',
                'badge_color' => 'purple',
                'sort_order' => 3,
            ],

            // Travel Packages
            [
                'service_module_id' => $travel?->id,
                'name' => 'Thailand Explorer 5D/4N',
                'slug' => 'thailand-explorer-5d4n',
                'description' => 'Discover the beauty of Bangkok and Pattaya with this budget-friendly package. Perfect for first-time visitors wanting to experience Thai culture, temples, and beaches.',
                'short_description' => 'Bangkok + Pattaya tour for budget travelers',
                'price' => 25000.00,
                'original_price' => null,
                'currency' => 'BDT',
                'price_unit' => 'per_person',
                'features' => [
                    '4 nights hotel accommodation',
                    'Daily breakfast included',
                    'Airport transfers',
                    'City tours in Bangkok',
                    'Pattaya beach day trip',
                    'Local SIM card',
                ],
                'specifications' => [
                    'Duration' => '5 Days / 4 Nights',
                    'Accommodation' => '3-star hotels',
                    'Meals' => 'Breakfast only',
                    'Group Size' => 'Min 10 people',
                ],
                'inclusions' => [
                    'Round-trip flights',
                    'Hotel accommodation (twin sharing)',
                    'Airport transfers',
                    'Guided city tours',
                    'Breakfast daily',
                ],
                'exclusions' => [
                    'Lunch and dinner',
                    'Travel insurance',
                    'Personal expenses',
                    'Optional activities',
                ],
                'is_popular' => true,
                'is_featured' => false,
                'is_active' => true,
                'badge_text' => 'Best Value',
                'badge_color' => 'yellow',
                'sort_order' => 4,
                'max_bookings' => 50,
                'current_bookings' => 37,
            ],
            [
                'service_module_id' => $travel?->id,
                'name' => 'Malaysia Premium Tour 7D/6N',
                'slug' => 'malaysia-premium-tour-7d6n',
                'description' => 'Explore Kuala Lumpur, Penang, and Langkawi with our premium package. Includes 4-star hotels, all meals, and exclusive activities for comfortable family travel.',
                'short_description' => 'Premium Malaysia tour with 4-star hotels',
                'price' => 45000.00,
                'original_price' => 52000.00,
                'currency' => 'BDT',
                'price_unit' => 'per_person',
                'features' => [
                    '6 nights 4-star hotel stays',
                    'All meals included (breakfast, lunch, dinner)',
                    'Private airport transfers',
                    'Kuala Lumpur city tour + KL Tower',
                    'Penang heritage tour',
                    'Langkawi island hopping',
                    'English-speaking tour guide',
                    'Travel insurance',
                ],
                'specifications' => [
                    'Duration' => '7 Days / 6 Nights',
                    'Accommodation' => '4-star hotels',
                    'Meals' => 'All meals included',
                    'Group Size' => 'Min 6 people',
                ],
                'inclusions' => [
                    'Round-trip flights',
                    '4-star hotels (twin sharing)',
                    'All meals',
                    'Private transfers',
                    'Guided tours with entry fees',
                    'Travel insurance',
                ],
                'exclusions' => [
                    'Optional activities',
                    'Personal shopping',
                    'Tips and gratuities',
                ],
                'is_popular' => false,
                'is_featured' => true,
                'is_active' => true,
                'badge_text' => null,
                'badge_color' => 'blue',
                'sort_order' => 5,
                'max_bookings' => 30,
                'current_bookings' => 12,
            ],

            // Education Packages
            [
                'service_module_id' => $education?->id,
                'name' => 'Study Abroad Essentials',
                'slug' => 'study-abroad-essentials',
                'description' => 'Entry-level university admission support for self-motivated students. Includes university shortlisting, application review, and basic guidance.',
                'short_description' => 'Basic admission support for independent students',
                'price' => 8000.00,
                'original_price' => null,
                'currency' => 'BDT',
                'price_unit' => 'fixed',
                'features' => [
                    'University shortlisting (5 universities)',
                    'Application review',
                    'Document checklist',
                    'Email support',
                    'General study abroad guidance',
                ],
                'specifications' => [
                    'Universities' => 'Up to 5',
                    'Applications' => 'Review only',
                    'Support Duration' => '1 month',
                    'Response Time' => '48-72 hours',
                ],
                'inclusions' => [
                    'University research',
                    'Application review',
                    'Document checklist',
                    'Email support',
                ],
                'exclusions' => [
                    'Application submission',
                    'Essay writing',
                    'Visa assistance',
                    'Phone consultations',
                ],
                'is_popular' => false,
                'is_featured' => false,
                'is_active' => true,
                'badge_text' => null,
                'badge_color' => 'blue',
                'sort_order' => 6,
            ],
            [
                'service_module_id' => $education?->id,
                'name' => 'Complete Admission Package',
                'slug' => 'complete-admission-package',
                'description' => 'End-to-end university admission service from shortlisting to acceptance. Includes essay editing, application submission, and interview preparation.',
                'short_description' => 'Full-service admission support',
                'price' => 18000.00,
                'original_price' => 22000.00,
                'currency' => 'BDT',
                'price_unit' => 'fixed',
                'features' => [
                    'University shortlisting (10 universities)',
                    'Complete application preparation',
                    'Essay review and editing (3 rounds)',
                    'Application submission',
                    'Interview preparation',
                    'Scholarship guidance',
                    'Phone + Email support',
                    'Acceptance follow-up',
                ],
                'specifications' => [
                    'Universities' => 'Up to 10',
                    'Applications' => 'Full submission',
                    'Support Duration' => '6 months',
                    'Response Time' => '24 hours',
                ],
                'inclusions' => [
                    'University research and shortlisting',
                    'Application form completion',
                    'Essay editing (3 rounds)',
                    'Document preparation',
                    'Application submission',
                    'Interview prep materials',
                    'Scholarship search',
                ],
                'exclusions' => [
                    'University application fees',
                    'Standardized test fees',
                    'Visa processing',
                ],
                'is_popular' => true,
                'is_featured' => true,
                'is_active' => true,
                'badge_text' => 'Most Popular',
                'badge_color' => 'green',
                'sort_order' => 7,
            ],
        ];

        foreach ($packages as $packageData) {
            Package::create($packageData);
            echo "  ✅ Created: {$packageData['name']}\n";
        }

        echo "✅ Package comparison data seeded successfully!\n";
    }
}
