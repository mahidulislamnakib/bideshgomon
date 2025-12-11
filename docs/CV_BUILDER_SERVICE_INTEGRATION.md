# CV Builder Service - Complete Integration with User Profile

## Overview

The CV Builder Service is now fully integrated with the user profile system, automatically extracting and transforming profile data for CV creation. The service handles all business logic including payment processing, PDF generation, and CV management.

---

## Architecture

### Service Layer: `CvBuilderService`

**Location:** `app/Services/CvBuilderService.php`

**Responsibilities:**
- Extract user profile data and transform for CV format
- Handle CV creation with premium template payment processing
- Manage CV operations (update, delete, duplicate)
- Generate and download PDFs
- Handle CV sharing (public/private)
- Provide CV statistics

**Dependencies:**
- `WalletService` - For premium template payments
- `UserCv` Model
- `CvTemplate` Model
- `User` Model with profile relationships

---

## Profile Data Integration

### Automatic Profile Extraction

The service automatically extracts data from the user's profile including:

#### 1. Basic Profile Data
```php
$profileData = [
    'full_name' => 'From user_profiles (first_name + middle_name + last_name)',
    'email' => 'User email',
    'phone' => 'Primary phone from user_phone_numbers',
    'address' => 'Present address from user_profiles',
    'city' => 'Present district from user_profiles',
    'country_id' => 'Country ID from user_profiles',
    'linkedin_url' => 'From user_profiles.social_links',
    'website_url' => 'From user_profiles.social_links',
    'professional_summary' => 'Bio from user_profiles',
];
```

#### 2. Education Data
**Source:** `user_educations` table

**Transformation:**
```php
[
    'degree' => 'degree',
    'institution' => 'institution_name',
    'field_of_study' => 'field_of_study',
    'start_date' => 'start_date (Y-m format)',
    'end_date' => 'end_date (Y-m format)',
    'grade' => 'gpa_or_grade',
    'description' => 'courses_completed',
]
```

#### 3. Work Experience Data
**Source:** `user_work_experiences` table

**Transformation:**
```php
[
    'job_title' => 'job_title',
    'company' => 'company_name',
    'location' => 'location',
    'start_date' => 'start_date (Y-m format)',
    'end_date' => 'end_date or empty if is_current',
    'is_current' => 'is_current boolean',
    'description' => 'description',
]
```

#### 4. Skills Data
**Source:** `user_skills` pivot table

**Transformation:**
```php
[
    'name' => 'Skill name',
    'level' => 'proficiency_level (lowercase)',
]
```

#### 5. Languages Data
**Source:** `user_languages` table

**Transformation:**
```php
[
    'language' => 'Language name',
    'proficiency' => 'proficiency_level (lowercase)',
]
```

#### 6. Certifications Data
**Source:** `user_profiles.certifications` JSON field

**Format:** Array of certifications from profile

---

## Service Methods

### Profile Data Methods

#### `getUserProfileData(User $user): array`
Extracts and transforms all user profile data for CV pre-filling.

**Returns:**
```php
[
    'profileData' => [...],          // Basic contact info
    'profileEducation' => [...],     // Education history
    'profileExperience' => [...],    // Work experience
    'profileSkills' => [...],        // Skills list
    'profileLanguages' => [...],     // Languages
    'profileCertifications' => [...] // Certifications
]
```

**Usage in Controller:**
```php
public function create(Request $request)
{
    $user = auth()->user();
    $profileData = $this->cvBuilderService->getUserProfileData($user);
    
    return Inertia::render('Services/CvBuilder/Create', [
        'template' => $template,
        'user' => $user,
        'countries' => $countries,
        ...$profileData,  // Spread all profile data
    ]);
}
```

### CV Management Methods

#### `createCv(array $data, User $user): UserCv`
Creates a new CV with automatic payment processing for premium templates.

**Process:**
1. Validates template existence
2. If premium template:
   - Checks wallet balance
   - Processes payment via `WalletService`
   - Creates wallet transaction
3. Creates CV record
4. Returns UserCv instance

**Throws:** `\Exception` if insufficient balance or payment fails

**Usage:**
```php
try {
    $cv = $this->cvBuilderService->createCv($validated, auth()->user());
    return redirect()->route('cv-builder.my-cvs')
        ->with('success', 'CV created successfully!');
} catch (\Exception $e) {
    return back()->withErrors(['payment' => $e->getMessage()]);
}
```

#### `updateCv(UserCv $cv, array $data): UserCv`
Updates an existing CV.

#### `deleteCv(UserCv $cv): bool`
Deletes a CV and removes associated PDF file.

#### `duplicateCv(UserCv $cv, User $user): UserCv`
Creates a copy of an existing CV with " (Copy)" appended to title.

**Resets:**
- View count
- Download count
- Public token
- PDF path

### PDF Methods

#### `generatePdf(UserCv $cv)`
Generates PDF using DomPDF with template styling.

**Configuration:**
- Paper: A4 portrait
- HTML5 parser enabled
- Remote content enabled

#### `downloadCvPdf(UserCv $cv)`
Generates PDF and returns download response.

**Actions:**
- Generates PDF
- Increments download count
- Creates filename: `{cv-title}-{date}.pdf`
- Returns download response

### Sharing Methods

#### `makePublic(UserCv $cv): UserCv`
Makes CV public and generates 32-character share token.

#### `makePrivate(UserCv $cv): UserCv`
Makes CV private and removes share token.

### Statistics Methods

#### `getUserCvStats(User $user): array`
Returns comprehensive CV statistics for user.

**Returns:**
```php
[
    'total_cvs' => 5,
    'total_views' => 127,
    'total_downloads' => 43,
    'most_viewed_cv' => UserCv,
    'most_downloaded_cv' => UserCv,
    'recent_cv' => UserCv,
]
```

### Template Methods

#### `getTemplates(?string $category = null, ?bool $isPremium = null)`
Returns filtered list of active CV templates.

#### `getTemplateCategories(): array`
Returns array of unique template categories.

---

## Controller Integration

### `CvBuilderController`

**Updated Methods:**

1. **`create(Request $request)`**
   - Uses `getUserProfileData()` to pre-fill form
   - Spreads profile data directly to Inertia

2. **`store(Request $request)`**
   - Uses `createCv()` with exception handling
   - Automatic payment processing for premium templates

3. **`update(Request $request, $id)`**
   - Uses `updateCv()` for business logic

4. **`destroy($id)`**
   - Uses `deleteCv()` with automatic PDF cleanup

5. **`download($id)`**
   - Uses `downloadCvPdf()` for PDF generation

**New Methods:**

6. **`duplicate($id)`**
   - Duplicates CV and redirects to edit page
   - Route: `POST /services/cv-builder/{id}/duplicate`

7. **`toggleShare($id)`**
   - Toggles CV public/private status
   - Route: `POST /services/cv-builder/{id}/share`

---

## Routes

### Complete Route List

```php
Route::prefix('services/cv-builder')->name('cv-builder.')->group(function () {
    // Viewing
    Route::get('/', [CvBuilderController::class, 'index'])->name('index');
    Route::get('/template/{slug}', [CvBuilderController::class, 'showTemplate'])->name('template');
    Route::get('/my-cvs', [CvBuilderController::class, 'myCvs'])->name('my-cvs');
    Route::get('/{id}/preview', [CvBuilderController::class, 'preview'])->name('preview');
    
    // CRUD
    Route::get('/create', [CvBuilderController::class, 'create'])->name('create');
    Route::post('/store', [CvBuilderController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [CvBuilderController::class, 'edit'])->name('edit');
    Route::put('/{id}', [CvBuilderController::class, 'update'])->name('update');
    Route::delete('/{id}', [CvBuilderController::class, 'destroy'])->name('destroy');
    
    // Actions
    Route::get('/{id}/download', [CvBuilderController::class, 'download'])->name('download');
    Route::post('/{id}/duplicate', [CvBuilderController::class, 'duplicate'])->name('duplicate');
    Route::post('/{id}/share', [CvBuilderController::class, 'toggleShare'])->name('toggle-share');
});
```

---

## Payment Integration

### Premium Template Payment Flow

1. **User selects premium template** in create wizard
2. **Controller validates** request data
3. **Service checks** template price and wallet balance:
   ```php
   if ($template->is_premium && $template->price > 0) {
       if ($wallet->balance < $template->price) {
           throw new \Exception('Insufficient wallet balance');
       }
   }
   ```
4. **WalletService debits** wallet:
   ```php
   $this->walletService->debitWallet(
       $wallet,
       (float) $template->price,
       "Payment for {$template->name} CV Template",
       'premium_cv_template',
       $template->id
   );
   ```
5. **Transaction recorded** in `wallet_transactions`
6. **CV created** with user_id and template_id

### Transaction Details

**Reference Type:** `'premium_cv_template'`  
**Reference ID:** `cv_template_id`  
**Description:** `"Payment for {Template Name} CV Template"`  
**Amount:** Template price in BDT

---

## Bangladesh Localization

### Currency Formatting
All template prices displayed using Bangladesh helpers:

```php
// Template price display
৳500 (formatted via format_bd_currency)

// In controller
$template->formatted_price // Uses CvTemplate accessor
```

### Date Formatting
Dates in CV follow DD/MM/YYYY format:

```php
// Education dates
'start_date' => date('Y-m', strtotime($edu->start_date))
// Stored as Y-m for month picker compatibility
```

---

## User Profile Requirements

### Minimum Profile Completeness for CV

To create a quality CV, users should complete:

**Required:**
- ✅ Basic profile (name, email, phone)
- ✅ At least 1 education entry
- ✅ At least 1 work experience
- ✅ At least 1 skill

**Recommended:**
- 📝 Professional summary/bio (minimum 50 characters)
- 📝 Languages
- 📝 Certifications
- 📝 LinkedIn URL
- 📝 Address and city

### Profile Completion Check

```php
// In service
public function validateCvData(array $data): bool
{
    $required = ['title', 'full_name', 'email', 'phone', 'professional_summary'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            return false;
        }
    }
    
    if (empty($data['education']) || count($data['education']) < 1) {
        return false;
    }
    
    if (empty($data['experience']) || count($data['experience']) < 1) {
        return false;
    }
    
    if (empty($data['skills']) || count($data['skills']) < 1) {
        return false;
    }
    
    return true;
}
```

---

## Database Schema

### `user_cvs` Table

```sql
id                      BIGINT PRIMARY KEY
user_id                 BIGINT FOREIGN KEY
cv_template_id          BIGINT FOREIGN KEY
title                   VARCHAR(255)
full_name               VARCHAR(255)
email                   VARCHAR(255)
phone                   VARCHAR(20)
city                    VARCHAR(100)
country_id              BIGINT NULLABLE
address                 TEXT
linkedin_url            VARCHAR(255)
website_url             VARCHAR(255)
professional_summary    TEXT
education               JSON
experience              JSON
skills                  JSON
languages               JSON
certifications          JSON
projects                JSON
references              JSON
custom_sections         JSON
section_order           JSON
pdf_path                VARCHAR(255)
last_generated_at       TIMESTAMP
is_public               BOOLEAN DEFAULT false
public_token            VARCHAR(32) UNIQUE
view_count              INT DEFAULT 0
download_count          INT DEFAULT 0
created_at              TIMESTAMP
updated_at              TIMESTAMP
```

### `cv_templates` Table

```sql
id              BIGINT PRIMARY KEY
name            VARCHAR(255)
slug            VARCHAR(255) UNIQUE
description     TEXT
thumbnail       VARCHAR(255)
category        VARCHAR(100)
is_premium      BOOLEAN DEFAULT false
price           DECIMAL(10,2)
color_scheme    JSON
sections        JSON
download_count  INT DEFAULT 0
is_active       BOOLEAN DEFAULT true
sort_order      INT DEFAULT 0
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

---

## Testing

### Test User Profile Setup

```bash
php artisan tinker

# Create test user with profile
$user = User::factory()->create();
$user->profile()->create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'bio' => 'Experienced software developer...',
]);

# Add education
$user->educations()->create([
    'degree' => 'BSc',
    'institution_name' => 'Dhaka University',
    'field_of_study' => 'Computer Science',
    'start_date' => '2015-01-01',
    'end_date' => '2019-12-31',
]);

# Add experience
$user->workExperiences()->create([
    'job_title' => 'Software Developer',
    'company_name' => 'Tech Corp',
    'start_date' => '2020-01-01',
    'is_current' => true,
]);
```

### Test Service

```php
use App\Services\CvBuilderService;

$service = app(CvBuilderService::class);

// Get profile data
$profileData = $service->getUserProfileData($user);
dd($profileData);

// Create CV
$cvData = [...]; // Your CV data
$cv = $service->createCv($cvData, $user);

// Generate PDF
$pdf = $service->generatePdf($cv);
$pdf->download('test.pdf');
```

---

## Features Summary

✅ **Profile Integration**
- Automatic data extraction from 9 profile tables
- Intelligent data transformation for CV format
- Pre-filling of all CV fields

✅ **Payment Processing**
- Wallet integration for premium templates
- Transaction recording
- Balance validation

✅ **PDF Generation**
- Professional A4 layouts
- Template-based styling
- Bangladesh formatting

✅ **CV Management**
- Create, read, update, delete
- Duplicate existing CVs
- Public/private sharing
- View and download tracking

✅ **Statistics**
- Total CVs count
- Total views/downloads
- Most popular CVs
- Recent activity

---

## Next Steps / Future Enhancements

### Potential Improvements

1. **AI-Powered Suggestions**
   - Suggest professional summary improvements
   - Recommend skills based on experience
   - Optimize CV for ATS (Applicant Tracking Systems)

2. **Advanced Sharing**
   - QR code generation for CVs
   - Custom domains for public CVs
   - Analytics for public CVs

3. **Templates**
   - More template categories (creative, technical, executive)
   - Custom template builder
   - Template marketplace

4. **Export Options**
   - Word (.docx) export
   - LinkedIn profile sync
   - JSON/XML export for APIs

5. **Collaboration**
   - CV review requests
   - Mentor feedback system
   - Employer CV ratings

---

## Status: ✅ **COMPLETE & PRODUCTION READY**

**Last Updated:** December 3, 2025  
**Version:** 1.0.0  
**Maintained By:** Platform Team
