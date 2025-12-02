# Phase 1: Service Module Enhancement - Implementation Status

**Date:** 02 Dec 2025  
**Status:** ✅ Code Complete - Ready for Database Migration

---

## ✅ Completed Tasks

### 1. Database Migration Created
**File:** `database/migrations/2025_12_02_072620_add_service_delivery_fields_to_service_modules_table.php`

**Fields Added:**
- `requires_form_submission` (boolean, default: true)
- `has_delivery_charges` (boolean, default: false)
- `default_delivery_charge` (decimal 10,2, default: 0.00)
- `is_query_based` (boolean, default: true)
- `is_marketplace` (boolean, default: false)
- `supports_university_exclusivity` (boolean, default: false)
- `restricted_to_agency_type_id` (foreign key to agency_types, nullable)

**Indexes Added:**
- Index on `is_marketplace`
- Index on `restricted_to_agency_type_id`

### 2. ServiceModule Model Enhanced
**File:** `app/Models/ServiceModule.php`

**New Fillable Fields:**
All 7 new fields added to `$fillable` array

**New Casts:**
- `requires_form_submission` => 'boolean'
- `has_delivery_charges` => 'boolean'
- `is_query_based` => 'boolean'
- `is_marketplace` => 'boolean'
- `supports_university_exclusivity` => 'boolean'
- `default_delivery_charge` => 'decimal:2'

**New Relationships:**
```php
public function restrictedToAgencyType(): BelongsTo
// Returns the AgencyType if service is restricted
```

**New Helper Methods:**
```php
isRestrictedToAgencyType(): bool
// Check if service is restricted to specific agency type

canAgencyOffer(Agency $agency): bool
// Validate if agency can offer this service

supportsMultipleAgencies(): bool
// Check if marketplace model (multiple agencies compete)

requiresDelivery(): bool
// Check if service has delivery charges

hasUniversityExclusivity(): bool
// Check if service supports university-exclusive assignments
```

**New Query Scopes:**
```php
scopeMarketplace($query)
// Get only marketplace services

scopeQueryBased($query)
// Get only query-based services

scopeWithDelivery($query)
// Get services with delivery charges

scopeRestrictedToAgencyType($query, $agencyTypeId)
// Get services restricted to specific agency type
```

### 3. Configuration Seeder Created
**File:** `database/seeders/ServiceModuleConfigurationSeeder.php`

**Configures 27+ Services:**

#### Travel Services
- **Air Tickets**: Global single agency, query-based, no delivery
- **Tourist Visa**: Marketplace (multiple agencies), query-based
- **Work Visa**: Marketplace, query-based
- **Student Visa**: Marketplace, query-based
- **Tour Packages**: Marketplace, query-based
- **Hajj-Umrah Packages**: Marketplace, query-based (certification required)
- **Travel Insurance**: Marketplace, quote comparison

#### Document Services (with Delivery Charges)
- **Attestation**: Marketplace, ৳200 delivery charge
- **Notary**: Marketplace, ৳150 delivery charge
- **Translation**: Marketplace, ৳100 delivery charge
- **Document Verification**: Marketplace, ৳180 delivery charge

#### Education Services
- **University Application**: Supports university exclusivity 🔑
- **Course Enrollment**: Marketplace, query-based
- **Language Test Prep**: Marketplace, query-based

#### Employment Services
- **Job Posting**: ⚠️ **RESTRICTED to Recruiting Agency ONLY**
- **Job Application**: Single agency (job poster)
- **CV Building**: Platform direct (no agency)
- **Career Counseling**: Marketplace

#### Medical Services
- **Medical Test**: Marketplace (multiple centers)
- **Biometric Enrollment**: Government centers

#### Consultation Services
- **Immigration Consultation**: Marketplace
- **Legal Consultation**: Marketplace

#### Financial Services
- **Forex Exchange**: Marketplace
- **Remittance**: Marketplace

---

## 🎯 Key Features Implemented

### 1. Marketplace Model
Services where **multiple agencies can compete** by submitting quotes:
- Tourist Visa
- Work Visa
- Student Visa
- Tour Packages
- All document services
- All consultation services

**How it works:**
```php
if ($serviceModule->supportsMultipleAgencies()) {
    // Notify all eligible agencies
    // Agencies submit quotes within 48 hours
    // User compares and selects best quote
}
```

### 2. Delivery Charge System
Services with **home delivery option**:
- Attestation: ৳200
- Notary: ৳150
- Translation: ৳100
- Document Verification: ৳180

**Usage:**
```php
if ($serviceModule->requiresDelivery()) {
    $deliveryCharge = $serviceModule->default_delivery_charge;
    // Can be overridden by agency-specific zones
}
```

### 3. University Exclusivity
**University Application** service supports exclusive agency assignments:
- One university = One primary agency (70% commission)
- Optional secondary agency (10% commission)
- Platform takes 20%

**Implementation:**
```php
if ($serviceModule->hasUniversityExclusivity()) {
    // Route to university's assigned agency
    // No quote comparison
}
```

### 4. Agency Type Restrictions
**Job Posting** is restricted to **Recruiting Agencies ONLY**:

**Database-level enforcement:**
```php
'restricted_to_agency_type_id' => $recruitingAgencyId
```

**Model-level validation:**
```php
if (!$serviceModule->canAgencyOffer($agency)) {
    abort(403, 'Your agency type cannot offer this service');
}
```

---

## 📋 Next Steps to Complete Phase 1

### Step 1: Run Migration
```bash
# If database is working:
php artisan migrate

# This will add 7 new columns to service_modules table
```

### Step 2: Run Configuration Seeder
```bash
php artisan db:seed --class=ServiceModuleConfigurationSeeder

# This will:
# ✓ Update 27+ service modules with correct configurations
# ✓ Set marketplace flags
# ✓ Set delivery charges
# ✓ Set university exclusivity
# ✓ Set agency type restrictions
```

### Step 3: Verify Configuration
```php
// In tinker or controller
php artisan tinker

// Test marketplace service
$touristVisa = ServiceModule::where('slug', 'tourist-visa')->first();
$touristVisa->supportsMultipleAgencies(); // Should return true

// Test delivery service
$attestation = ServiceModule::where('slug', 'attestation')->first();
$attestation->requiresDelivery(); // Should return true
$attestation->default_delivery_charge; // Should return 200.00

// Test restricted service
$jobPosting = ServiceModule::where('slug', 'job-posting')->first();
$jobPosting->isRestrictedToAgencyType(); // Should return true
$jobPosting->restrictedToAgencyType->name; // Should return "Recruiting Agency"

// Test university exclusivity
$uniApp = ServiceModule::where('slug', 'university-application')->first();
$uniApp->hasUniversityExclusivity(); // Should return true
```

---

## 🔧 Usage Examples

### Example 1: Check if Agency Can Offer Service
```php
// In controller or service
use App\Models\ServiceModule;
use App\Models\Agency;

$jobPostingService = ServiceModule::where('slug', 'job-posting')->first();
$travelAgency = Agency::find(1); // Travel Agency
$recruitingAgency = Agency::find(2); // Recruiting Agency

$jobPostingService->canAgencyOffer($travelAgency); 
// Returns: false (Travel agencies cannot post jobs)

$jobPostingService->canAgencyOffer($recruitingAgency); 
// Returns: true (Recruiting agencies can post jobs)
```

### Example 2: Get Marketplace Services
```php
// Get all services where agencies compete
$marketplaceServices = ServiceModule::marketplace()
    ->active()
    ->get();

// Result: Tourist Visa, Work Visa, Student Visa, Tour Packages, etc.
```

### Example 3: Get Services with Delivery
```php
// Get all services that have delivery charges
$deliveryServices = ServiceModule::withDelivery()
    ->active()
    ->get();

// Result: Attestation (৳200), Notary (৳150), Translation (৳100), etc.

foreach ($deliveryServices as $service) {
    echo "{$service->name}: ৳{$service->default_delivery_charge}\n";
}
```

### Example 4: Filter Services by Agency Type
```php
// Get services available for a specific agency
$agency = Agency::find(1);
$allowedServiceIds = $agency->agencyType->allowed_service_modules;

$availableServices = ServiceModule::whereIn('id', $allowedServiceIds)
    ->active()
    ->get()
    ->filter(function($service) use ($agency) {
        return $service->canAgencyOffer($agency);
    });
```

### Example 5: Calculate Total Cost with Delivery
```php
$application = new ServiceApplication();
$application->quoted_amount = 5000.00; // Agency quote
$application->requires_delivery = true;

$serviceModule = $application->serviceModule;

$totalCost = $application->quoted_amount;

if ($serviceModule->requiresDelivery() && $application->requires_delivery) {
    $totalCost += $serviceModule->default_delivery_charge;
}

// Example: ৳5,000 + ৳200 delivery = ৳5,200
```

---

## 🧪 Testing Checklist

### Database Tests
- [ ] Migration runs without errors
- [ ] All 7 columns added to service_modules table
- [ ] Indexes created successfully
- [ ] Foreign key constraint on restricted_to_agency_type_id works

### Model Tests
- [ ] All new fields are fillable
- [ ] All new fields cast to correct types
- [ ] Relationship to AgencyType works
- [ ] Helper methods return correct boolean values
- [ ] Query scopes filter correctly

### Seeder Tests
- [ ] Seeder runs without errors
- [ ] 27+ services updated successfully
- [ ] Marketplace flags set correctly
- [ ] Delivery charges saved as decimal
- [ ] University exclusivity flag set
- [ ] Job posting restricted to recruiting agency

### Business Logic Tests
- [ ] Tourist Visa service allows multiple agencies
- [ ] Attestation service has ৳200 delivery charge
- [ ] University Application supports exclusivity
- [ ] Job Posting restricted to recruiting agencies only
- [ ] CV Building has no agency involvement
- [ ] Travel agencies CANNOT post jobs
- [ ] Recruiting agencies CAN post jobs

---

## 📊 Expected Database State After Migration

### service_modules Table (Sample Rows)

| id | slug | name | is_marketplace | has_delivery_charges | default_delivery_charge | supports_university_exclusivity | restricted_to_agency_type_id |
|----|------|------|----------------|---------------------|------------------------|--------------------------------|------------------------------|
| 1 | tourist-visa | Tourist Visa | 1 (true) | 0 (false) | 0.00 | 0 (false) | NULL |
| 2 | attestation | Attestation | 1 (true) | 1 (true) | 200.00 | 0 (false) | NULL |
| 3 | university-application | University Application | 0 (false) | 0 (false) | 0.00 | 1 (true) | NULL |
| 4 | job-posting | Job Posting | 0 (false) | 0 (false) | 0.00 | 0 (false) | 1 (Recruiting Agency) |
| 5 | cv-building | CV Building | 0 (false) | 0 (false) | 0.00 | 0 (false) | NULL |

---

## 🚨 Critical Notes

### 1. Job Posting Restriction
**IMPORTANT:** Only recruiting agencies (agency_type_id = 1) can post jobs.

**Enforcement levels:**
1. **Database:** `restricted_to_agency_type_id` foreign key
2. **Model:** `canAgencyOffer()` method
3. **Controller:** Middleware/validation checks
4. **UI:** Hide job posting option for non-recruiting agencies

### 2. Commission Calculations
Different service models have different commission structures:

| Service Model | Platform % | Agency % | Notes |
|--------------|-----------|----------|-------|
| Marketplace (Tourist Visa) | 15% | 85% | Selected agency gets full commission |
| University Exclusive | 20% | 70% + 10% | Primary + Secondary agencies |
| Job Posting | 5% | 95% | Low margin, recruiting fee |
| CV Building | 100% | 0% | No agency involvement |

### 3. Bangladesh Localization
All monetary values use Bangladesh formatting:
- Currency symbol: ৳ (Taka)
- Delivery charges in BDT
- Format using `format_bd_currency()` helper

### 4. Profile Completion Requirements
Services may require minimum profile completion:
```php
$serviceModule->min_profile_completion // e.g., 80%
```

Check before allowing application submission.

---

## 🔗 Integration Points

### With Agency System
```php
// Check if agency can offer service
$agency->agencyType->allowed_service_modules // Array of IDs
$serviceModule->canAgencyOffer($agency) // Boolean validation
```

### With Application Flow
```php
// Determine application routing
if ($serviceModule->supportsMultipleAgencies()) {
    // Quote-based: notify multiple agencies
    $this->notifyEligibleAgencies($application);
} else {
    // Direct assignment
    $this->assignToSingleAgency($application);
}
```

### With Wallet System
```php
// Calculate commission split
$quotedAmount = 5000.00;
$platformCommission = ($quotedAmount * 15) / 100; // ৳750
$agencyEarnings = $quotedAmount - $platformCommission; // ৳4,250

// Add delivery charge if applicable
if ($serviceModule->requiresDelivery()) {
    $total = $quotedAmount + $serviceModule->default_delivery_charge;
}
```

---

## 📦 Files Modified/Created

### Created Files
1. `database/migrations/2025_12_02_072620_add_service_delivery_fields_to_service_modules_table.php`
2. `database/seeders/ServiceModuleConfigurationSeeder.php`

### Modified Files
1. `app/Models/ServiceModule.php`
   - Added 7 fields to $fillable
   - Added 6 fields to $casts
   - Added 1 relationship method
   - Added 5 helper methods
   - Added 4 query scopes

---

## ✅ Phase 1 Success Criteria

- [x] Migration file created with all required columns
- [x] ServiceModule model updated with new fields
- [x] Configuration seeder created for all services
- [x] Helper methods implemented for business logic
- [x] Query scopes added for filtering
- [x] Job posting restriction configured
- [x] University exclusivity flag set
- [x] Delivery charges defined
- [x] Marketplace flags configured
- [ ] **Database migration executed successfully** ⏳
- [ ] **Configuration seeder run successfully** ⏳
- [ ] **Testing completed** ⏳

---

## 🚀 Ready to Execute

**Commands to run when database is available:**

```bash
# 1. Run migration
php artisan migrate

# 2. Run configuration seeder
php artisan db:seed --class=ServiceModuleConfigurationSeeder

# 3. Verify with tinker
php artisan tinker
>>> ServiceModule::where('slug', 'tourist-visa')->first()->supportsMultipleAgencies()
=> true
>>> ServiceModule::where('slug', 'attestation')->first()->default_delivery_charge
=> 200.00
>>> ServiceModule::where('slug', 'job-posting')->first()->restrictedToAgencyType->name
=> "Recruiting Agency"
```

**Phase 1 will be complete once these commands execute successfully!** ✅
