<?php

namespace Database\Factories;

use App\Models\CoursesType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoursesTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CoursesType::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_type_name' => $this->faker->word(),
        ];
    }
}
