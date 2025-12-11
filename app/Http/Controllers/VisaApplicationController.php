<?php

namespace App\Http\Controllers;

use App\Models\VisaApplication;
use App\Models\VisaDocument;
use App\Traits\CreatesServiceApplications;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VisaApplicationController extends Controller
{
    use AuthorizesRequests;
    use CreatesServiceApplications;

    /**
     * Display visa types and countries
     */
    public function index()
    {
        $visaTypes = [
            'tourist' => [
                'name' => 'Tourist Visa',
                'description' => 'For leisure, vacation, or visiting family and friends',
                'icon' => 'camera',
            ],
            'business' => [
                'name' => 'Business Visa',
                'description' => 'For business meetings, conferences, or trade activities',
                'icon' => 'briefcase',
            ],
            'student' => [
                'name' => 'Student Visa',
                'description' => 'For pursuing education or academic programs',
                'icon' => 'academic-cap',
            ],
            'work' => [
                'name' => 'Work Visa',
                'description' => 'For employment or professional work opportunities',
                'icon' => 'identification',
            ],
            'medical' => [
                'name' => 'Medical Visa',
                'description' => 'For medical treatment or healthcare services',
                'icon' => 'heart',
            ],
            'transit' => [
                'name' => 'Transit Visa',
                'description' => 'For passing through a country en route to another destination',
                'icon' => 'globe',
            ],
        ];

        $popularCountries = [
            ['name' => 'United States', 'code' => 'USA', 'flag' => '🇺🇸', 'service_fee' => 15000],
            ['name' => 'United Kingdom', 'code' => 'GBR', 'flag' => '🇬🇧', 'service_fee' => 12000],
            ['name' => 'Canada', 'code' => 'CAN', 'flag' => '🇨🇦', 'service_fee' => 10000],
            ['name' => 'Australia', 'code' => 'AUS', 'flag' => '🇦🇺', 'service_fee' => 13000],
            ['name' => 'Schengen', 'code' => 'SCH', 'flag' => '🇪🇺', 'service_fee' => 8000],
            ['name' => 'United Arab Emirates', 'code' => 'ARE', 'flag' => '🇦🇪', 'service_fee' => 6000],
            ['name' => 'Singapore', 'code' => 'SGP', 'flag' => '🇸🇬', 'service_fee' => 7000],
            ['name' => 'Malaysia', 'code' => 'MYS', 'flag' => '🇲🇾', 'service_fee' => 3000],
            ['name' => 'Thailand', 'code' => 'THA', 'flag' => '🇹🇭', 'service_fee' => 2500],
            ['name' => 'India', 'code' => 'IND', 'flag' => '🇮🇳', 'service_fee' => 3500],
        ];

        return Inertia::render('Services/Visa/Index', [
            'visaTypes' => $visaTypes,
            'popularCountries' => $popularCountries,
        ]);
    }

    /**
     * Show visa application form
     */
    public function create(Request $request)
    {
        $visaType = $request->query('type', 'tourist');
        $country = $request->query('country');

        $documentRequirements = $this->getDocumentRequirements($visaType);

        return Inertia::render('Services/Visa/Apply', [
            'visaType' => $visaType,
            'selectedCountry' => $country,
            'documentRequirements' => $documentRequirements,
        ]);
    }

    /**
     * Show universal visa wizard
     */
    public function wizard(Request $request)
    {
        $country = $request->query('country', 'United States');
        $visaType = $request->query('type', 'tourist');

        return Inertia::render('Services/Visa/Wizard', [
            'country' => $country,
            'visaType' => $visaType,
        ]);
    }

    /**
     * Get visa configuration for country + visa type (Universal Wizard API)
     */
    public function getConfig(Request $request)
    {
        $validated = $request->validate([
            'country' => 'required|string|max:100',
            'visa_type' => 'required|in:tourist,business,student,work,medical,transit,family',
        ]);

        // Fetch visa requirements from database
        $requirement = \App\Models\VisaRequirement::where('country', $validated['country'])
            ->where('visa_type', $validated['visa_type'])
            ->where('is_active', true)
            ->first();

        if (! $requirement) {
            return response()->json([
                'error' => 'No visa requirements found for this country and visa type.',
                'config' => null,
            ], 404);
        }

        // Build dynamic wizard configuration
        $config = [
            'country' => $requirement->country,
            'country_code' => $requirement->country_code,
            'visa_type' => $requirement->visa_type,
            'visa_category' => $requirement->visa_category,

            // Wizard metadata
            'estimated_time' => $this->getEstimatedTime($requirement->visa_type),
            'total_steps' => 5,

            // Steps configuration
            'steps' => [
                [
                    'id' => 1,
                    'title' => 'Personal Information',
                    'description' => 'Basic applicant details',
                    'fields' => [
                        ['name' => 'applicant_name', 'label' => 'Full Name (as per passport)', 'type' => 'text', 'required' => true, 'placeholder' => 'John Doe'],
                        ['name' => 'applicant_email', 'label' => 'Email Address', 'type' => 'email', 'required' => true, 'placeholder' => 'john@example.com'],
                        ['name' => 'applicant_phone', 'label' => 'Phone Number', 'type' => 'tel', 'required' => true, 'placeholder' => '+880 1712-345678'],
                        ['name' => 'applicant_dob', 'label' => 'Date of Birth', 'type' => 'date', 'required' => true],
                        ['name' => 'nationality', 'label' => 'Nationality', 'type' => 'text', 'required' => true, 'placeholder' => 'Bangladeshi'],
                        ['name' => 'occupation', 'label' => 'Occupation', 'type' => 'text', 'required' => false, 'placeholder' => 'Software Engineer'],
                    ],
                ],
                [
                    'id' => 2,
                    'title' => 'Passport Details',
                    'description' => 'Passport information',
                    'fields' => [
                        ['name' => 'passport_number', 'label' => 'Passport Number', 'type' => 'text', 'required' => true, 'placeholder' => 'A12345678'],
                        ['name' => 'passport_issue_date', 'label' => 'Issue Date', 'type' => 'date', 'required' => true],
                        ['name' => 'passport_expiry_date', 'label' => 'Expiry Date', 'type' => 'date', 'required' => true],
                        ['name' => 'passport_issuing_country', 'label' => 'Issuing Country', 'type' => 'text', 'required' => true, 'placeholder' => 'Bangladesh'],
                    ],
                ],
                [
                    'id' => 3,
                    'title' => 'Travel Information',
                    'description' => 'Trip details',
                    'fields' => [
                        ['name' => 'intended_travel_date', 'label' => 'Intended Travel Date', 'type' => 'date', 'required' => true],
                        ['name' => 'return_date', 'label' => 'Return Date (if known)', 'type' => 'date', 'required' => false],
                        ['name' => 'duration_days', 'label' => 'Duration (days)', 'type' => 'number', 'required' => false, 'placeholder' => '30', 'min' => 1, 'max' => 365],
                        ['name' => 'travel_purpose', 'label' => 'Purpose of Travel', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Tourism, visiting family...', 'rows' => 3],
                        ['name' => 'visa_category', 'label' => 'Visa Category', 'type' => 'select', 'required' => true, 'options' => [
                            ['value' => 'single_entry', 'label' => 'Single Entry'],
                            ['value' => 'multiple_entry', 'label' => 'Multiple Entry'],
                            ['value' => 'transit', 'label' => 'Transit'],
                        ]],
                    ],
                ],
                [
                    'id' => 4,
                    'title' => 'Processing & Fees',
                    'description' => 'Select processing type',
                    'fields' => [
                        ['name' => 'processing_type', 'label' => 'Processing Speed', 'type' => 'radio', 'required' => true, 'options' => [
                            ['value' => 'standard', 'label' => 'Standard', 'description' => ($requirement->processing_days_standard ?? 15).' days', 'price' => $requirement->processing_fee_standard ?? 0],
                            ['value' => 'express', 'label' => 'Express', 'description' => ($requirement->processing_days_express ?? 7).' days', 'price' => $requirement->processing_fee_express ?? 0],
                            ['value' => 'urgent', 'label' => 'Urgent', 'description' => ($requirement->processing_days_urgent ?? 3).' days', 'price' => $requirement->processing_fee_urgent ?? 0],
                        ]],
                    ],
                ],
                [
                    'id' => 5,
                    'title' => 'Review & Submit',
                    'description' => 'Confirm details before submission',
                    'is_review' => true,
                ],
            ],

            // Requirements & info
            'requirements' => [
                'documents' => $requirement->required_documents ?? [],
                'financial' => [
                    'min_bank_balance' => $requirement->min_bank_balance,
                    'bank_statement_months' => $requirement->bank_statement_months,
                ],
                'interview_required' => $requirement->interview_required,
                'biometrics_required' => $requirement->biometrics_required,
            ],

            // Fees
            'fees' => [
                'government_fee' => (float) $requirement->government_fee,
                'service_fee' => (float) $requirement->service_fee,
                'currency' => $requirement->currency ?? 'BDT',
            ],

            // Processing information
            'processing' => [
                'standard_days' => $requirement->processing_days_standard ?? 15,
                'express_days' => $requirement->processing_days_express ?? 7,
                'urgent_days' => $requirement->processing_days_urgent ?? 3,
            ],

            // Help text
            'help_text' => $requirement->general_requirements,
            'important_notes' => $requirement->important_notes,
            'tips' => $requirement->tips_for_applicants ?? [],
        ];

        // Add country-specific conditional fields
        $config['steps'] = $this->addCountrySpecificFields($config['steps'], $validated['country'], $validated['visa_type']);

        return response()->json(['config' => $config]);
    }

    /**
     * Add country-specific fields to wizard steps
     */
    private function addCountrySpecificFields(array $steps, string $country, string $visaType): array
    {
        // USA-specific fields
        if (strtoupper($country) === 'UNITED STATES' || $country === 'USA') {
            // Add DS-160 equivalent fields to step 1
            $steps[0]['fields'][] = ['name' => 'us_ssn', 'label' => 'U.S. Social Security Number (if any)', 'type' => 'text', 'required' => false, 'placeholder' => 'XXX-XX-XXXX'];
            $steps[2]['fields'][] = ['name' => 'us_address', 'label' => 'U.S. Address (if known)', 'type' => 'textarea', 'required' => false, 'rows' => 2];
        }

        // UK-specific fields
        if (strtoupper($country) === 'UNITED KINGDOM' || $country === 'UK') {
            // Add NHS surcharge info
            $steps[3]['fields'][] = ['name' => 'nhs_surcharge_acknowledged', 'label' => 'I understand I may need to pay NHS surcharge', 'type' => 'checkbox', 'required' => true];
        }

        // Canada-specific fields
        if (strtoupper($country) === 'CANADA' || $country === 'CAN') {
            // Add biometrics acknowledgment
            $steps[3]['fields'][] = ['name' => 'biometrics_acknowledged', 'label' => 'I understand biometrics are required', 'type' => 'checkbox', 'required' => true];
        }

        // Student visa specific fields (all countries)
        if ($visaType === 'student') {
            $steps[2]['fields'][] = ['name' => 'university_name', 'label' => 'University/Institution Name', 'type' => 'text', 'required' => true];
            $steps[2]['fields'][] = ['name' => 'course_name', 'label' => 'Course/Program', 'type' => 'text', 'required' => true];
            $steps[2]['fields'][] = ['name' => 'admission_letter', 'label' => 'Have admission letter?', 'type' => 'checkbox', 'required' => false];
        }

        // Work visa specific fields
        if ($visaType === 'work') {
            $steps[2]['fields'][] = ['name' => 'employer_name', 'label' => 'Employer Name', 'type' => 'text', 'required' => true];
            $steps[2]['fields'][] = ['name' => 'job_title', 'label' => 'Job Title', 'type' => 'text', 'required' => true];
            $steps[2]['fields'][] = ['name' => 'job_offer_letter', 'label' => 'Have job offer letter?', 'type' => 'checkbox', 'required' => false];
        }

        return $steps;
    }

    /**
     * Get estimated completion time for visa type
     */
    private function getEstimatedTime(string $visaType): string
    {
        return match ($visaType) {
            'tourist' => '20-30 minutes',
            'business' => '25-35 minutes',
            'student' => '40-50 minutes',
            'work' => '45-60 minutes',
            'medical' => '30-40 minutes',
            'transit' => '15-20 minutes',
            'family' => '35-45 minutes',
            default => '30 minutes',
        };
    }

    /**
     * Store new visa application
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'visa_type' => 'required|in:tourist,business,student,work,medical,transit',
            'destination_country' => 'required|string|max:100',
            'destination_country_code' => 'required|string|max:10',
            'visa_category' => 'required|in:single_entry,multiple_entry,transit',
            'duration_days' => 'nullable|integer|min:1|max:365',
            'applicant_name' => 'required|string|max:255',
            'applicant_email' => 'required|email|max:255',
            'applicant_phone' => 'required|string|max:20',
            'applicant_dob' => 'required|date|before:today',
            'passport_number' => 'required|string|max:50',
            'passport_issue_date' => 'required|date',
            'passport_expiry_date' => 'required|date|after:passport_issue_date',
            'passport_issuing_country' => 'required|string|max:100',
            'nationality' => 'required|string|max:100',
            'intended_travel_date' => 'required|date|after:today',
            'return_date' => 'nullable|date|after:intended_travel_date',
            'travel_purpose' => 'required|string|max:500',
            'occupation' => 'nullable|string|max:100',
            'employer_name' => 'nullable|string|max:255',
            'previous_visa_rejected' => 'nullable|boolean',
            'processing_type' => 'required|in:standard,express,urgent',
            'service_fee' => 'required|numeric|min:0',
            'government_fee' => 'required|numeric|min:0',
            'processing_fee' => 'required|numeric|min:0',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'draft';
        $validated['payment_status'] = 'pending';
        $validated['total_amount'] = $validated['service_fee'] + $validated['government_fee'] + $validated['processing_fee'];
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();

        // Set processing days based on type
        $validated['processing_days'] = match ($validated['processing_type']) {
            'standard' => 15,
            'express' => 7,
            'urgent' => 3,
        };

        $application = VisaApplication::create($validated);

        // 🔥 PLUGIN SYSTEM: Create ServiceApplication for agency assignment
        // Route different visa types to their respective services
        $serviceSlug = match ($validated['visa_type']) {
            'work' => 'work-visa',
            'student' => 'student-visa',
            'business' => 'business-visa',
            'medical' => 'medical-visa',
            'transit' => 'transit-visa',
            default => null, // Tourist and other types handled separately
        };

        // Special handling for family visa type (if it exists as a field)
        if (isset($validated['family_visa']) && $validated['family_visa']) {
            $serviceSlug = 'family-visa';
        }

        if ($serviceSlug) {
            $this->createServiceApplicationFor(
                $application,
                $serviceSlug,
                [
                    'visa_type' => $validated['visa_type'],
                    'destination_country' => $validated['destination_country'],
                    'destination_country_code' => $validated['destination_country_code'],
                    'applicant_name' => $validated['applicant_name'],
                    'intended_travel_date' => $validated['intended_travel_date'],
                    'processing_type' => $validated['processing_type'],
                    'total_amount' => $validated['total_amount'],
                ]
            );
        }

        return redirect()->route('visa.show', $application)
            ->with('success', 'Visa application created successfully. Please upload required documents.');
    }

    /**
     * Display user's applications
     */
    public function myApplications()
    {
        $applications = VisaApplication::forUser(auth()->id())
            ->with(['documents', 'appointments'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Services/Visa/MyApplications', [
            'applications' => $applications,
        ]);
    }

    /**
     * Show specific application
     */
    public function show(VisaApplication $application)
    {
        $this->authorize('view', $application);

        $application->load(['documents', 'appointments', 'user']);

        return Inertia::render('Services/Visa/ShowApplication', [
            'application' => $application,
        ]);
    }

    /**
     * Upload document for application
     */
    public function uploadDocument(Request $request, VisaApplication $application)
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'document_type' => 'required|in:passport,photo,bank_statement,invitation_letter,travel_itinerary,accommodation_proof,employment_letter,education_certificate,medical_reports,police_clearance,sponsor_letter,other',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'document_number' => 'nullable|string|max:100',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'notes' => 'nullable|string|max:500',
        ]);

        // Upload file
        $file = $request->file('file');
        $filename = time().'_'.$file->getClientOriginalName();
        $path = $file->storeAs('visa-documents/'.$application->id, $filename, 'public');

        $validated['visa_application_id'] = $application->id;
        $validated['file_path'] = $path;
        $validated['file_name'] = $file->getClientOriginalName();
        $validated['file_size'] = $file->getSize();
        $validated['mime_type'] = $file->getMimeType();
        $validated['status'] = 'pending';
        $validated['uploaded_by'] = auth()->id();

        $document = VisaDocument::create($validated);

        return back()->with('success', 'Document uploaded successfully.');
    }

    /**
     * Show payment page
     */
    public function payment(VisaApplication $application)
    {
        $this->authorize('view', $application);

        if ($application->isPaid()) {
            return redirect()->route('visa.show', $application)
                ->with('info', 'This application has already been paid for.');
        }

        return Inertia::render('Services/Visa/Payment', [
            'application' => $application,
        ]);
    }

    /**
     * Process payment
     */
    public function processPayment(Request $request, VisaApplication $application)
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'payment_method' => 'required|in:bkash,nagad,rocket,bank_transfer,card',
            'transaction_id' => 'required|string|max:100',
        ]);

        $application->update([
            'payment_method' => $validated['payment_method'],
            'payment_transaction_id' => $validated['transaction_id'],
            'payment_status' => 'paid',
            'paid_at' => now(),
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()->route('visa.show', $application)
            ->with('success', 'Payment successful! Your visa application has been submitted.');
    }

    /**
     * Cancel application
     */
    public function cancel(VisaApplication $application)
    {
        $this->authorize('delete', $application);

        if ($application->isApproved() || $application->isRejected()) {
            return back()->with('error', 'Cannot cancel an application that has already been processed.');
        }

        $application->cancel();

        return back()->with('success', 'Application cancelled successfully.');
    }

    /**
     * Get document requirements based on visa type
     */
    private function getDocumentRequirements(string $visaType): array
    {
        $common = ['passport', 'photo', 'bank_statement', 'travel_itinerary', 'accommodation_proof'];

        $specific = match ($visaType) {
            'business' => ['invitation_letter', 'employment_letter'],
            'student' => ['education_certificate', 'admission_letter'],
            'work' => ['employment_letter', 'work_permit'],
            'medical' => ['medical_reports', 'hospital_invitation'],
            default => [],
        };

        return array_merge($common, $specific);
    }
}
