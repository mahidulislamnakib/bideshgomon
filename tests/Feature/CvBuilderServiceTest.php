<?php

namespace Tests\Feature;

use App\Models\CvTemplate;
use App\Models\User;
use App\Models\UserCv;
use App\Services\CvBuilderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CvBuilderServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected CvBuilderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CvBuilderService::class);
    }

    /** @test */
    public function it_can_extract_user_profile_data()
    {
        $user = User::factory()->create();

        // Create profile
        $user->profile()->create([
            'first_name' => 'John',
            'middle_name' => 'M',
            'last_name' => 'Doe',
            'bio' => 'Experienced professional with extensive background',
        ]);

        $profileData = $this->service->getUserProfileData($user);

        $this->assertArrayHasKey('profileData', $profileData);
        $this->assertArrayHasKey('profileEducation', $profileData);
        $this->assertArrayHasKey('profileExperience', $profileData);
        $this->assertArrayHasKey('profileSkills', $profileData);
        $this->assertArrayHasKey('profileLanguages', $profileData);
        $this->assertArrayHasKey('profileCertifications', $profileData);

        $this->assertEquals('John M Doe', $profileData['profileData']['full_name']);
    }

    /** @test */
    public function it_can_create_a_free_cv()
    {
        $user = User::factory()->create();

        // Create free template
        $template = CvTemplate::factory()->create([
            'is_premium' => false,
            'price' => 0,
        ]);

        $cvData = [
            'cv_template_id' => $template->id,
            'title' => 'My Professional CV',
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '01712345678',
            'professional_summary' => 'Experienced professional with extensive background in software development',
            'education' => [['degree' => 'BSc', 'institution' => 'DU']],
            'experience' => [['job_title' => 'Developer', 'company' => 'Tech Corp']],
            'skills' => [['name' => 'PHP', 'level' => 'advanced']],
        ];

        $cv = $this->service->createCv($cvData, $user);

        $this->assertInstanceOf(UserCv::class, $cv);
        $this->assertEquals('My Professional CV', $cv->title);
        $this->assertEquals($user->id, $cv->user_id);
    }

    /** @test */
    public function it_throws_exception_for_premium_cv_without_balance()
    {
        $user = User::factory()->create();

        // Create wallet with insufficient balance
        $user->wallet()->create(['balance' => 100]);

        // Create premium template
        $template = CvTemplate::factory()->create([
            'is_premium' => true,
            'price' => 500,
        ]);

        $cvData = [
            'cv_template_id' => $template->id,
            'title' => 'Premium CV',
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '01712345678',
            'professional_summary' => 'Experienced professional with extensive background in software development',
            'education' => [['degree' => 'BSc']],
            'experience' => [['job_title' => 'Developer']],
            'skills' => [['name' => 'PHP']],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient wallet balance');

        $this->service->createCv($cvData, $user);
    }

    /** @test */
    public function it_can_duplicate_a_cv()
    {
        $user = User::factory()->create();

        $cv = UserCv::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original CV',
            'view_count' => 10,
            'download_count' => 5,
        ]);

        $duplicatedCv = $this->service->duplicateCv($cv, $user);

        $this->assertNotEquals($cv->id, $duplicatedCv->id);
        $this->assertEquals('Original CV (Copy)', $duplicatedCv->title);
        $this->assertEquals(0, $duplicatedCv->view_count);
        $this->assertEquals(0, $duplicatedCv->download_count);
    }

    /** @test */
    public function it_can_make_cv_public_and_private()
    {
        $user = User::factory()->create();

        $cv = UserCv::factory()->create([
            'user_id' => $user->id,
            'is_public' => false,
        ]);

        // Make public
        $this->service->makePublic($cv);
        $this->assertTrue($cv->fresh()->is_public);
        $this->assertNotNull($cv->fresh()->public_token);

        // Make private
        $this->service->makePrivate($cv);
        $this->assertFalse($cv->fresh()->is_public);
        $this->assertNull($cv->fresh()->public_token);
    }

    /** @test */
    public function it_can_get_user_cv_statistics()
    {
        $user = User::factory()->create();

        // Create multiple CVs with different stats
        UserCv::factory()->create([
            'user_id' => $user->id,
            'view_count' => 100,
            'download_count' => 20,
        ]);

        UserCv::factory()->create([
            'user_id' => $user->id,
            'view_count' => 50,
            'download_count' => 30,
        ]);

        $stats = $this->service->getUserCvStats($user);

        $this->assertEquals(2, $stats['total_cvs']);
        $this->assertEquals(150, $stats['total_views']);
        $this->assertEquals(50, $stats['total_downloads']);
        $this->assertInstanceOf(UserCv::class, $stats['most_viewed_cv']);
        $this->assertEquals(100, $stats['most_viewed_cv']->view_count);
    }

    /** @test */
    public function it_can_validate_cv_data()
    {
        // Valid data
        $validData = [
            'title' => 'My CV',
            'full_name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '01712345678',
            'professional_summary' => 'A detailed professional summary with more than fifty characters',
            'education' => [['degree' => 'BSc']],
            'experience' => [['job_title' => 'Developer']],
            'skills' => [['name' => 'PHP']],
        ];

        $this->assertTrue($this->service->validateCvData($validData));

        // Invalid data - missing education
        $invalidData = $validData;
        $invalidData['education'] = [];

        $this->assertFalse($this->service->validateCvData($invalidData));
    }

    /** @test */
    public function it_can_get_template_categories()
    {
        CvTemplate::factory()->create(['category' => 'Professional', 'is_active' => true]);
        CvTemplate::factory()->create(['category' => 'Creative', 'is_active' => true]);
        CvTemplate::factory()->create(['category' => 'Professional', 'is_active' => true]);

        $categories = $this->service->getTemplateCategories();

        $this->assertCount(2, $categories);
        $this->assertContains('Professional', $categories);
        $this->assertContains('Creative', $categories);
    }
}
