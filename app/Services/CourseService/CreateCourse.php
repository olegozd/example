<?php

namespace App\Services\CourseService;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\CourseStructureType;
use App\Models\User;
use App\Repositories\Calendar\CalendarEventRepository;
use App\Repositories\Calendar\CalendarRepository;
use App\Repositories\Calendar\UserCalendarRepository;
use App\Repositories\CourseCategoryRepository;
use App\Repositories\CourseRepository;
use App\Repositories\TeacherCourseRepository;

class CreateCourse
{
    private $courseRepository;
    private $teacherCourseRepository;
    private $courseCategoryRepository;
    private $calendarRepo;
    private $userCalendarRepo;
    private $calendarEventRepo;

    public function __construct(
        CourseRepository $courseRepository,
        CourseCategoryRepository $courseCategoryRepository,
        TeacherCourseRepository $teacherCourseRepository,
        CalendarRepository $calendarRepo,
        UserCalendarRepository $userCalendarRepo,
        CalendarEventRepository $calendarEventRepo
    ) {
        $this->courseRepository = $courseRepository;
        $this->courseCategoryRepository = $courseCategoryRepository;
        $this->teacherCourseRepository = $teacherCourseRepository;
        $this->calendarRepo = $calendarRepo;
        $this->userCalendarRepo = $userCalendarRepo;
        $this->calendarEventRepo = $calendarEventRepo;
    }

    public function make(array $data, User $user, string $structureType): Course
    {
        $data['course_status_id'] = CourseStatus::DRAFT;
        $data['courses_access_code'] = uniqueCode();
        $data['course_uuid'] = uniqueCode();

        $data['course_structure_type_id'] = CourseStructureType::where('course_structure_slug', $structureType)->first()->id;

        /** @var Course $course */
        $course = $this->courseRepository->create($data);

        // TODO: add this later
//        if ($user->hasRole(Role::ROLE_TEACHER)) {
            $this->teacherCourseRepository->create([
                'teacher_id' => $user->id,
                'course_id' => $course->id,
            ]);
//        }

        if ($structureType === CourseStructureType::COURSE_TYPE) {
            $calendar = $this->calendarRepo->create([
                'course_id' => $course->id,
                'calendar_name' => $course->course_name,
                'calendar_color' => randomColor(),
                'calendar_is_show' => true,
            ]);

            if (!empty($calendar)) {
                $this->userCalendarRepo->create([
                    'user_id' => $user->id,
                    'calendar_id' => $calendar->id,
                    'can_edit' => false,
                ]);
                $this->calendarEventRepo->create([
                    'calendar_id' => $calendar->id,
                    'course_id' => $course->id,
                    'event_name' => $course->course_name,
                    'event_description' => $course->course_name,
                    'event_start_date' => $course->course_start_date,
                    'event_end_date' => $course->course_end_date,
                    'event_recur' => 0,
                    'event_is_all_day' => false,
                    'event_is_conference' => false,
                    'event_is_rsvp' => false,
                ]);
            }

            if (isset($data['course_teacher_id']) && $user->id != $data['course_teacher_id']) {
                $this->teacherCourseRepository->create([
                    'teacher_id' => $data['course_teacher_id'],
                    'course_id' => $course->id,
                ]);
                if (!empty($calendar)) {
                    $this->userCalendarRepo->create([
                        'user_id' => $data['course_teacher_id'],
                        'calendar_id' => $calendar->id,
                        'can_edit' => false,
                    ]);
                }
            }
        }

        $course->categories()->attach($data['course_category_id']);

        if (!isset($data['product_price']) || is_null($data['product_price'])) {
            $price = 0;
        } else {
            $price = floatval($data['product_price']) * 100;
        }
        $course->product()->create(['product_price' => $price]);

        return $course->refresh();
    }
}
