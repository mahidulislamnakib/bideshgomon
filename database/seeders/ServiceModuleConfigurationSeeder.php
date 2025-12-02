<?php

namespace Database\Seeders;

use App\Models\ServiceModule;
use App\Models\AgencyType;
use Illuminate\Database\Seeder;

class ServiceModuleConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get agency type IDs
        $recruitingAgencyId = AgencyType::where('name', 'Recruiting Agency')->value('id');
        $studentConsultancyId = AgencyType::where('name', 'Student Consultancy')->value('id');

        $serviceConfigs = [
            // TRAVEL SERVICES
            'air-tickets' => [
                'is_marketplace' => false, // Global single agency model
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            'tourist-visa' => [
                'is_marketplace' => true, // Multiple agencies compete
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            'work-visa' => [
                'is_marketplace' => true, // Multiple agencies compete
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            'student-visa' => [
                'is_marketplace' => true, // Multiple agencies compete
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            'tour-packages' => [
                'is_marketplace' => true, // Multiple travel agencies
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            'hajj-umrah-packages' => [
                'is_marketplace' => true, // Multiple licensed agencies
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null, // Will be restricted by certification
            ],

            'travel-insurance' => [
                'is_marketplace' => true, // Quote comparison
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            // DOCUMENT SERVICES
            'attestation' => [
                'is_marketplace' => true,
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => true,
                'default_delivery_charge' => 200.00, // ৳200
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            'notary' => [
                'is_marketplace' => true,
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => true,
                'default_delivery_charge' => 150.00, // ৳150
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            'translation' => [
                'is_marketplace' => true,
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => true,
                'default_delivery_charge' => 100.00, // ৳100
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            'document-verification' => [
                'is_marketplace' => true,
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => true,
                'default_delivery_charge' => 180.00, // ৳180
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            // EDUCATION SERVICES
            'university-application' => [
                'is_marketplace' => false, // Can be exclusive per university
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => true, // Key feature
                'restricted_to_agency_type_id' => null,
            ],

            'course-enrollment' => [
                'is_marketplace' => true,
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            'language-test-prep' => [
                'is_marketplace' => true,
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            // EMPLOYMENT SERVICES (Critical restrictions)
            'job-posting' => [
                'is_marketplace' => false, // Not applicable
                'is_query_based' => false, // Direct posting by agency
                'requires_form_submission' => false, // Agency creates, not user
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => $recruitingAgencyId, // 🔑 ONLY recruiting agencies
            ],

            'job-application' => [
                'is_marketplace' => false, // Single agency (job poster)
                'is_query_based' => false,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            'cv-building' => [
                'is_marketplace' => false, // Platform direct service
                'is_query_based' => false,
                'requires_form_submission' => false, // Automated generation
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null, // No agency involvement
            ],

            'career-counseling' => [
                'is_marketplace' => true,
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            // MEDICAL & BIOMETRICS
            'medical-test' => [
                'is_marketplace' => true, // Multiple medical centers
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            'biometric-enrollment' => [
                'is_marketplace' => false, // Government centers
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            // CONSULTATION SERVICES
            'immigration-consultation' => [
                'is_marketplace' => true,
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            'legal-consultation' => [
                'is_marketplace' => true,
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            // FINANCIAL SERVICES
            'forex-exchange' => [
                'is_marketplace' => true,
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],

            'remittance' => [
                'is_marketplace' => true,
                'is_query_based' => true,
                'requires_form_submission' => true,
                'has_delivery_charges' => false,
                'supports_university_exclusivity' => false,
                'restricted_to_agency_type_id' => null,
            ],
        ];

        $updated = 0;
        $created = 0;

        foreach ($serviceConfigs as $slug => $config) {
            $serviceModule = ServiceModule::where('slug', $slug)->first();

            if ($serviceModule) {
                $serviceModule->update($config);
                $updated++;
                $this->command->info("✓ Updated: {$serviceModule->name}");
            } else {
                $this->command->warn("⚠ Service module not found: {$slug}");
            }
        }

        $this->command->info("\n=== Service Module Configuration Complete ===");
        $this->command->info("Updated: {$updated} service modules");
        $this->command->info("\nKey Features Configured:");
        $this->command->info("• Marketplace services: Tourist Visa, Work Visa, Student Visa, Tour Packages");
        $this->command->info("• Delivery services: Attestation (৳200), Notary (৳150), Translation (৳100)");
        $this->command->info("• University exclusivity: University Application");
        $this->command->info("• Recruiting agency only: Job Posting");
        $this->command->info("• Platform direct: CV Building");
    }
}
