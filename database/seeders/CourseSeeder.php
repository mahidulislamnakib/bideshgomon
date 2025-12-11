<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\University;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $universities = University::all();

        if ($universities->isEmpty()) {
            $this->command->warn('No universities found. Please run UniversitySeeder first.');

            return;
        }

        // Sample courses data - we'll create 50+ courses
        $coursesData = $this->getCoursesData();

        $coursesCreated = 0;

        foreach ($coursesData as $uniName => $courses) {
            $university = $universities->where('name', $uniName)->first();

            if (! $university) {
                continue;
            }

            foreach ($courses as $course) {
                Course::create(array_merge($course, [
                    'university_id' => $university->id,
                    'start_date' => now()->addMonths(rand(1, 6)),
                    'application_deadline' => now()->addMonths(rand(1, 4)),
                    'enrollment_capacity' => rand(20, 200),
                    'current_enrollment' => rand(0, 150),
                    'average_rating' => rand(40, 50) / 10,
                    'reviews_count' => rand(10, 500),
                ]));

                $coursesCreated++;
            }
        }

        $this->command->info("Successfully seeded {$coursesCreated} courses!");
    }

    private function getCoursesData(): array
    {
        return [
            'Massachusetts Institute of Technology' => [
                ['code' => 'CS101', 'name' => 'Introduction to Computer Science', 'subject' => 'Computer Science', 'level' => 'Undergraduate', 'degree_type' => "Bachelor's", 'duration_months' => 48, 'study_mode' => 'Full-time', 'tuition_fee' => 53790, 'credits' => 120, 'language' => 'English', 'scholarships_available' => true, 'is_featured' => true, 'description' => 'Comprehensive CS foundation with programming, algorithms, and software development.', 'prerequisites' => ['High school diploma', 'Math proficiency'], 'learning_outcomes' => ['Master programming', 'Design algorithms', 'Solve real-world problems']],
                ['code' => 'AI601', 'name' => 'Artificial Intelligence', 'subject' => 'Computer Science', 'level' => 'Graduate', 'degree_type' => "Master's", 'duration_months' => 24, 'study_mode' => 'Full-time', 'tuition_fee' => 53790, 'credits' => 60, 'language' => 'English', 'scholarships_available' => true, 'is_featured' => true, 'description' => 'Advanced AI and machine learning techniques.', 'prerequisites' => ["Bachelor's in CS", 'Programming experience'], 'learning_outcomes' => ['AI algorithms', 'ML models', 'Deep learning']],
            ],
            'Stanford University' => [
                ['code' => 'CS106A', 'name' => 'Programming Methodology', 'subject' => 'Computer Science', 'level' => 'Undergraduate', 'degree_type' => "Bachelor's", 'duration_months' => 48, 'study_mode' => 'Full-time', 'tuition_fee' => 56169, 'credits' => 120, 'language' => 'English', 'scholarships_available' => true, 'is_featured' => true, 'description' => 'Introduction to programming and software engineering.', 'prerequisites' => ['High school diploma'], 'learning_outcomes' => ['Programming skills', 'Software design']],
                ['code' => 'MBA500', 'name' => 'Master of Business Administration', 'subject' => 'Business', 'level' => 'Graduate', 'degree_type' => 'MBA', 'duration_months' => 24, 'study_mode' => 'Full-time', 'tuition_fee' => 76950, 'credits' => 60, 'language' => 'English', 'scholarships_available' => true, 'is_featured' => true, 'description' => 'Elite MBA program for future business leaders.', 'prerequisites' => ["Bachelor's degree", 'Work experience'], 'learning_outcomes' => ['Strategic thinking', 'Leadership']],
            ],
            'University of Oxford' => [
                ['code' => 'LAW101', 'name' => 'Bachelor of Laws', 'subject' => 'Law', 'level' => 'Undergraduate', 'degree_type' => "Bachelor's", 'duration_months' => 36, 'study_mode' => 'Full-time', 'tuition_fee' => 28370, 'credits' => 120, 'language' => 'English', 'scholarships_available' => true, 'is_featured' => true, 'description' => 'Comprehensive legal education covering core principles.', 'prerequisites' => ['High school diploma', 'English proficiency'], 'learning_outcomes' => ['Legal analysis', 'Critical thinking']],
                ['code' => 'MBA600', 'name' => 'Master of Business Administration', 'subject' => 'Business', 'level' => 'Graduate', 'degree_type' => 'MBA', 'duration_months' => 12, 'study_mode' => 'Full-time', 'tuition_fee' => 66800, 'credits' => 60, 'language' => 'English', 'scholarships_available' => true, 'is_featured' => true, 'description' => 'Intensive MBA program with global perspective.', 'prerequisites' => ["Bachelor's degree", '3+ years experience'], 'learning_outcomes' => ['Business strategy', 'Global leadership']],
            ],
            // Add more universities with courses...
        ];
    }
}
