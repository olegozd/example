<?php

namespace App\Services\CourseService;

use App\Facades\CreateCourse;
use App\Facades\UpdateCourse;
use App\Models\Course;
use App\Models\User;
use App\Repositories\CourseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CourseService implements CourseServiceInterface
{
    private $courseRepository;

    public function __construct(
        CourseRepository $courseRepository
    ) {
        $this->courseRepository = $courseRepository;
    }

    public function getTeacherCourses(int $teacherId, Request $request, int $statusId = null): Collection
    {
        $courses = $this->courseRepository->getTeacherCourses($teacherId, $request, $statusId);

        return $courses;
    }

    public function getPurchasedCourses(int $userId, Request $request, bool $onlyEnrolled = false): Collection
    {
        $courses = $this->courseRepository->getPurchasedCourses($userId, $request, $onlyEnrolled);

        return $courses;
    }

    public function create(array $data, User $user, string $structureType): Course
    {
        return CreateCourse::make($data, $user, $structureType);
    }

    public function update(array $data, int $course, Request $request): Course
    {
        return UpdateCourse::make($data, $course, $request);
    }
}
