<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseToCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseToCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseToCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => Course::factory()->create(),
            'course_category_id' => CourseCategory::inRandomOrder()->first(),
        ];
    }
}
