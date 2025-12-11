<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use App\Models\MasterDocument;
use Illuminate\Database\Seeder;

class InternationalVisaDocumentsSeeder extends Seeder
{
    /**
     * Seed international standard visa documents based on:
     * - Schengen visa requirements
     * - US Department of State standards
     * - UK Home Office requirements
     * - Australian Department of Home Affairs
     * - Canadian Immigration standards
     */
    public function run(): void
    {
        // 1. Identity Documents Category
        $identityCategory = DocumentCategory::create([
            'name' => 'Identity Documents',
            'description' => 'Personal identification documents',
            'sort_order' => 1,
        ]);

        $identityDocs = [
            [
                'document_name' => 'Passport',
                'description' => 'Valid passport with minimum 6 months validity',
                'specifications' => 'Must have at least 2 blank pages. Machine-readable passport required. Validity must extend 6 months beyond intended stay.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 3650, // 10 years
                'international_standard' => 'ICAO Doc 9303',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'National ID Card',
                'description' => 'Government-issued national identity card',
                'specifications' => 'Copy of both sides. Must be valid and not expired.',
                'translation_required' => true,
                'notarization_required' => false,
                'typical_validity_days' => 3650,
                'international_standard' => 'ISO/IEC 7810',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'Birth Certificate',
                'description' => 'Official birth certificate',
                'specifications' => 'Original or certified copy. Must show full name, date of birth, place of birth, and parents\' names.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => null,
                'international_standard' => 'UN Legal Identity',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'Marriage Certificate',
                'description' => 'Official marriage certificate (if applicable)',
                'specifications' => 'Original or certified copy. Required if traveling with spouse or name changed after marriage.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => null,
                'international_standard' => 'UN Legal Identity',
                'sort_order' => 4,
            ],
            [
                'document_name' => 'Passport-Size Photographs',
                'description' => 'Recent passport-size color photographs',
                'specifications' => 'Usually 2-4 photos required. White or light background. 35mm x 45mm or 2x2 inches. Taken within last 6 months. No glasses, neutral expression.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 180, // 6 months
                'international_standard' => 'ICAO Photo Standards',
                'sort_order' => 5,
            ],
        ];

        foreach ($identityDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $identityCategory->id]));
        }

        // 2. Financial Documents Category
        $financialCategory = DocumentCategory::create([
            'name' => 'Financial Documents',
            'description' => 'Proof of financial stability and ability to support travel',
            'sort_order' => 2,
        ]);

        $financialDocs = [
            [
                'document_name' => 'Bank Statements',
                'description' => 'Bank statements for last 3-6 months',
                'specifications' => 'Must be stamped and signed by bank. Should show regular transactions and sufficient balance. Original bank statements preferred.',
                'translation_required' => true,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'IBAN/SWIFT',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'Bank Solvency Certificate',
                'description' => 'Certificate from bank showing account balance',
                'specifications' => 'On bank letterhead with seal. Must state account holder name, account number, current balance, and average balance.',
                'translation_required' => true,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'Banking Standard',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'Tax Returns (ITR)',
                'description' => 'Income tax returns for last 2-3 years',
                'specifications' => 'Government-stamped acknowledgment. Shows annual income and tax paid. Required for business owners and self-employed.',
                'translation_required' => true,
                'notarization_required' => false,
                'typical_validity_days' => 365,
                'international_standard' => 'Tax Authority Standard',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'TIN Certificate',
                'description' => 'Tax Identification Number certificate',
                'specifications' => 'Government-issued TIN certificate showing taxpayer registration.',
                'translation_required' => true,
                'notarization_required' => false,
                'typical_validity_days' => null,
                'international_standard' => 'Tax Authority Standard',
                'sort_order' => 4,
            ],
            [
                'document_name' => 'Sponsorship Letter',
                'description' => 'Letter from sponsor (if sponsored)',
                'specifications' => 'Must state relationship, purpose of sponsorship, financial commitment. Include sponsor\'s ID, bank statements, and employment proof.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => 90,
                'international_standard' => 'Notarized Affidavit',
                'sort_order' => 5,
            ],
        ];

        foreach ($financialDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $financialCategory->id]));
        }

        // 3. Employment Documents Category
        $employmentCategory = DocumentCategory::create([
            'name' => 'Employment Documents',
            'description' => 'Documents related to employment status',
            'sort_order' => 3,
        ]);

        $employmentDocs = [
            [
                'document_name' => 'Employment Letter / NOC',
                'description' => 'Letter from employer on company letterhead',
                'specifications' => 'Must include: employee name, designation, salary, joining date, leave approval, company contact. Signed by HR/Manager with company seal.',
                'translation_required' => true,
                'notarization_required' => false,
                'typical_validity_days' => 90,
                'international_standard' => 'Corporate Standard',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'Pay Slips',
                'description' => 'Salary slips for last 3-6 months',
                'specifications' => 'Company-issued payslips showing monthly salary, deductions, and net pay.',
                'translation_required' => true,
                'notarization_required' => false,
                'typical_validity_days' => 180,
                'international_standard' => 'Corporate Standard',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'Employee ID Card',
                'description' => 'Company-issued employee identification card',
                'specifications' => 'Copy of both sides. Must be valid and show employee name, photo, designation.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 365,
                'international_standard' => 'Corporate Standard',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'Visiting Card',
                'description' => 'Professional business card',
                'specifications' => 'Shows name, designation, company name, contact information.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 365,
                'international_standard' => 'Corporate Standard',
                'sort_order' => 4,
            ],
        ];

        foreach ($employmentDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $employmentCategory->id]));
        }

        // 4. Business Documents Category
        $businessCategory = DocumentCategory::create([
            'name' => 'Business Documents',
            'description' => 'Documents for business owners and self-employed',
            'sort_order' => 4,
        ]);

        $businessDocs = [
            [
                'document_name' => 'Trade License',
                'description' => 'Valid business/trade license',
                'specifications' => 'Government-issued trade license showing business registration, owner name, business type, validity period.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => 365,
                'international_standard' => 'Business Registration',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'Company Registration Certificate',
                'description' => 'Certificate of incorporation',
                'specifications' => 'Shows company name, registration number, date of incorporation, registered office address.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => null,
                'international_standard' => 'Corporate Law',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'Memorandum of Association',
                'description' => 'Company MOA and Articles of Association',
                'specifications' => 'Legal document defining company objectives, share structure, rights and responsibilities.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => null,
                'international_standard' => 'Corporate Law',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'Company Letterhead',
                'description' => 'Official company letterhead sample',
                'specifications' => 'Shows company name, logo, address, contact information.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 365,
                'international_standard' => 'Corporate Standard',
                'sort_order' => 4,
            ],
            [
                'document_name' => 'Business Bank Statements',
                'description' => 'Company bank statements for last 6 months',
                'specifications' => 'Must show business transactions, turnover, and healthy cash flow.',
                'translation_required' => true,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'Banking Standard',
                'sort_order' => 5,
            ],
        ];

        foreach ($businessDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $businessCategory->id]));
        }

        // 5. Educational Documents Category
        $educationCategory = DocumentCategory::create([
            'name' => 'Educational Documents',
            'description' => 'Documents for students',
            'sort_order' => 5,
        ]);

        $educationDocs = [
            [
                'document_name' => 'Student ID Card',
                'description' => 'Valid student identification card',
                'specifications' => 'Copy of both sides. Must be current and show student name, photo, institution name.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 365,
                'international_standard' => 'Academic Standard',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'School/University NOC',
                'description' => 'No Objection Certificate from educational institution',
                'specifications' => 'On institution letterhead. States student name, program, approval for travel during vacation.',
                'translation_required' => true,
                'notarization_required' => false,
                'typical_validity_days' => 90,
                'international_standard' => 'Academic Standard',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'Parent\'s Bank Statements',
                'description' => 'Bank statements of parents/guardian',
                'specifications' => 'Last 6 months bank statements of sponsor parent showing sufficient funds.',
                'translation_required' => true,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'Banking Standard',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'Parent\'s Employment Letter',
                'description' => 'Employment proof of sponsoring parent',
                'specifications' => 'Letter from parent\'s employer showing position, salary, employment duration.',
                'translation_required' => true,
                'notarization_required' => false,
                'typical_validity_days' => 90,
                'international_standard' => 'Corporate Standard',
                'sort_order' => 4,
            ],
            [
                'document_name' => 'Parent\'s Marriage Certificate',
                'description' => 'Marriage certificate of parents',
                'specifications' => 'Proves relationship between sponsoring parents and student.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => null,
                'international_standard' => 'UN Legal Identity',
                'sort_order' => 5,
            ],
        ];

        foreach ($educationDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $educationCategory->id]));
        }

        // 6. Travel Documents Category
        $travelCategory = DocumentCategory::create([
            'name' => 'Travel Documents',
            'description' => 'Travel itinerary and bookings',
            'sort_order' => 6,
        ]);

        $travelDocs = [
            [
                'document_name' => 'Flight Itinerary',
                'description' => 'Confirmed flight booking or itinerary',
                'specifications' => 'Shows departure and return dates, flight numbers, booking reference. Note: Some embassies accept dummy bookings.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'IATA',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'Hotel Reservations',
                'description' => 'Confirmed hotel bookings',
                'specifications' => 'Shows hotel name, address, check-in/out dates, booking confirmation. Must cover entire stay.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'Hospitality Standard',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'Travel Insurance',
                'description' => 'Travel medical insurance',
                'specifications' => 'Coverage minimum: €30,000 for Schengen, $50,000 for US. Must cover medical emergency, repatriation, emergency evacuation.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 180,
                'international_standard' => 'Insurance Standard',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'Tour Itinerary',
                'description' => 'Detailed day-by-day travel plan',
                'specifications' => 'Shows places to visit, activities planned, transportation arrangements.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'Tourism Standard',
                'sort_order' => 4,
            ],
        ];

        foreach ($travelDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $travelCategory->id]));
        }

        // 7. Supporting Documents Category
        $supportingCategory = DocumentCategory::create([
            'name' => 'Supporting Documents',
            'description' => 'Additional supporting documentation',
            'sort_order' => 7,
        ]);

        $supportingDocs = [
            [
                'document_name' => 'Cover Letter',
                'description' => 'Personal cover letter to visa officer',
                'specifications' => 'Explains purpose of visit, travel plans, ties to home country, assurance of return. Signed and dated.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'Visa Application Standard',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'Invitation Letter',
                'description' => 'Invitation from host in destination country',
                'specifications' => 'Letter from inviter with their ID copy, address proof, explaining relationship and purpose of visit.',
                'translation_required' => false,
                'notarization_required' => true,
                'typical_validity_days' => 90,
                'international_standard' => 'Notarized Affidavit',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'Previous Visas',
                'description' => 'Copies of previous visas and travel history',
                'specifications' => 'Photocopies of previous visas, entry/exit stamps. Demonstrates travel history and compliance.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => null,
                'international_standard' => 'Visa History',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'Property Documents',
                'description' => 'Property ownership documents (if applicable)',
                'specifications' => 'Land deed, house ownership papers. Proves ties to home country.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => null,
                'international_standard' => 'Property Law',
                'sort_order' => 4,
            ],
            [
                'document_name' => 'Police Clearance Certificate',
                'description' => 'Criminal background check certificate',
                'specifications' => 'Issued by police authority. Must be recent (usually within 6 months). Required for long-term visas.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => 180,
                'international_standard' => 'Law Enforcement Standard',
                'sort_order' => 5,
            ],
        ];

        foreach ($supportingDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $supportingCategory->id]));
        }

        // 8. Medical Documents Category
        $medicalCategory = DocumentCategory::create([
            'name' => 'Medical Documents',
            'description' => 'Health and medical certificates',
            'sort_order' => 8,
        ]);

        $medicalDocs = [
            [
                'document_name' => 'Medical Certificate',
                'description' => 'Health fitness certificate from doctor',
                'specifications' => 'Certificate from registered physician stating applicant is fit to travel. Required for some countries.',
                'translation_required' => true,
                'notarization_required' => false,
                'typical_validity_days' => 90,
                'international_standard' => 'Medical Standard',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'Vaccination Certificate',
                'description' => 'Proof of required vaccinations',
                'specifications' => 'WHO Yellow Card or country-specific vaccination proof (Yellow Fever, COVID-19, etc.).',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 3650,
                'international_standard' => 'WHO IHR',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'TB Test Certificate',
                'description' => 'Tuberculosis screening certificate',
                'specifications' => 'Required for UK and some other countries. Must be from approved clinic.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 180,
                'international_standard' => 'WHO TB Guidelines',
                'sort_order' => 3,
            ],
        ];

        foreach ($medicalDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $medicalCategory->id]));
        }

        // 9. Schengen-Specific Documents Category
        $schengenCategory = DocumentCategory::create([
            'name' => 'Schengen-Specific Documents',
            'description' => 'Additional documents required for Schengen visa applications',
            'sort_order' => 9,
        ]);

        $schengenDocs = [
            [
                'document_name' => 'Schengen Visa Application Form',
                'description' => 'Completed and signed Schengen visa application form',
                'specifications' => 'Fill online or download from embassy website. Must be signed and dated. All fields mandatory.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'Schengen Regulation',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'Proof of Accommodation (Schengen)',
                'description' => 'Hotel bookings or invitation letter for entire Schengen stay',
                'specifications' => 'Must cover all Schengen countries visited. Hotel confirmations with address, or host invitation with address proof and ID copy.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'Schengen Border Code',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'Travel Medical Insurance (Schengen)',
                'description' => 'Minimum €30,000 coverage for all Schengen states',
                'specifications' => 'Must be valid for entire Schengen area. Coverage: medical emergency, emergency hospital treatment, repatriation. From approved insurance provider.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 90,
                'international_standard' => 'Schengen Insurance Requirement',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'Proof of Sufficient Funds (Schengen)',
                'description' => 'Evidence of financial means for entire trip',
                'specifications' => 'Minimum daily requirement varies by country (€50-100/day). Bank statements, traveler\'s cheques, or credit card with statement.',
                'translation_required' => true,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'Schengen Financial Requirement',
                'sort_order' => 4,
            ],
        ];

        foreach ($schengenDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $schengenCategory->id]));
        }

        // 10. USA-Specific Documents Category
        $usaCategory = DocumentCategory::create([
            'name' => 'USA-Specific Documents',
            'description' => 'Additional documents for USA visa applications',
            'sort_order' => 10,
        ]);

        $usaDocs = [
            [
                'document_name' => 'DS-160 Confirmation Page',
                'description' => 'Confirmation page of completed DS-160 form',
                'specifications' => 'Online nonimmigrant visa application. Print confirmation page with barcode. Photo must be uploaded in DS-160.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'US Department of State',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'Visa Fee Payment Receipt',
                'description' => 'Proof of visa application fee payment',
                'specifications' => 'MRV fee receipt showing payment confirmation. Required for interview scheduling.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 365,
                'international_standard' => 'US Consular Affairs',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'Interview Appointment Letter',
                'description' => 'Visa interview appointment confirmation',
                'specifications' => 'Print appointment confirmation from CGI Federal website. Shows date, time, location of interview.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 90,
                'international_standard' => 'US Embassy Standard',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'I-20 Form (Students)',
                'description' => 'Certificate of Eligibility for F-1 Student Visa',
                'specifications' => 'Issued by SEVP-approved US school. Must be signed by student and school official. Required for F-1 visa.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 365,
                'international_standard' => 'SEVP Standard',
                'sort_order' => 4,
            ],
            [
                'document_name' => 'SEVIS Fee Payment Receipt',
                'description' => 'I-901 SEVIS fee payment confirmation',
                'specifications' => 'Paid before visa interview. For F, M, and J visa categories. Print receipt from SEVIS website.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 365,
                'international_standard' => 'SEVP Standard',
                'sort_order' => 5,
            ],
        ];

        foreach ($usaDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $usaCategory->id]));
        }

        // 11. UK-Specific Documents Category
        $ukCategory = DocumentCategory::create([
            'name' => 'UK-Specific Documents',
            'description' => 'Additional documents for UK visa applications',
            'sort_order' => 11,
        ]);

        $ukDocs = [
            [
                'document_name' => 'UK Visa Application Form (VAF)',
                'description' => 'Completed online visa application form',
                'specifications' => 'Complete on gov.uk website. Print and sign. Include IHS reference number (Immigration Health Surcharge).',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'UK Visas & Immigration',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'IHS Payment Confirmation',
                'description' => 'Immigration Health Surcharge payment receipt',
                'specifications' => 'Paid during online application. Gives access to NHS. Amount depends on visa length.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 90,
                'international_standard' => 'UK Home Office',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'TB Test Certificate (UK)',
                'description' => 'Tuberculosis test from approved clinic',
                'specifications' => 'Required for Bangladesh applicants staying 6+ months. Must be from UK-approved clinic. Valid 6 months.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 180,
                'international_standard' => 'UK TB Testing',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'CAS Statement (Students)',
                'description' => 'Confirmation of Acceptance for Studies',
                'specifications' => 'Issued by UK university. 14-digit reference number. Valid 6 months. Required for Tier 4 Student visa.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 180,
                'international_standard' => 'UK Student Visa',
                'sort_order' => 4,
            ],
            [
                'document_name' => 'Academic Technology Approval Scheme',
                'description' => 'ATAS certificate (for specific STEM courses)',
                'specifications' => 'Required for certain science, engineering subjects. Apply before visa. Certificate of Sponsorship number needed.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 365,
                'international_standard' => 'UK ATAS',
                'sort_order' => 5,
            ],
        ];

        foreach ($ukDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $ukCategory->id]));
        }

        // 12. Canada-Specific Documents Category
        $canadaCategory = DocumentCategory::create([
            'name' => 'Canada-Specific Documents',
            'description' => 'Additional documents for Canada visa applications',
            'sort_order' => 12,
        ]);

        $canadaDocs = [
            [
                'document_name' => 'Biometrics Instruction Letter',
                'description' => 'Biometrics collection appointment letter',
                'specifications' => 'Received after online application. Take to VAC for fingerprints and photo. Valid 30 days.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 30,
                'international_standard' => 'IRCC Standard',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'Letter of Acceptance (Canada)',
                'description' => 'University/college acceptance letter',
                'specifications' => 'From DLI (Designated Learning Institution). Must state program, duration, tuition fees, start date.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 180,
                'international_standard' => 'IRCC DLI',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'Quebec Acceptance Certificate (CAQ)',
                'description' => 'Certificat d\'acceptation du Québec',
                'specifications' => 'Required for study/work in Quebec. Apply to MIFI. Get before federal study permit application.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 365,
                'international_standard' => 'Quebec Immigration',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'GIC Certificate',
                'description' => 'Guaranteed Investment Certificate',
                'specifications' => 'CAD $10,000+ in approved Canadian bank. Proves financial stability. Speeds up Student Direct Stream (SDS) processing.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 365,
                'international_standard' => 'IRCC SDS',
                'sort_order' => 4,
            ],
            [
                'document_name' => 'Provincial Nomination Certificate',
                'description' => 'Provincial Nominee Program certificate',
                'specifications' => 'Issued by Canadian province. Required for provincial nominee PR applications. Adds 600 CRS points.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 180,
                'international_standard' => 'IRCC PNP',
                'sort_order' => 5,
            ],
        ];

        foreach ($canadaDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $canadaCategory->id]));
        }

        // 13. Australia-Specific Documents Category
        $australiaCategory = DocumentCategory::create([
            'name' => 'Australia-Specific Documents',
            'description' => 'Additional documents for Australia visa applications',
            'sort_order' => 13,
        ]);

        $australiaDocs = [
            [
                'document_name' => 'Confirmation of Enrolment (CoE)',
                'description' => 'Electronic Confirmation of Enrolment',
                'specifications' => 'Issued by Australian education provider after tuition fee payment. Required for student visa (subclass 500).',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 180,
                'international_standard' => 'Australian Student Visa',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'Overseas Student Health Cover (OSHC)',
                'description' => 'Health insurance for international students',
                'specifications' => 'Mandatory for student visa. Must cover entire stay. Buy from approved providers (Medibank, BUPA, etc.).',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 1095,
                'international_standard' => 'Australian Health Insurance',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'GTE Statement',
                'description' => 'Genuine Temporary Entrant statement',
                'specifications' => '1-2 pages explaining study plans, career goals, reasons for Australia, ties to home country. Critical for student visa.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 90,
                'international_standard' => 'Australian Student Visa',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'English Proficiency Test',
                'description' => 'IELTS, PTE, or TOEFL score report',
                'specifications' => 'Required for most Australian visas. Minimum scores: IELTS 5.5+ for student, 6.0+ for work. Test valid 2 years.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 730,
                'international_standard' => 'Australian Immigration',
                'sort_order' => 4,
            ],
            [
                'document_name' => 'Skills Assessment',
                'description' => 'Occupation skills assessment',
                'specifications' => 'Required for skilled migration. Done by assessing authority (ACS, Engineers Australia, etc.). Shows qualifications match occupation.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 1095,
                'international_standard' => 'Australian Skilled Migration',
                'sort_order' => 5,
            ],
        ];

        foreach ($australiaDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $australiaCategory->id]));
        }

        // 14. Family & Dependent Documents Category
        $familyCategory = DocumentCategory::create([
            'name' => 'Family & Dependent Documents',
            'description' => 'Documents for family visa applications and dependents',
            'sort_order' => 14,
        ]);

        $familyDocs = [
            [
                'document_name' => 'Relationship Proof Documents',
                'description' => 'Evidence of genuine relationship',
                'specifications' => 'Photos together, joint bank accounts, correspondence, travel together, social media evidence, sworn affidavits from friends/family.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => 90,
                'international_standard' => 'Immigration Standard',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'Child Birth Certificate',
                'description' => 'Birth certificates of dependent children',
                'specifications' => 'Original or certified copy. Must show both parents\' names. Required for dependent visa.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => null,
                'international_standard' => 'UN Legal Identity',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'Custody Documents',
                'description' => 'Legal custody papers (if applicable)',
                'specifications' => 'Court-issued custody papers if parents separated/divorced. Consent letter from other parent if child traveling with one parent.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => null,
                'international_standard' => 'Family Law',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'Consent Letter for Minors',
                'description' => 'Parental consent for minor traveling',
                'specifications' => 'Notarized letter from non-traveling parent(s). Include parent ID copies, contact info. Required for minors under 18.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => 180,
                'international_standard' => 'Border Security',
                'sort_order' => 4,
            ],
            [
                'document_name' => 'Guardianship Certificate',
                'description' => 'Legal guardianship documents',
                'specifications' => 'Court-issued guardianship certificate if applicant is legal guardian of minor.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => null,
                'international_standard' => 'Family Law',
                'sort_order' => 5,
            ],
        ];

        foreach ($familyDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $familyCategory->id]));
        }

        // 15. Middle East Work Visa Documents
        $middleEastCategory = DocumentCategory::create([
            'name' => 'Middle East Work Visa Documents',
            'description' => 'Specific documents for Gulf countries work visas',
            'sort_order' => 15,
        ]);

        $middleEastDocs = [
            [
                'document_name' => 'Attested Educational Certificates',
                'description' => 'Degree certificates attested by embassy',
                'specifications' => 'Must be attested by: University → HEC/Education Ministry → Foreign Ministry → Embassy of destination country. Full attestation chain required.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => null,
                'international_standard' => 'GCC Attestation',
                'sort_order' => 1,
            ],
            [
                'document_name' => 'Attested Experience Certificates',
                'description' => 'Work experience letters attested',
                'specifications' => 'Employment letters attested by Chamber of Commerce and Foreign Ministry. Must show job title, duration, duties.',
                'translation_required' => true,
                'notarization_required' => true,
                'typical_validity_days' => 365,
                'international_standard' => 'GCC Attestation',
                'sort_order' => 2,
            ],
            [
                'document_name' => 'Medical Fitness Certificate',
                'description' => 'Medical test from authorized center',
                'specifications' => 'Blood tests (HIV, Hepatitis), chest X-ray, general fitness. From GAMCA/GAMCA-approved center. Mandatory for GCC work visa.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 90,
                'international_standard' => 'GAMCA/GCC Health',
                'sort_order' => 3,
            ],
            [
                'document_name' => 'Work Contract (Arabic)',
                'description' => 'Employment contract in Arabic',
                'specifications' => 'Signed by employer and employee. States salary, benefits, job role, contract duration. Must be approved by Labor Ministry.',
                'translation_required' => false,
                'notarization_required' => true,
                'typical_validity_days' => 90,
                'international_standard' => 'GCC Labor Law',
                'sort_order' => 4,
            ],
            [
                'document_name' => 'Job Offer Letter',
                'description' => 'Official job offer from employer',
                'specifications' => 'On company letterhead. Details: position, salary, benefits, start date. Company must be registered.',
                'translation_required' => false,
                'notarization_required' => false,
                'typical_validity_days' => 90,
                'international_standard' => 'GCC Employment',
                'sort_order' => 5,
            ],
        ];

        foreach ($middleEastDocs as $doc) {
            MasterDocument::create(array_merge($doc, ['category_id' => $middleEastCategory->id]));
        }

        $this->command->info('✅ Comprehensive master documents seeded successfully!');
        $this->command->info('📊 Total categories: 15');
        $this->command->info('📄 Total documents: 70+');
    }
}
