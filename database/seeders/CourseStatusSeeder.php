<?php

namespace Database\Seeders;

use App\Models\CourseStatus;
use Illuminate\Database\Seeder;

class CourseStatusSeeder extends Seeder
{
    const COURSE_STATUSES = [
        [
            'status_name' => 'draft',
        ],
        [
            'status_name' => 'published',
        ],
        [
            'status_name' => 'unpublished',
        ],
    ];

    public function run()
    {
        foreach (self::COURSE_STATUSES as $courseStatus) {
            CourseStatus::create($courseStatus);
        }
    }
}
