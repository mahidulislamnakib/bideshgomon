<?php

namespace App\Services;

use App\Models\ApplicationDocument;
use App\Models\ApplicationStatusHistory;
use App\Models\ServiceApplication;
use App\Models\ServiceModule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ServiceApplicationService
 *
 * Handles application submissions, validation, and lifecycle management
 */
class ServiceApplicationService
{
    protected $dataMapper;

    public function __construct(DataMapperService $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * Create a new application (draft or submitted)
     */
    public function createApplication(
        User $user,
        ServiceModule $service,
        array $formData,
        array $files = [],
        bool $isDraft = false
    ): ServiceApplication {
        // Validate form data
        if (! $isDraft) {
            $validation = $this->dataMapper->validateFormData($service, $formData);
            if (! $validation['valid']) {
                throw new \InvalidArgumentException(json_encode($validation['errors']));
            }
        }

        DB::beginTransaction();
        try {
            // Generate unique application number
            $applicationNumber = $this->generateApplicationNumber();

            // Take a snapshot of user profile at time of application
            $profileSnapshot = $this->captureProfileSnapshot($user);

            // Create application
            $application = ServiceApplication::create([
                'user_id' => $user->id,
                'service_module_id' => $service->id,
                'application_number' => $applicationNumber,
                'status' => $isDraft ? 'draft' : 'pending',
                'form_data' => $formData,
                'application_data' => $formData, // Backward compatibility
                'profile_snapshot' => $profileSnapshot,
                'submitted_at' => $isDraft ? null : now(),
            ]);

            // Handle file uploads
            if (! empty($files)) {
                $this->attachDocuments($application, $files);
            }

            // Record status history
            $this->recordStatusChange($application, null, $application->status,
                $isDraft ? 'Application saved as draft' : 'Application submitted');

            // Update service application count
            if (! $isDraft) {
                $service->increment('applications_count');
            }

            DB::commit();

            return $application->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create application', [
                'user_id' => $user->id,
                'service_id' => $service->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update existing application
     */
    public function updateApplication(
        ServiceApplication $application,
        array $formData,
        array $files = []
    ): ServiceApplication {
        // Only allow updates for draft applications
        if ($application->status !== 'draft') {
            throw new \Exception('Only draft applications can be updated');
        }

        DB::beginTransaction();
        try {
            $application->update([
                'form_data' => $formData,
                'application_data' => $formData,
            ]);

            // Handle new file uploads
            if (! empty($files)) {
                $this->attachDocuments($application, $files);
            }

            DB::commit();

            return $application->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update application', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Submit draft application
     */
    public function submitDraftApplication(ServiceApplication $application): ServiceApplication
    {
        if ($application->status !== 'draft') {
            throw new \Exception('Only draft applications can be submitted');
        }

        // Validate before submission
        $service = $application->serviceModule;
        $validation = $this->dataMapper->validateFormData($service, $application->form_data ?? []);

        if (! $validation['valid']) {
            throw new \InvalidArgumentException(json_encode($validation['errors']));
        }

        DB::beginTransaction();
        try {
            $application->update([
                'status' => 'pending',
                'submitted_at' => now(),
            ]);

            $this->recordStatusChange($application, 'draft', 'pending', 'Application submitted for review');

            // Update service application count
            $service->increment('applications_count');

            DB::commit();

            return $application->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Change application status (Admin)
     */
    public function changeStatus(
        ServiceApplication $application,
        string $newStatus,
        ?string $notes = null,
        ?User $changedBy = null
    ): ServiceApplication {
        $validStatuses = [
            'draft', 'pending', 'under_review', 'additional_info',
            'approved', 'rejected', 'cancelled', 'completed',
        ];

        if (! in_array($newStatus, $validStatuses)) {
            throw new \InvalidArgumentException("Invalid status: {$newStatus}");
        }

        DB::beginTransaction();
        try {
            $oldStatus = $application->status;

            $updateData = ['status' => $newStatus];

            // Update timestamp based on status
            switch ($newStatus) {
                case 'under_review':
                    $updateData['reviewed_at'] = now();
                    break;
                case 'approved':
                    $updateData['approved_at'] = now();
                    break;
                case 'completed':
                    $updateData['completed_at'] = now();
                    break;
            }

            $application->update($updateData);

            $this->recordStatusChange($application, $oldStatus, $newStatus, $notes, $changedBy);

            DB::commit();

            return $application->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Attach documents to application
     *
     * @param  array  $files  Format: ['field_name' => UploadedFile]
     */
    public function attachDocuments(ServiceApplication $application, array $files): void
    {
        foreach ($files as $fieldName => $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }

            $path = $file->store("applications/{$application->id}", 'public');

            ApplicationDocument::create([
                'application_id' => $application->id,
                'field_name' => $fieldName,
                'document_type' => $fieldName,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
    }

    /**
     * Record status change in history
     */
    protected function recordStatusChange(
        ServiceApplication $application,
        ?string $fromStatus,
        string $toStatus,
        ?string $notes = null,
        ?User $changedBy = null
    ): void {
        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'changed_by' => $changedBy?->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
        ]);
    }

    /**
     * Generate unique application number
     *
     * @return string Format: APP-2025-001234
     */
    protected function generateApplicationNumber(): string
    {
        $year = date('Y');
        $lastApp = ServiceApplication::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastApp ? (int) substr($lastApp->application_number, -6) + 1 : 1;

        return sprintf('APP-%s-%06d', $year, $sequence);
    }

    /**
     * Capture user profile snapshot at time of application
     */
    protected function captureProfileSnapshot(User $user): array
    {
        return [
            'captured_at' => now()->toISOString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'profile' => $user->profile?->toArray(),
            'passport' => $user->passports()->where('is_primary', true)->first()?->toArray(),
            'phone' => $user->phoneNumbers()->where('is_primary', true)->first()?->toArray(),
        ];
    }

    /**
     * Get application with related data
     */
    public function getApplicationDetails(int $applicationId): ServiceApplication
    {
        return ServiceApplication::with([
            'user',
            'serviceModule',
            'documents',
            'statusHistory',
        ])->findOrFail($applicationId);
    }

    /**
     * Update user profile from application data (if user requested)
     */
    public function syncProfileFromApplication(ServiceApplication $application, array $fieldsToUpdate = []): bool
    {
        return $this->dataMapper->updateProfileFromFormData(
            $application->user,
            $application->serviceModule,
            $application->form_data ?? [],
            $fieldsToUpdate
        );
    }
}
