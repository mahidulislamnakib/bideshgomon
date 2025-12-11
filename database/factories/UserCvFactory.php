<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\CvTemplate;
use App\Models\User;
use App\Models\UserCv;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserCvFactory extends Factory
{
    protected $model = UserCv::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'cv_template_id' => CvTemplate::factory(),
            'title' => $this->faker->words(3, true).' CV',
            'full_name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => '01'.$this->faker->numberBetween(700000000, 999999999),
            'city' => $this->faker->city(),
            'country_id' => null, // Can be set to Country::factory() if needed
            'address' => $this->faker->address(),
            'linkedin_url' => 'https://linkedin.com/in/'.$this->faker->userName(),
            'website_url' => $this->faker->url(),
            'professional_summary' => $this->faker->paragraphs(3, true),
            'education' => [
                [
                    'degree' => 'Bachelor of Science',
                    'institution' => 'Dhaka University',
                    'field_of_study' => 'Computer Science',
                    'start_date' => '2015-01',
                    'end_date' => '2019-12',
                    'grade' => '3.75',
                    'description' => 'Relevant coursework: Data Structures, Algorithms, Web Development',
                ],
            ],
            'experience' => [
                [
                    'job_title' => 'Software Developer',
                    'company' => 'Tech Solutions Ltd',
                    'location' => 'Dhaka, Bangladesh',
                    'start_date' => '2020-01',
                    'end_date' => '',
                    'is_current' => true,
                    'description' => 'Developing web applications using Laravel and Vue.js',
                ],
            ],
            'skills' => [
                ['name' => 'PHP', 'level' => 'advanced'],
                ['name' => 'Laravel', 'level' => 'advanced'],
                ['name' => 'Vue.js', 'level' => 'intermediate'],
                ['name' => 'MySQL', 'level' => 'intermediate'],
            ],
            'languages' => [
                ['language' => 'Bengali', 'proficiency' => 'native'],
                ['language' => 'English', 'proficiency' => 'fluent'],
            ],
            'certifications' => [
                [
                    'name' => 'AWS Certified Developer',
                    'issuer' => 'Amazon Web Services',
                    'date' => '2023-06',
                ],
            ],
            'projects' => [],
            'references' => [],
            'custom_sections' => [],
            'section_order' => ['personal_info', 'professional_summary', 'education', 'experience', 'skills', 'languages'],
            'pdf_path' => null,
            'last_generated_at' => null,
            'is_public' => false,
            'public_token' => null,
            'view_count' => $this->faker->numberBetween(0, 100),
            'download_count' => $this->faker->numberBetween(0, 50),
        ];
    }

    public function withPublicToken(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
            'public_token' => \Illuminate\Support\Str::random(32),
        ]);
    }

    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'view_count' => $this->faker->numberBetween(100, 500),
            'download_count' => $this->faker->numberBetween(50, 200),
        ]);
    }
}
