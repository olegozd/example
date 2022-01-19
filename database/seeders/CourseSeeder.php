<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseStructureType;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class CourseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /** @var $courseCategories CourseCategory[]|Collection */
        $courseCategories = CourseCategory::inRandomOrder()->get();
        if ($courseCategories->isEmpty()) {
            return;
        }

        $teachers = User::query()
            ->whereHas('roles', function(Builder $query) {
                $query->where('name', Role::ROLE_TEACHER);
            })
            ->inRandomOrder()
            ->get();

        /** @var $courses Course[]|Collection */
        $courses = Course::factory()->count(30)->create();

        $structureTypes = [
            CourseStructureType::COURSE_TYPE,
            CourseStructureType::PROGRAM_TYPE,
            CourseStructureType::BUNDLE_TYPE
        ];

        foreach ($structureTypes as $structureType) {
            $structureTypeId = CourseStructureType::where('course_structure_slug', $structureType)->first()->id;

            $structureTypes[$structureType] = $structureTypeId;
        }

        foreach ($courses as $i => $course) {
            if ($course->course_structure_type_id == $structureTypes[CourseStructureType::PROGRAM_TYPE]) {
                $course->program()->create(['course_id' => $course->id]);
                $courseName = 'Program Title ' . ($i+1);
            } elseif ($course->course_structure_type_id == $structureTypes[CourseStructureType::BUNDLE_TYPE]) {
                $course->bundle()->create(['course_id' => $course->id]);
                $courseName = 'Bundle Title ' . ($i+1);
            } else {
                $courseName = 'Course Title ' . ($i+1);
            }

            $course->update(['course_name' => $courseName]);
            $course->categories()->attach($courseCategories->random());
            $course->teachers()->attach($teachers->random());
            $course->product()->create(['product_price' => rand(1000, 10000)]);
        }
    }
}
