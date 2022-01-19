<?php

namespace Database\Seeders;

use App\Models\CourseCategory;
use Illuminate\Database\Seeder;

class CourseCategorySeeder extends Seeder
{
    const COURSE_CATEGORIES = [
        [
            'course_category_name' => 'Computer Science',
        ],
        [
            'course_category_name' => 'Front-End Development',
        ],
        [
            'course_category_name' => 'Ethical Hacking',
        ],
        [
            'course_category_name' => 'Graphic design',
        ],
        [
            'course_category_name' => 'Health',
        ],
        [
            'course_category_name' => 'Language',
        ],
        [
            'course_category_name' => 'Engineering & Construction',
        ],
        [
            'course_category_name' => 'Business',
        ],
        [
            'course_category_name' => 'Management',
        ],
        [
            'course_category_name' => 'Sales & Marketing',
        ],
        [
            'course_category_name' => 'Teaching & Academics',
        ],
        [
            'course_category_name' => 'Personal Development',
        ],

    ];

    public function run()
    {
        foreach (self::COURSE_CATEGORIES as $courseCategory) {
            CourseCategory::create($courseCategory);
        }
    }
}
