<?php

namespace Database\Factories;

use App\Models\CourseStructureType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseStructureTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CourseStructureType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_structure_name' => $this->faker->word,
            'course_structure_slug' => $this->faker->word
        ];
    }
}
