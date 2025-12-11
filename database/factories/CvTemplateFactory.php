<?php

namespace Database\Factories;

use App\Models\CvTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CvTemplateFactory extends Factory
{
    protected $model = CvTemplate::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'thumbnail' => $this->faker->imageUrl(400, 600, 'business', true),
            'category' => $this->faker->randomElement(['Professional', 'Creative', 'Modern', 'Executive', 'Technical']),
            'is_premium' => $this->faker->boolean(30), // 30% premium
            'price' => function (array $attributes) {
                return $attributes['is_premium']
                    ? $this->faker->numberBetween(300, 1000)
                    : 0;
            },
            'color_scheme' => [
                'primary' => $this->faker->hexColor(),
                'secondary' => $this->faker->hexColor(),
                'accent' => $this->faker->hexColor(),
            ],
            'sections' => [
                'personal_info' => true,
                'professional_summary' => true,
                'education' => true,
                'experience' => true,
                'skills' => true,
                'languages' => true,
                'certifications' => true,
                'projects' => false,
                'references' => false,
            ],
            'download_count' => $this->faker->numberBetween(0, 500),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => true,
            'price' => $this->faker->numberBetween(500, 2000),
        ]);
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => false,
            'price' => 0,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
