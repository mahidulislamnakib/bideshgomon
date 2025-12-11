<?php

namespace App\Services;

use App\Models\CvTemplate;
use App\Models\User;
use App\Models\UserCv;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * CV Builder Service
 *
 * Handles all CV building business logic including:
 * - Profile data extraction and transformation
 * - CV creation and management
 * - Premium template payment processing
 * - PDF generation
 */
class CvBuilderService
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Extract and transform user profile data for CV pre-fill
     */
    public function getUserProfileData(User $user): array
    {
        // Load all necessary relationships
        $user->load([
            'profile',
            'educations',
            'workExperiences',
            'skills',
            'languages',
            'phoneNumbers',
            'wallet',
        ]);

        $userProfile = $user->profile;

        return [
            'profileData' => $this->extractBasicProfileData($user, $userProfile),
            'profileEducation' => $this->extractEducationData($user),
            'profileExperience' => $this->extractExperienceData($user),
            'profileSkills' => $this->extractSkillsData($user),
            'profileLanguages' => $this->extractLanguagesData($user),
            'profileCertifications' => $this->extractCertificationsData($userProfile),
        ];
    }

    /**
     * Extract basic profile data
     *
     * @param  mixed  $userProfile
     */
    protected function extractBasicProfileData(User $user, $userProfile): array
    {
        // Get primary phone number
        $primaryPhone = $user->phoneNumbers->where('is_primary', true)->first();
        $phoneNumber = '';
        if ($primaryPhone) {
            $phoneNumber = $primaryPhone->phone_number;
        } elseif ($user->phoneNumbers->isNotEmpty()) {
            $phoneNumber = $user->phoneNumbers->first()->phone_number;
        }

        // Construct full name
        $fullName = $user->name;
        if ($userProfile) {
            $fullName = trim(
                ($userProfile->first_name ?? '').' '.
                ($userProfile->middle_name ?? '').' '.
                ($userProfile->last_name ?? '')
            );
        }

        return [
            'full_name' => $fullName ?: $user->name,
            'email' => $user->email,
            'phone' => $phoneNumber,
            'address' => $userProfile ? ($userProfile->present_address_line ?? '') : '',
            'city' => $userProfile ? ($userProfile->present_district ?? '') : '',
            'country_id' => $userProfile ? ($userProfile->country_id ?? null) : null,
            'linkedin_url' => $userProfile && $userProfile->social_links ? ($userProfile->social_links['linkedin'] ?? '') : '',
            'website_url' => $userProfile && $userProfile->social_links ? ($userProfile->social_links['website'] ?? '') : '',
            'professional_summary' => $userProfile ? ($userProfile->bio ?? '') : '',
        ];
    }

    /**
     * Extract and transform education data
     */
    protected function extractEducationData(User $user): array
    {
        return $user->educations->map(function ($edu) {
            return [
                'degree' => $edu->degree ?? '',
                'institution' => $edu->institution_name ?? '',
                'field_of_study' => $edu->field_of_study ?? '',
                'start_date' => $edu->start_date ? date('Y-m', strtotime($edu->start_date)) : '',
                'end_date' => $edu->end_date ? date('Y-m', strtotime($edu->end_date)) : '',
                'grade' => $edu->gpa_or_grade ?? '',
                'description' => $edu->courses_completed ?? '',
            ];
        })->toArray();
    }

    /**
     * Extract and transform work experience data
     */
    protected function extractExperienceData(User $user): array
    {
        return $user->workExperiences->map(function ($exp) {
            return [
                'job_title' => $exp->job_title ?? '',
                'company' => $exp->company_name ?? '',
                'location' => $exp->location ?? '',
                'start_date' => $exp->start_date ? date('Y-m', strtotime($exp->start_date)) : '',
                'end_date' => $exp->is_current ? '' : ($exp->end_date ? date('Y-m', strtotime($exp->end_date)) : ''),
                'is_current' => $exp->is_current ?? false,
                'description' => $exp->description ?? '',
            ];
        })->toArray();
    }

    /**
     * Extract and transform skills data
     */
    protected function extractSkillsData(User $user): array
    {
        return $user->skills->map(function ($skill) {
            return [
                'name' => $skill->name ?? '',
                'level' => strtolower($skill->pivot->proficiency_level ?? 'intermediate'),
            ];
        })->toArray();
    }

    /**
     * Extract and transform languages data
     */
    protected function extractLanguagesData(User $user): array
    {
        return $user->languages->map(function ($lang) {
            return [
                'language' => $lang->language->name ?? $lang->language ?? '',
                'proficiency' => strtolower($lang->proficiency_level ?? 'intermediate'),
            ];
        })->toArray();
    }

    /**
     * Extract certifications data
     *
     * @param  mixed  $userProfile
     */
    protected function extractCertificationsData($userProfile): array
    {
        if (! $userProfile || ! $userProfile->certifications) {
            return [];
        }

        return is_array($userProfile->certifications)
            ? $userProfile->certifications
            : json_decode($userProfile->certifications, true) ?? [];
    }

    /**
     * Create a new CV with payment processing for premium templates
     *
     * @throws \Exception
     */
    public function createCv(array $data, User $user): UserCv
    {
        return DB::transaction(function () use ($data, $user) {
            // Check if premium template and process payment
            $template = CvTemplate::findOrFail($data['cv_template_id']);

            if ($template->is_premium && $template->price > 0) {
                $this->processPremiumTemplatePayment($user, $template);
            }

            // Add user_id to data
            $data['user_id'] = $user->id;

            // Create the CV
            $cv = UserCv::create($data);

            return $cv;
        });
    }

    /**
     * Process payment for premium CV template
     *
     * @throws \Exception
     */
    protected function processPremiumTemplatePayment(User $user, CvTemplate $template): void
    {
        $wallet = $user->wallet;

        if (! $wallet || $wallet->balance < $template->price) {
            throw new \Exception('Insufficient wallet balance. Please add funds to your wallet.');
        }

        try {
            $this->walletService->debitWallet(
                $wallet,
                (float) $template->price,
                "Payment for {$template->name} CV Template",
                'premium_cv_template',
                $template->id
            );
        } catch (\Exception $e) {
            throw new \Exception('Payment failed: '.$e->getMessage());
        }
    }

    /**
     * Update an existing CV
     */
    public function updateCv(UserCv $cv, array $data): UserCv
    {
        $cv->update($data);

        return $cv->fresh();
    }

    /**
     * Delete a CV
     */
    public function deleteCv(UserCv $cv): bool
    {
        // Delete PDF if exists
        if ($cv->pdf_path && Storage::disk('public')->exists($cv->pdf_path)) {
            Storage::disk('public')->delete($cv->pdf_path);
        }

        return $cv->delete();
    }

    /**
     * Generate PDF for CV
     *
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generatePdf(UserCv $cv)
    {
        // Load relationships
        $cv->load(['cvTemplate', 'country']);

        // Generate PDF
        $pdf = Pdf::loadView('cv-pdf-template', [
            'cv' => $cv,
            'template' => $cv->cvTemplate,
        ]);

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Set options for better rendering
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);

        return $pdf;
    }

    /**
     * Download CV as PDF
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadCvPdf(UserCv $cv)
    {
        // Generate PDF
        $pdf = $this->generatePdf($cv);

        // Increment download count
        $cv->incrementDownloadCount();

        // Generate filename
        $filename = Str::slug($cv->title).'-'.date('Y-m-d').'.pdf';

        // Download PDF with proper headers
        return $pdf->download($filename);
    }

    /**
     * Get CV statistics for user
     */
    public function getUserCvStats(User $user): array
    {
        $cvs = UserCv::forUser($user->id)->get();

        return [
            'total_cvs' => $cvs->count(),
            'total_views' => $cvs->sum('view_count'),
            'total_downloads' => $cvs->sum('download_count'),
            'most_viewed_cv' => $cvs->sortByDesc('view_count')->first(),
            'most_downloaded_cv' => $cvs->sortByDesc('download_count')->first(),
            'recent_cv' => $cvs->sortByDesc('updated_at')->first(),
        ];
    }

    /**
     * Duplicate an existing CV
     */
    public function duplicateCv(UserCv $cv, User $user): UserCv
    {
        // Create a copy of the CV data
        $cvData = $cv->toArray();

        // Remove unique/system fields
        unset(
            $cvData['id'],
            $cvData['created_at'],
            $cvData['updated_at'],
            $cvData['pdf_path'],
            $cvData['last_generated_at'],
            $cvData['is_public'],
            $cvData['public_token'],
            $cvData['view_count'],
            $cvData['download_count']
        );

        // Update title to indicate it's a copy
        $cvData['title'] = $cvData['title'].' (Copy)';
        $cvData['user_id'] = $user->id;

        // Create new CV
        return UserCv::create($cvData);
    }

    /**
     * Make CV public and generate share token
     */
    public function makePublic(UserCv $cv): UserCv
    {
        if (! $cv->is_public) {
            $cv->is_public = true;
            $cv->public_token = Str::random(32);
            $cv->save();
        }

        return $cv;
    }

    /**
     * Make CV private
     */
    public function makePrivate(UserCv $cv): UserCv
    {
        $cv->is_public = false;
        $cv->public_token = null;
        $cv->save();

        return $cv;
    }

    /**
     * Get all available CV templates
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTemplates(?string $category = null, ?bool $isPremium = null)
    {
        $query = CvTemplate::active()->ordered();

        if ($category) {
            $query->byCategory($category);
        }

        if ($isPremium !== null) {
            $query->where('is_premium', $isPremium);
        }

        return $query->get();
    }

    /**
     * Get template categories
     */
    public function getTemplateCategories(): array
    {
        return CvTemplate::active()
            ->distinct()
            ->pluck('category')
            ->toArray();
    }

    /**
     * Validate CV data before saving
     */
    public function validateCvData(array $data): bool
    {
        // Check required fields
        $required = ['title', 'full_name', 'email', 'phone', 'professional_summary'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }

        // Check minimum array sizes
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
}
