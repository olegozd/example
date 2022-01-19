<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Program;
use App\Models\ProgramCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramCourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProgramCourse::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => Course::factory()->create(),
            'program_id' => Program::factory()->create()
        ];
    }
}
