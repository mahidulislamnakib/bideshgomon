<?php

namespace Database\Seeders;

use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SkillsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create skill categories first
        $categories = [
            ['name' => 'Programming Languages', 'slug' => 'programming-languages', 'is_active' => true],
            ['name' => 'Web Development', 'slug' => 'web-development', 'is_active' => true],
            ['name' => 'Mobile Development', 'slug' => 'mobile-development', 'is_active' => true],
            ['name' => 'Database Management', 'slug' => 'database-management', 'is_active' => true],
            ['name' => 'DevOps & Cloud', 'slug' => 'devops-cloud', 'is_active' => true],
            ['name' => 'Design', 'slug' => 'design', 'is_active' => true],
            ['name' => 'Business', 'slug' => 'business', 'is_active' => true],
            ['name' => 'Marketing', 'slug' => 'marketing', 'is_active' => true],
            ['name' => 'Language Skills', 'slug' => 'language-skills', 'is_active' => true],
            ['name' => 'Soft Skills', 'slug' => 'soft-skills', 'is_active' => true],
        ];

        foreach ($categories as $categoryData) {
            SkillCategory::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }

        // Define skills by category
        $skills = [
            // Programming Languages
            'Programming Languages' => [
                'PHP', 'JavaScript', 'Python', 'Java', 'C++', 'C#', 'Ruby', 'Go',
                'Swift', 'Kotlin', 'TypeScript', 'Rust', 'Scala', 'R', 'MATLAB',
            ],

            // Web Development
            'Web Development' => [
                'HTML', 'CSS', 'React', 'Vue.js', 'Angular', 'Node.js', 'Laravel',
                'Django', 'Flask', 'Express.js', 'Next.js', 'Nuxt.js', 'jQuery',
                'Bootstrap', 'Tailwind CSS', 'WordPress', 'Shopify', 'REST API', 'GraphQL',
            ],

            // Mobile Development
            'Mobile Development' => [
                'Android Development', 'iOS Development', 'React Native', 'Flutter',
                'Xamarin', 'Ionic', 'SwiftUI', 'Jetpack Compose',
            ],

            // Database Management
            'Database Management' => [
                'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Oracle', 'SQL Server',
                'SQLite', 'Firebase', 'Elasticsearch', 'Cassandra', 'DynamoDB',
            ],

            // DevOps & Cloud
            'DevOps & Cloud' => [
                'AWS', 'Azure', 'Google Cloud', 'Docker', 'Kubernetes', 'Jenkins',
                'Git', 'GitHub', 'GitLab', 'CI/CD', 'Terraform', 'Ansible', 'Linux',
            ],

            // Design
            'Design' => [
                'Adobe Photoshop', 'Adobe Illustrator', 'Figma', 'Sketch', 'Adobe XD',
                'InDesign', 'UI/UX Design', 'Graphic Design', 'Web Design', '3D Modeling',
                'After Effects', 'Premiere Pro', 'Blender',
            ],

            // Business
            'Business' => [
                'Project Management', 'Business Analysis', 'Financial Analysis',
                'Strategic Planning', 'Risk Management', 'Quality Assurance',
                'Agile', 'Scrum', 'JIRA', 'Trello', 'Microsoft Office', 'Excel',
                'Data Analysis', 'Power BI', 'Tableau',
            ],

            // Marketing
            'Marketing' => [
                'Digital Marketing', 'SEO', 'SEM', 'Social Media Marketing',
                'Content Marketing', 'Email Marketing', 'Google Analytics',
                'Google Ads', 'Facebook Ads', 'Copywriting', 'Brand Management',
            ],

            // Language Skills
            'Language Skills' => [
                'English', 'Bengali', 'Hindi', 'Arabic', 'Spanish', 'French',
                'German', 'Chinese', 'Japanese', 'Korean', 'Portuguese', 'Russian',
            ],

            // Soft Skills
            'Soft Skills' => [
                'Communication', 'Leadership', 'Teamwork', 'Problem Solving',
                'Time Management', 'Critical Thinking', 'Adaptability', 'Creativity',
                'Emotional Intelligence', 'Negotiation', 'Public Speaking',
                'Customer Service', 'Conflict Resolution',
            ],
        ];

        foreach ($skills as $categoryName => $skillsList) {
            $category = SkillCategory::where('name', $categoryName)->first();

            if ($category) {
                foreach ($skillsList as $skillName) {
                    Skill::firstOrCreate(
                        ['slug' => Str::slug($skillName)],
                        [
                            'skill_category_id' => $category->id,
                            'name' => $skillName,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        $this->command->info('Skills and categories seeded successfully!');
    }
}
