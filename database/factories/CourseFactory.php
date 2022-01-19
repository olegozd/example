<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseStatus;
use App\Models\CourseStructureType;
use App\Models\CoursesType;
use App\Models\GradingScale\GradingScale;
use App\Models\Language;
use App\Models\Organization;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_name' => $this->faker->sentence(10),
            'course_organization_id' => Organization::inRandomOrder()->first(),
            'course_type_id' => CoursesType::inRandomOrder()->first(),
            'course_structure_type_id' => CourseStructureType::inRandomOrder()->first(),
            'course_subject_id' => Subject::inRandomOrder()->first(),
            'course_start_date' => $this->faker->date(),
            'course_end_date' => $this->faker->date(),
            'course_language_id' => Language::inRandomOrder()->first(),
            'course_timezone' => $this->faker->word(),
            'grading_scale_id' => GradingScale::inRandomOrder()->first(),
            'course_semester' => $this->faker->randomDigitNotNull(),
            'course_description' => $this->faker->word(),
            'course_credits' =>  $this->faker->randomFloat(2, null, 100),
            'course_status_id' => CourseStatus::where('status_name', CourseStatus::STATUS_DRAFT)->first(),
            'courses_access_code' => uniqueCode(),
            'course_uuid' => uniqueCode(),
        ];
    }
}
