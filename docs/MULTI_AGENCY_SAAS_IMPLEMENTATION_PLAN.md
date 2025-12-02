# Multi-Agency SaaS Platform - Complete Implementation Plan

**Project:** BideshGomon - Bangladesh Migration & Travel Platform  
**Date:** 02 Dec 2025  
**Architecture:** Multi-Agency SaaS with Role-Based Dashboards

---

## 🎯 Project Vision

A comprehensive SaaS platform where **agencies (companies) operate as independent businesses**, offering specialized travel and migration services to Bangladeshi users. Each user role receives a unique, purpose-built dashboard interface.

---

## 🏢 Core Principles

### 1. Agency = Company (NOT Individual)
- Agencies are **business entities**, not personal accounts
- Each agency has:
  - Company registration
  - License numbers
  - Team members (consultants)
  - Service portfolios
  - Country expertise

### 2. Role-Based Dashboard Architecture
Every user role gets a dedicated, custom-designed dashboard:

| Role | Dashboard Focus | Primary Actions |
|------|----------------|-----------------|
| **User** | Application tracking, service discovery | Submit applications, track status, pay fees |
| **Agency** | Application management, quote submission | Process applications, submit quotes, manage team |
| **Consultant** | Case management, document verification | Assist clients, verify documents, communicate |
| **Admin** | Platform oversight, agency approval | Manage agencies, assign permissions, monitor revenue |

### 3. Query-Based Service Model
- Most services require comprehensive form submission
- Forms auto-populate from user profile data
- Users can request quotes from multiple agencies
- Agencies compete by submitting quotes
- Some services include delivery charges (attestation, notary)

---

## 🌍 Service Categories & Delivery Models

### Travel Services
| Service | Type | Agency Model | Unique Features |
|---------|------|--------------|-----------------|
| **Air Tickets** | Global Single | One platform-managed agency | High volume, low margin (8% commission) |
| **Tourist Visas** | Competitive | Multiple agencies per country | Users compare quotes, select best agency |
| **Study Abroad** | Exclusive + Competitive | University-exclusive + country-level | One agency per university, but multiple per country |
| **Tour Packages** | Readymade + Custom | Agency catalog + custom quotes | Pre-built packages + tailored itineraries |
| **Hajj & Umrah** | Licensed Specialized | Govt-approved agencies only | Ministry of Religious Affairs licensing required |
| **Travel Insurance** | Marketplace | Multiple providers | Quote comparison, instant purchase |

### Migration Services
| Service | Type | Agency Model | Special Requirements |
|---------|------|--------------|---------------------|
| **Work Visa** | Competitive | Multiple agencies per country | BMET/RL license for recruiting agencies |
| **Student Visa** | Competitive | Education consultancies | Country-specific expertise |
| **Family Visa** | Competitive | General migration agencies | Document-heavy, profile completion required |
| **Permanent Residence** | Specialized | Licensed immigration consultants | Long processing times, high fees |

### Document Services
| Service | Type | Agency Model | Delivery |
|---------|------|--------------|----------|
| **Attestation** | Query-based | Multiple providers | Home delivery + charges |
| **Notary** | Query-based | Legal service providers | Home delivery + charges |
| **Translation** | Query-based | Translation agencies | Digital/physical delivery |
| **GAMCA/Medical** | Appointment | Medical center network | Physical visit required |

### Employment Services
| Service | Type | Agency Model | Agency Type |
|---------|------|--------------|-------------|
| **Job Posting** | Recruiting Agency Managed | **Recruiting agencies ONLY** | BMET-licensed manpower recruiters |
| **Job Application** | User-initiated | Recruiting agencies process | CV required, profile-based matching |
| **CV Building** | Platform Direct | No agency (100% platform revenue) | Premium feature, instant generation |

---

## 📊 Agency Type System (Existing)

**Status:** ✅ Already implemented (22 agency types)

Key agency types relevant to your requirements:

### 1. Recruiting Agency (Work & Jobs)
```php
'allowed_service_modules' => [3, 18, 19, 20, 21, 22], // Work Visa + Job services
'commission_rate' => 15.00,
'certifications_required' => ['BMET License', 'RL License'],
'can_post_jobs' => true, // 🔑 ONLY recruiting agencies can post jobs
```

### 2. Student Consultancy (Education Services)
```php
'allowed_service_modules' => [2, 14, 15, 16, 17], // Student Visa + Education
'commission_rate' => 18.00,
'can_manage_universities' => true, // Exclusive university assignments
```

### 3. Travel Agency (Tourist Services)
```php
'allowed_service_modules' => [1, 4, 5, 6, 7, 8, 9, 11], // Tourist Visa, Flights, Hotels, Tours
'commission_rate' => 15.00,
```

### 4. Hajj-Umrah Agency (Religious Travel)
```php
'allowed_service_modules' => [10], // Hajj-Umrah packages only
'commission_rate' => 12.00,
'certifications_required' => ['Religious Affairs License'],
'country_restrictions' => ['Saudi Arabia'],
```

### 5. Translation & Attestation Services
```php
'allowed_service_modules' => [23, 24, 25], // Translation, Attestation, Notary
'commission_rate' => 20.00,
'requires_delivery_handling' => true, // ⚠️ Delivery charges apply
```

---

## 🔧 Technical Architecture

### Database Structure (Existing & Required)

#### ✅ Existing Tables
```sql
-- Core Agency System
agencies (id, user_id, name, company_name, registration_number, agency_type_id, ...)
agency_types (id, name, allowed_service_modules, commission_rate, ...)
agency_country_assignments (id, agency_id, country_id, service_module_id, commission_rate, ...)
agency_team_members (id, agency_id, user_id, role, permissions, ...)

-- Service System
service_modules (id, name, slug, service_type, base_price, ...)
service_applications (id, user_id, service_module_id, agency_id, status, ...)
service_quotes (id, service_application_id, agency_id, quoted_amount, ...)

-- User Profiles (9 tables)
user_profiles, user_passports, user_educations, user_work_experiences, 
user_financial_information, user_family_members, user_travel_history, 
user_visa_history, user_languages

-- Wallet System
wallets (id, user_id, balance, ...)
wallet_transactions (id, wallet_id, type, amount, reference_type, ...)

-- Specialized Services
tourist_visas (id, user_id, country_id, visa_type_id, status, ...)
work_visas (id, user_id, ...)
universities (id, name, country_id, primary_agency_id, ...) // Exclusive model
```

#### ⚠️ Tables Needing Enhancement

**1. service_modules Table**
Add columns:
```sql
-- Identify service delivery characteristics
requires_form_submission BOOLEAN DEFAULT TRUE,
has_delivery_charges BOOLEAN DEFAULT FALSE,
default_delivery_charge DECIMAL(10,2) DEFAULT 0.00,
is_query_based BOOLEAN DEFAULT TRUE,
is_marketplace BOOLEAN DEFAULT FALSE,

-- University exclusivity
supports_university_exclusivity BOOLEAN DEFAULT FALSE,

-- Job posting restrictions
restricted_to_agency_type_id INT NULL, // Only specific agency types can offer
```

**2. service_applications Table**
Add columns:
```sql
-- Form data from user submission
form_submission_data JSON,
profile_completion_percentage INT,

-- Delivery handling
requires_delivery BOOLEAN DEFAULT FALSE,
delivery_address TEXT,
delivery_charge DECIMAL(10,2) DEFAULT 0.00,

-- Quote comparison (multi-agency)
quote_count INT DEFAULT 0,
selected_quote_id INT NULL,
quote_deadline DATETIME,
```

**3. agencies Table**
Add columns:
```sql
-- Job posting capability
can_post_jobs BOOLEAN DEFAULT FALSE,
active_job_postings_count INT DEFAULT 0,
max_job_postings INT DEFAULT 10,

-- University exclusivity
exclusive_universities JSON, // Array of university IDs
```

#### 🆕 New Tables Required

**1. agency_service_delivery_zones**
```sql
CREATE TABLE agency_service_delivery_zones (
    id BIGINT PRIMARY KEY,
    agency_id BIGINT, -- FK to agencies
    service_module_id BIGINT, -- FK to service_modules
    city_id BIGINT NULL, -- FK to cities (for local delivery)
    country_id BIGINT NULL, -- FK to countries (for international)
    delivery_charge DECIMAL(10,2) DEFAULT 0.00,
    delivery_time_days INT DEFAULT 3,
    is_active BOOLEAN DEFAULT TRUE,
    UNIQUE(agency_id, service_module_id, city_id, country_id)
);
```

**2. university_agency_assignments** (Exclusive Model)
```sql
CREATE TABLE university_agency_assignments (
    id BIGINT PRIMARY KEY,
    university_id BIGINT, -- FK to universities
    primary_agency_id BIGINT, -- FK to agencies (70% earnings)
    secondary_agency_id BIGINT NULL, -- FK to agencies (10% referral)
    country_id BIGINT, -- FK to countries
    commission_split_primary DECIMAL(5,2) DEFAULT 70.00,
    commission_split_secondary DECIMAL(5,2) DEFAULT 10.00,
    platform_commission DECIMAL(5,2) DEFAULT 20.00,
    assigned_at DATETIME,
    is_exclusive BOOLEAN DEFAULT TRUE,
    UNIQUE(university_id) -- One university = one primary agency
);
```

**3. job_postings** (Recruiting Agency Only)
```sql
CREATE TABLE job_postings (
    id BIGINT PRIMARY KEY,
    agency_id BIGINT, -- FK to agencies (must have can_post_jobs = TRUE)
    country_id BIGINT,
    job_title VARCHAR(255),
    job_description TEXT,
    salary_range VARCHAR(100),
    requirements JSON,
    application_deadline DATE,
    status ENUM('active', 'closed', 'expired'),
    applications_count INT DEFAULT 0,
    agency_service_fee DECIMAL(10,2),
    platform_commission_rate DECIMAL(5,2) DEFAULT 5.00,
    created_at DATETIME,
    CONSTRAINT check_agency_can_post FOREIGN KEY (agency_id) 
        REFERENCES agencies(id) WHERE can_post_jobs = TRUE
);
```

---

## 🚀 Implementation Phases

### Phase 1: Service Module Enhancement (Week 1)
**Goal:** Configure service delivery models

#### Tasks:
1. **Update service_modules table**
   ```php
   // Migration
   php artisan make:migration add_service_delivery_fields_to_service_modules
   
   // Add columns: requires_form_submission, has_delivery_charges, 
   // is_query_based, supports_university_exclusivity, restricted_to_agency_type_id
   ```

2. **Seed service configurations**
   ```php
   // database/seeders/ServiceModuleEnhancementSeeder.php
   
   // Air Tickets
   ServiceModule::where('slug', 'air-tickets')->update([
       'is_marketplace' => false,
       'is_query_based' => true,
       'restricted_to_agency_type_id' => null, // Global single agency
   ]);
   
   // Tourist Visa
   ServiceModule::where('slug', 'tourist-visa')->update([
       'is_marketplace' => true, // Multiple agencies compete
       'is_query_based' => true,
       'requires_form_submission' => true,
   ]);
   
   // Attestation Services
   ServiceModule::where('slug', 'attestation')->update([
       'is_query_based' => true,
       'has_delivery_charges' => true,
       'default_delivery_charge' => 200.00, // ৳200 delivery
   ]);
   
   // Job Posting
   ServiceModule::where('slug', 'job-posting')->update([
       'restricted_to_agency_type_id' => 1, // Only Recruiting Agency (ID: 1)
   ]);
   
   // University Application
   ServiceModule::where('slug', 'university-application')->update([
       'supports_university_exclusivity' => true,
   ]);
   ```

3. **Update ServiceModule model**
   ```php
   // app/Models/ServiceModule.php
   
   public function isRestrictedToAgencyType(): bool
   {
       return !is_null($this->restricted_to_agency_type_id);
   }
   
   public function canAgencyOffer(Agency $agency): bool
   {
       if ($this->isRestrictedToAgencyType()) {
           return $agency->agency_type_id === $this->restricted_to_agency_type_id;
       }
       
       $allowedModules = $agency->agencyType->allowed_service_modules ?? [];
       return in_array($this->id, $allowedModules);
   }
   
   public function supportsMultipleAgencies(): bool
   {
       return $this->is_marketplace === true;
   }
   ```

**Testing Phase 1:**
- ✅ Service modules correctly identify delivery requirements
- ✅ Restricted services (job posting) only accessible by recruiting agencies
- ✅ University exclusivity flag set correctly

---

### Phase 2: Agency Dashboard (Week 2)
**Goal:** Build agency-specific dashboard with application management

#### Tasks:

1. **Create Agency Dashboard Controller**
   ```php
   // app/Http/Controllers/Agency/DashboardController.php
   
   public function index(Request $request)
   {
       $agency = $request->user()->agency;
       
       return Inertia::render('Agency/Dashboard', [
           'stats' => [
               'pending_applications' => $agency->applications()->where('status', 'pending')->count(),
               'active_applications' => $agency->applications()->whereIn('status', ['in_progress', 'quoted'])->count(),
               'completed_this_month' => $agency->applications()->where('status', 'completed')
                   ->whereMonth('completed_at', now()->month)->count(),
               'total_earnings' => $agency->walletTransactions()->where('type', 'credit')->sum('amount'),
               'pending_quotes' => $agency->quotes()->where('status', 'pending')->count(),
           ],
           'recent_applications' => $agency->applications()->with(['user', 'serviceModule', 'quotes'])
               ->latest()->limit(10)->get(),
           'service_modules' => $this->getAgencyServices($agency),
           'country_assignments' => $agency->countryAssignments()->with('country', 'serviceModule')->get(),
       ]);
   }
   
   private function getAgencyServices(Agency $agency): Collection
   {
       $allowedModules = $agency->agencyType->allowed_service_modules ?? [];
       
       return ServiceModule::whereIn('id', $allowedModules)
           ->where('is_active', true)
           ->withCount(['applications' => function($q) use ($agency) {
               $q->where('agency_id', $agency->id);
           }])
           ->get();
   }
   ```

2. **Create Agency Application Controller**
   ```php
   // app/Http/Controllers/Agency/ApplicationController.php
   
   public function index(Request $request)
   {
       $agency = $request->user()->agency;
       
       $applications = ServiceApplication::where('agency_id', $agency->id)
           ->with(['user', 'serviceModule', 'quotes'])
           ->when($request->status, function($q, $status) {
               $q->where('status', $status);
           })
           ->when($request->service, function($q, $service) {
               $q->where('service_module_id', $service);
           })
           ->latest()
           ->paginate(20);
       
       return Inertia::render('Agency/Applications/Index', [
           'applications' => $applications,
           'filters' => $request->only(['status', 'service']),
       ]);
   }
   
   public function show(Request $request, ServiceApplication $application)
   {
       // Ensure agency owns this application
       if ($application->agency_id !== $request->user()->agency->id) {
           abort(403);
       }
       
       $application->load([
           'user.profile',
           'user.passports',
           'user.educations',
           'serviceModule',
           'documents',
           'statusHistory',
           'quotes' => function($q) use ($request) {
               $q->where('agency_id', $request->user()->agency->id);
           }
       ]);
       
       return Inertia::render('Agency/Applications/Show', [
           'application' => $application,
           'user_profile_snapshot' => $application->profile_snapshot,
       ]);
   }
   ```

3. **Create Agency Quote Controller**
   ```php
   // app/Http/Controllers/Agency/QuoteController.php
   
   public function store(Request $request, ServiceApplication $application)
   {
       $validated = $request->validate([
           'quoted_amount' => 'required|numeric|min:0',
           'processing_time_days' => 'required|integer|min:1',
           'special_notes' => 'nullable|string|max:500',
       ]);
       
       $agency = $request->user()->agency;
       $commissionRate = $agency->commission_rate ?? 15.00;
       
       $platformCommission = ($validated['quoted_amount'] * $commissionRate) / 100;
       $agencyEarnings = $validated['quoted_amount'] - $platformCommission;
       
       $quote = ServiceQuote::create([
           'service_application_id' => $application->id,
           'agency_id' => $agency->id,
           'quoted_amount' => $validated['quoted_amount'],
           'platform_commission' => $platformCommission,
           'agency_earnings' => $agencyEarnings,
           'processing_time_days' => $validated['processing_time_days'],
           'special_notes' => $validated['special_notes'],
           'status' => 'pending',
           'expires_at' => now()->addHours(48),
       ]);
       
       // Update application
       $application->increment('quote_count');
       $application->update(['status' => 'quoted']);
       
       return redirect()->route('agency.applications.show', $application)
           ->with('success', 'Quote submitted successfully');
   }
   ```

4. **Create Dashboard Vue Component**
   ```vue
   <!-- resources/js/Pages/Agency/Dashboard.vue -->
   <script setup>
   import { Head } from '@inertiajs/vue3';
   import AgencyLayout from '@/Layouts/AgencyLayout.vue';
   import { useBangladeshFormat } from '@/Composables/useBangladeshFormat';
   
   const props = defineProps({
       stats: Object,
       recent_applications: Array,
       service_modules: Array,
       country_assignments: Array,
   });
   
   const { formatCurrency, formatDate } = useBangladeshFormat();
   </script>
   
   <template>
       <Head title="Agency Dashboard" />
       
       <AgencyLayout>
           <!-- Stats Cards -->
           <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
               <div class="card">
                   <h3 class="text-sm font-medium text-gray-500">Pending Applications</h3>
                   <p class="text-3xl font-bold text-indigo-600">{{ stats.pending_applications }}</p>
               </div>
               <!-- More stat cards... -->
           </div>
           
           <!-- Recent Applications Table -->
           <div class="card">
               <h2 class="text-xl font-semibold mb-4">Recent Applications</h2>
               <table class="table-fix">
                   <thead>
                       <tr>
                           <th>Application #</th>
                           <th>Service</th>
                           <th>User</th>
                           <th>Status</th>
                           <th>Date</th>
                           <th>Actions</th>
                       </tr>
                   </thead>
                   <tbody>
                       <tr v-for="app in recent_applications" :key="app.id">
                           <td>{{ app.application_number }}</td>
                           <td>{{ app.service_module.name }}</td>
                           <td>{{ app.user.name }}</td>
                           <td>
                               <span :class="getBadgeClass(app.status)">
                                   {{ app.status }}
                               </span>
                           </td>
                           <td>{{ formatDate(app.created_at) }}</td>
                           <td>
                               <Link :href="route('agency.applications.show', app.id)" class="btn-primary btn-sm">
                                   View
                               </Link>
                           </td>
                       </tr>
                   </tbody>
               </table>
           </div>
       </AgencyLayout>
   </template>
   ```

**Testing Phase 2:**
- ✅ Agency can view their dashboard with correct stats
- ✅ Applications filtered by agency ownership
- ✅ Quote submission calculates commission correctly
- ✅ Only assigned services visible to agency

---

### Phase 3: User Application Flow (Week 3)
**Goal:** Query-based form submission with profile pre-population

#### Tasks:

1. **Create Service Application Form Controller**
   ```php
   // app/Http/Controllers/User/ServiceApplicationController.php
   
   public function create(Request $request, ServiceModule $serviceModule)
   {
       $user = $request->user();
       
       // Check profile completion
       $profileCompletion = $this->calculateProfileCompletion($user);
       if ($profileCompletion < $serviceModule->min_profile_completion) {
           return redirect()->route('profile.index')
               ->with('warning', "Complete your profile ({$serviceModule->min_profile_completion}% required) to apply for {$serviceModule->name}");
       }
       
       // Get profile snapshot for form pre-population
       $profileSnapshot = [
           'personal' => $user->profile,
           'passports' => $user->passports,
           'educations' => $user->educations,
           'work_experiences' => $user->workExperiences,
           'family_members' => $user->familyMembers,
           'financial_info' => $user->financialInformation,
       ];
       
       return Inertia::render('User/Applications/Create', [
           'service_module' => $serviceModule,
           'profile_snapshot' => $profileSnapshot,
           'profile_completion' => $profileCompletion,
           'form_fields' => $serviceModule->activeFormFields,
       ]);
   }
   
   public function store(Request $request, ServiceModule $serviceModule)
   {
       $validated = $request->validate([
           'form_data' => 'required|array',
           'requires_delivery' => 'boolean',
           'delivery_address' => 'required_if:requires_delivery,true',
       ]);
       
       $user = $request->user();
       
       // Calculate delivery charge
       $deliveryCharge = 0;
       if ($serviceModule->has_delivery_charges && $validated['requires_delivery']) {
           $deliveryCharge = $this->calculateDeliveryCharge(
               $serviceModule, 
               $user->profile->city_id
           );
       }
       
       $application = ServiceApplication::create([
           'user_id' => $user->id,
           'service_module_id' => $serviceModule->id,
           'form_submission_data' => $validated['form_data'],
           'profile_snapshot' => $this->captureProfileSnapshot($user),
           'profile_completion_percentage' => $this->calculateProfileCompletion($user),
           'requires_delivery' => $validated['requires_delivery'] ?? false,
           'delivery_address' => $validated['delivery_address'] ?? null,
           'delivery_charge' => $deliveryCharge,
           'status' => 'pending',
           'submitted_at' => now(),
       ]);
       
       // Assign to agencies based on service model
       if ($serviceModule->supportsMultipleAgencies()) {
           $this->notifyEligibleAgencies($application);
           $application->update(['quote_deadline' => now()->addHours(48)]);
       } else {
           $this->assignToSingleAgency($application);
       }
       
       return redirect()->route('user.applications.show', $application)
           ->with('success', 'Application submitted successfully');
   }
   
   private function notifyEligibleAgencies(ServiceApplication $application): void
   {
       // Find agencies with country + service assignment
       $eligibleAgencies = AgencyCountryAssignment::where('service_module_id', $application->service_module_id)
           ->where('country_id', $application->form_submission_data['country_id'])
           ->where('is_active', true)
           ->with('agency')
           ->get();
       
       foreach ($eligibleAgencies as $assignment) {
           // Send notification to agency
           $assignment->agency->user->notify(new NewApplicationAvailable($application));
       }
   }
   ```

2. **Create Dynamic Form Component**
   ```vue
   <!-- resources/js/Components/ServiceApplicationForm.vue -->
   <script setup>
   import { useForm } from '@inertiajs/vue3';
   import BangladeshDateInput from '@/Components/BangladeshDateInput.vue';
   
   const props = defineProps({
       serviceModule: Object,
       profileSnapshot: Object,
       formFields: Array,
   });
   
   const form = useForm({
       form_data: {
           // Pre-populate from profile
           full_name: props.profileSnapshot.personal?.full_name || '',
           passport_number: props.profileSnapshot.passports[0]?.passport_number || '',
           date_of_birth: props.profileSnapshot.personal?.date_of_birth || '',
           // Service-specific fields will be added dynamically
       },
       requires_delivery: false,
       delivery_address: '',
   });
   
   const getInputComponent = (fieldType) => {
       const components = {
           'date': BangladeshDateInput,
           'text': 'input',
           'select': 'select',
           'textarea': 'textarea',
       };
       return components[fieldType] || 'input';
   };
   </script>
   
   <template>
       <form @submit.prevent="form.post(route('user.applications.store', serviceModule.id))">
           <!-- Dynamic Form Fields -->
           <div v-for="field in formFields" :key="field.id" class="form-row-fix">
               <label :for="field.name" class="label">
                   {{ field.label }}
                   <span v-if="field.is_required" class="text-red-500">*</span>
               </label>
               
               <component
                   :is="getInputComponent(field.field_type)"
                   :id="field.name"
                   v-model="form.form_data[field.name]"
                   :placeholder="field.placeholder"
                   :required="field.is_required"
                   class="input"
               />
               
               <p v-if="field.help_text" class="help-text">{{ field.help_text }}</p>
               <p v-if="form.errors[`form_data.${field.name}`]" class="error-text">
                   {{ form.errors[`form_data.${field.name}`] }}
               </p>
           </div>
           
           <!-- Delivery Options -->
           <div v-if="serviceModule.has_delivery_charges" class="form-row-fix">
               <label class="flex items-center gap-2">
                   <input type="checkbox" v-model="form.requires_delivery" class="rounded" />
                   <span>Require home delivery (৳{{ serviceModule.default_delivery_charge }})</span>
               </label>
               
               <textarea
                   v-if="form.requires_delivery"
                   v-model="form.delivery_address"
                   placeholder="Enter your delivery address"
                   class="input mt-2"
                   required
               ></textarea>
           </div>
           
           <div class="flex justify-end gap-3 mt-6">
               <button type="button" @click="$inertia.visit(route('services.index'))" class="btn-outline">
                   Cancel
               </button>
               <button type="submit" :disabled="form.processing" class="btn-primary">
                   {{ form.processing ? 'Submitting...' : 'Submit Application' }}
               </button>
           </div>
       </form>
   </template>
   ```

**Testing Phase 3:**
- ✅ Forms pre-populate from user profile
- ✅ Profile completion check enforced
- ✅ Delivery charges calculated correctly
- ✅ Eligible agencies notified for competitive services
- ✅ Bangladesh date format used throughout

---

### Phase 4: University Exclusivity System (Week 4)
**Goal:** Implement exclusive university-agency assignments

#### Tasks:

1. **Create University Agency Assignment Migration**
   ```php
   // database/migrations/YYYY_MM_DD_create_university_agency_assignments.php
   
   Schema::create('university_agency_assignments', function (Blueprint $table) {
       $table->id();
       $table->foreignId('university_id')->constrained()->onDelete('cascade');
       $table->foreignId('primary_agency_id')->constrained('agencies')->onDelete('cascade');
       $table->foreignId('secondary_agency_id')->nullable()->constrained('agencies')->onDelete('set null');
       $table->foreignId('country_id')->constrained();
       $table->decimal('commission_split_primary', 5, 2)->default(70.00);
       $table->decimal('commission_split_secondary', 5, 2)->default(10.00);
       $table->decimal('platform_commission', 5, 2)->default(20.00);
       $table->timestamp('assigned_at');
       $table->boolean('is_exclusive')->default(true);
       $table->timestamps();
       
       $table->unique('university_id'); // One university = one primary agency
   });
   ```

2. **Update University Model**
   ```php
   // app/Models/University.php
   
   public function primaryAgency(): BelongsTo
   {
       return $this->belongsTo(Agency::class, 'primary_agency_id');
   }
   
   public function exclusiveAssignment(): HasOne
   {
       return $this->hasOne(UniversityAgencyAssignment::class);
   }
   
   public function isExclusive(): bool
   {
       return $this->exclusiveAssignment?->is_exclusive ?? false;
   }
   
   public function canBeHandledBy(Agency $agency): bool
   {
       if (!$this->isExclusive()) {
           // Open to all education consultancies
           return $agency->agencyType->name === 'Student Consultancy';
       }
       
       // Check if agency is primary or secondary
       $assignment = $this->exclusiveAssignment;
       return $assignment->primary_agency_id === $agency->id 
           || $assignment->secondary_agency_id === $agency->id;
   }
   ```

3. **Create University Application Flow**
   ```php
   // app/Http/Controllers/User/UniversityApplicationController.php
   
   public function create(Request $request, University $university)
   {
       $user = $request->user();
       
       // Check if university is exclusive
       if ($university->isExclusive()) {
           $assignment = $university->exclusiveAssignment;
           
           return Inertia::render('User/Applications/UniversityExclusive', [
               'university' => $university,
               'primary_agency' => $assignment->primaryAgency,
               'secondary_agency' => $assignment->secondaryAgency,
               'message' => "This university is exclusively handled by {$assignment->primaryAgency->name}",
           ]);
       }
       
       // Non-exclusive: user can choose agency
       $eligibleAgencies = Agency::whereHas('countryAssignments', function($q) use ($university) {
           $q->where('country_id', $university->country_id)
             ->where('service_module_id', 15); // University Application service
       })->get();
       
       return Inertia::render('User/Applications/UniversitySelect', [
           'university' => $university,
           'eligible_agencies' => $eligibleAgencies,
       ]);
   }
   
   public function store(Request $request, University $university)
   {
       $validated = $request->validate([
           'form_data' => 'required|array',
           'selected_agency_id' => 'required_unless:is_exclusive,true',
       ]);
       
       $user = $request->user();
       $agencyId = null;
       
       if ($university->isExclusive()) {
           $agencyId = $university->exclusiveAssignment->primary_agency_id;
       } else {
           // Verify selected agency is eligible
           $agency = Agency::findOrFail($validated['selected_agency_id']);
           if (!$agency->canHandleUniversity($university)) {
               abort(403, 'Selected agency cannot handle this university');
           }
           $agencyId = $agency->id;
       }
       
       $application = ServiceApplication::create([
           'user_id' => $user->id,
           'service_module_id' => 15, // University Application
           'agency_id' => $agencyId,
           'form_submission_data' => $validated['form_data'],
           'profile_snapshot' => $this->captureProfileSnapshot($user),
           'status' => 'assigned',
           'assigned_at' => now(),
       ]);
       
       return redirect()->route('user.applications.show', $application);
   }
   ```

**Testing Phase 4:**
- ✅ Exclusive universities routed to primary agency only
- ✅ Non-exclusive universities allow agency selection
- ✅ Commission split calculated correctly (70/10/20)
- ✅ Users cannot bypass exclusivity

---

### Phase 5: Job Posting System (Week 5)
**Goal:** Restrict job posting to recruiting agencies only

#### Tasks:

1. **Create Job Postings Migration**
   ```php
   // database/migrations/YYYY_MM_DD_create_job_postings.php
   
   Schema::create('job_postings', function (Blueprint $table) {
       $table->id();
       $table->foreignId('agency_id')->constrained()->onDelete('cascade');
       $table->foreignId('country_id')->constrained();
       $table->string('job_title');
       $table->text('job_description');
       $table->string('salary_range')->nullable();
       $table->json('requirements');
       $table->date('application_deadline');
       $table->enum('status', ['active', 'closed', 'expired'])->default('active');
       $table->integer('applications_count')->default(0);
       $table->decimal('agency_service_fee', 10, 2);
       $table->decimal('platform_commission_rate', 5, 2)->default(5.00);
       $table->timestamps();
       
       // Ensure only recruiting agencies can post
       $table->index(['agency_id', 'status']);
   });
   
   // Add check constraint (MySQL 8.0.16+)
   DB::statement('
       ALTER TABLE job_postings 
       ADD CONSTRAINT check_agency_can_post_jobs 
       CHECK (agency_id IN (
           SELECT id FROM agencies 
           WHERE agency_type_id = 1 -- Recruiting Agency
       ))
   ');
   ```

2. **Update Agency Model**
   ```php
   // app/Models/Agency.php
   
   public function canPostJobs(): bool
   {
       return $this->agencyType->name === 'Recruiting Agency' 
           && $this->is_verified 
           && $this->is_active;
   }
   
   public function jobPostings(): HasMany
   {
       return $this->hasMany(JobPosting::class);
   }
   
   public function activeJobPostings(): HasMany
   {
       return $this->hasMany(JobPosting::class)->where('status', 'active');
   }
   ```

3. **Create Job Posting Controller (Agency)**
   ```php
   // app/Http/Controllers/Agency/JobPostingController.php
   
   public function create(Request $request)
   {
       $agency = $request->user()->agency;
       
       if (!$agency->canPostJobs()) {
           abort(403, 'Only recruiting agencies can post jobs');
       }
       
       return Inertia::render('Agency/JobPostings/Create', [
           'countries' => Country::all(),
           'max_postings' => $agency->max_job_postings,
           'current_postings' => $agency->activeJobPostings()->count(),
       ]);
   }
   
   public function store(Request $request)
   {
       $agency = $request->user()->agency;
       
       if (!$agency->canPostJobs()) {
           abort(403);
       }
       
       // Check posting limits
       if ($agency->activeJobPostings()->count() >= $agency->max_job_postings) {
           return back()->with('error', 'Maximum job posting limit reached');
       }
       
       $validated = $request->validate([
           'country_id' => 'required|exists:countries,id',
           'job_title' => 'required|string|max:255',
           'job_description' => 'required|string',
           'salary_range' => 'nullable|string',
           'requirements' => 'required|array',
           'application_deadline' => 'required|date|after:today',
           'agency_service_fee' => 'required|numeric|min:0',
       ]);
       
       $jobPosting = JobPosting::create([
           'agency_id' => $agency->id,
           ...$validated,
           'status' => 'active',
       ]);
       
       return redirect()->route('agency.job-postings.index')
           ->with('success', 'Job posted successfully');
   }
   ```

4. **Job Application Flow (Users)**
   ```php
   // app/Http/Controllers/User/JobApplicationController.php
   
   public function apply(Request $request, JobPosting $jobPosting)
   {
       $user = $request->user();
       
       // Check if user already applied
       if ($user->jobApplications()->where('job_posting_id', $jobPosting->id)->exists()) {
           return back()->with('error', 'You have already applied to this job');
       }
       
       $application = JobApplication::create([
           'user_id' => $user->id,
           'job_posting_id' => $jobPosting->id,
           'agency_id' => $jobPosting->agency_id,
           'cv_id' => $user->primaryCv?->id,
           'status' => 'pending',
           'applied_at' => now(),
       ]);
       
       // Increment counter
       $jobPosting->increment('applications_count');
       
       return redirect()->route('user.job-applications.show', $application)
           ->with('success', 'Application submitted successfully');
   }
   ```

**Testing Phase 5:**
- ✅ Only recruiting agencies can access job posting interface
- ✅ Database constraint prevents non-recruiting agencies from posting
- ✅ Job posting limits enforced
- ✅ Users can apply with their CV
- ✅ Platform commission (5%) calculated on successful placement

---

### Phase 6: Delivery Zone Management (Week 6)
**Goal:** Handle delivery charges for attestation/notary services

#### Tasks:

1. **Create Delivery Zones Controller (Admin)**
   ```php
   // app/Http/Controllers/Admin/ServiceDeliveryZoneController.php
   
   public function store(Request $request, Agency $agency)
   {
       $validated = $request->validate([
           'service_module_id' => 'required|exists:service_modules,id',
           'city_id' => 'nullable|exists:cities,id',
           'country_id' => 'nullable|exists:countries,id',
           'delivery_charge' => 'required|numeric|min:0',
           'delivery_time_days' => 'required|integer|min:1',
       ]);
       
       $zone = AgencyServiceDeliveryZone::create([
           'agency_id' => $agency->id,
           ...$validated,
       ]);
       
       return back()->with('success', 'Delivery zone added');
   }
   ```

2. **Calculate Delivery Charge Helper**
   ```php
   // app/Helpers/service_helpers.php
   
   function calculate_delivery_charge(ServiceModule $service, Agency $agency, $cityId = null): float
   {
       if (!$service->has_delivery_charges) {
           return 0.00;
       }
       
       // Check agency-specific delivery zone
       $zone = AgencyServiceDeliveryZone::where('agency_id', $agency->id)
           ->where('service_module_id', $service->id)
           ->where(function($q) use ($cityId) {
               $q->where('city_id', $cityId)
                 ->orWhere('country_id', City::find($cityId)?->country_id)
                 ->orWhereNull('city_id'); // Global fallback
           })
           ->orderByRaw('CASE WHEN city_id IS NOT NULL THEN 1 WHEN country_id IS NOT NULL THEN 2 ELSE 3 END')
           ->first();
       
       return $zone?->delivery_charge ?? $service->default_delivery_charge;
   }
   ```

**Testing Phase 6:**
- ✅ Delivery charges calculated based on user's city
- ✅ Agency-specific delivery zones override defaults
- ✅ Charges added to total application cost

---

## 🎨 Role-Based Dashboard Designs

### 1. User Dashboard
**Focus:** Application tracking, service discovery, payments

**Layout:**
```
┌─────────────────────────────────────────────────┐
│ 🏠 My Applications      🔍 Discover Services    │
├─────────────────────────────────────────────────┤
│ Stats: Pending (3) | In Progress (2) | Completed│
├─────────────────────────────────────────────────┤
│ Recent Applications                              │
│ ┌─────────────────────────────────────────────┐ │
│ │ 📄 Tourist Visa - Thailand                  │ │
│ │ Status: Awaiting Quotes (3 agencies)        │ │
│ │ Submitted: 28 Nov 2025                      │ │
│ │ [Compare Quotes] [View Details]             │ │
│ └─────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘
```

### 2. Agency Dashboard
**Focus:** Application management, quote submission, team oversight

**Layout:**
```
┌─────────────────────────────────────────────────┐
│ 📊 Agency Overview      ⚙️ Settings             │
├─────────────────────────────────────────────────┤
│ Pending Apps (12) | Active (8) | Earnings ৳45K  │
├─────────────────────────────────────────────────┤
│ Assigned Applications                            │
│ ┌─────────────────────────────────────────────┐ │
│ │ APP-20251201-ABC123 | Tourist Visa           │ │
│ │ User: John Doe | Country: Malaysia           │ │
│ │ Deadline: 2 days | [Submit Quote]            │ │
│ └─────────────────────────────────────────────┘ │
│ Services: Tourist Visa (25) | Work Visa (12)    │
│ Countries: Thailand, Malaysia, Singapore         │
└─────────────────────────────────────────────────┘
```

### 3. Consultant Dashboard
**Focus:** Case management, document verification, client communication

**Layout:**
```
┌─────────────────────────────────────────────────┐
│ 📋 My Cases             💬 Messages             │
├─────────────────────────────────────────────────┤
│ Assigned (15) | Pending Docs (5) | Completed    │
├─────────────────────────────────────────────────┤
│ Active Cases                                     │
│ ┌─────────────────────────────────────────────┐ │
│ │ 🟡 Sarah Ahmed - Student Visa (UK)           │ │
│ │ Status: Document Review                      │ │
│ │ Action: Verify passport scan                 │ │
│ │ [Review Docs] [Contact Client]               │ │
│ └─────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘
```

### 4. Admin Dashboard
**Focus:** Platform oversight, agency approvals, revenue monitoring

**Layout:**
```
┌─────────────────────────────────────────────────┐
│ 🌐 Platform Stats       💰 Revenue Report      │
├─────────────────────────────────────────────────┤
│ Agencies (85) | Applications (1,245) | ৳2.5M    │
├─────────────────────────────────────────────────┤
│ Pending Agency Verifications (12)               │
│ ┌─────────────────────────────────────────────┐ │
│ │ 🏢 Global Travel Agency                      │ │
│ │ Type: Travel Agency | Services: 5            │ │
│ │ Documents: ✅ Trade License ✅ TOAB          │ │
│ │ [Approve] [Request More Info] [Reject]       │ │
│ └─────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘
```

---

## 📈 Commission Structure Summary

| Service Type | Platform % | Agency % | Notes |
|-------------|-----------|----------|-------|
| Tourist Visa (Competitive) | 15% | 85% | Multiple agencies compete |
| Study Abroad (Exclusive) | 20% | 70% (primary) + 10% (secondary) | University-locked |
| Air Tickets (Global Single) | 8% | 92% | High volume, low margin |
| Job Placement | 5% | 95% | From recruitment fee |
| Attestation/Translation | 20% | 80% | High value-add |
| CV Builder (Platform Direct) | 100% | 0% | No agency involved |

---

## 🚨 Critical Implementation Notes

### 1. Profile Completion Enforcement
```php
// Minimum profile completion by service:
Tourist Visa: 60%
Work Visa: 80%
Student Visa: 90%
Hajj/Umrah: 70%
```

### 2. Agency Verification Requirements
- Trade license upload
- Industry certifications (BMET, TOAB, etc.)
- Company registration documents
- Admin manual approval required

### 3. Quote Deadline System
- Applications open for quotes: 48 hours
- Auto-close after deadline
- Users can extend by 24 hours (once)

### 4. Job Posting Restrictions
```php
// Database-level enforcement
ALTER TABLE job_postings 
ADD CONSTRAINT check_recruiting_agency_only 
CHECK (agency_id IN (
    SELECT id FROM agencies 
    WHERE agency_type_id = 1
));
```

### 5. Bangladesh Localization
- All dates: DD Mon YYYY format (`format_bd_date()`)
- All currency: ৳ symbol (`format_bd_currency()`)
- Phone numbers: 01XXX-XXXXXX format
- Commission rates stored in BDT

---

## 📋 Migration Checklist

### Database Changes Required:
- [ ] Add service delivery fields to `service_modules`
- [ ] Add job posting fields to `agencies`
- [ ] Create `agency_service_delivery_zones` table
- [ ] Create `university_agency_assignments` table
- [ ] Create `job_postings` table
- [ ] Add check constraints for job posting restrictions
- [ ] Seed service module configurations

### Code Changes Required:
- [ ] Create AgencyDashboardController
- [ ] Create Agency/ApplicationController
- [ ] Create Agency/QuoteController
- [ ] Create Agency/JobPostingController
- [ ] Create User/ServiceApplicationController
- [ ] Create User/UniversityApplicationController
- [ ] Create User/JobApplicationController
- [ ] Add delivery charge calculation helpers
- [ ] Update ServiceModule model with new scopes
- [ ] Update Agency model with job posting methods

### Vue Components Required:
- [ ] Agency/Dashboard.vue
- [ ] Agency/Applications/Index.vue
- [ ] Agency/Applications/Show.vue
- [ ] Agency/JobPostings/Create.vue
- [ ] User/Applications/Create.vue (dynamic form)
- [ ] User/Applications/UniversitySelect.vue
- [ ] ServiceApplicationForm.vue (reusable)
- [ ] QuoteComparison.vue (user side)

### Routes Required:
```php
// Agency Routes (role:agency middleware)
Route::prefix('agency')->middleware(['auth', 'role:agency'])->group(function() {
    Route::get('/dashboard', [Agency\DashboardController::class, 'index']);
    Route::resource('applications', Agency\ApplicationController::class);
    Route::resource('quotes', Agency\QuoteController::class);
    Route::resource('job-postings', Agency\JobPostingController::class);
});

// User Routes
Route::prefix('user')->middleware('auth')->group(function() {
    Route::get('/services/{serviceModule}/apply', [User\ServiceApplicationController::class, 'create']);
    Route::post('/services/{serviceModule}/apply', [User\ServiceApplicationController::class, 'store']);
    Route::get('/universities/{university}/apply', [User\UniversityApplicationController::class, 'create']);
});
```

---

## 🎯 Success Metrics

### Phase 1 Success:
- ✅ Service modules correctly configured
- ✅ Delivery charges calculated
- ✅ Job posting restricted to recruiting agencies

### Phase 2 Success:
- ✅ Agency dashboard shows correct stats
- ✅ Applications filtered by agency ownership
- ✅ Quote submission works with commission calculation

### Phase 3 Success:
- ✅ Forms pre-populate from user profile
- ✅ Profile completion check enforced
- ✅ Eligible agencies notified

### Phase 4 Success:
- ✅ Exclusive universities route to correct agency
- ✅ Commission split calculated (70/10/20)

### Phase 5 Success:
- ✅ Only recruiting agencies can post jobs
- ✅ Users can apply with CV
- ✅ Platform commission tracked

---

## 📞 Next Steps

Ready to begin implementation? I recommend starting with:

1. **Phase 1** (Service Module Enhancement) - Lay the foundation
2. **Fix CSS build error** (line 275) - Required for asset compilation
3. **Phase 2** (Agency Dashboard) - Core agency functionality
4. **Test end-to-end** with sample agencies and users

Would you like me to proceed with Phase 1 implementation now?
