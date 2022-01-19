<?php

namespace App\Services\CourseService;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface CourseServiceInterface
{
    public function getTeacherCourses(int $teacherId, Request $request, int $statusId = null): Collection;
    public function getPurchasedCourses(int $userId, Request $request, bool $onlyEnrolled = false): Collection;
    public function create(array $data, User $user, string $structureType): Course;
    public function update(array $data, int $course, Request $request): Course;
}
