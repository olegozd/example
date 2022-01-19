<?php

namespace App\Services\CourseService;

use App\Models\Course;
use App\Repositories\Calendar\CalendarEventRepository;
use App\Repositories\Calendar\CalendarRepository;
use App\Repositories\CourseRepository;
use App\Repositories\CourseToCategoryRepository;
use App\Repositories\TeacherCourseRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateCourse
{
    private $courseRepository;
    private $courseToCategoryRepository;
    private $teacherCourseRepository;
    private $calendarEventRepo;
    private $calendarRepo;

    public function __construct(
        CourseRepository $courseRepository,
        CourseToCategoryRepository $courseToCategoryRepository,
        TeacherCourseRepository $teacherCourseRepository,
        CalendarEventRepository $calendarEventRepo,
        CalendarRepository $calendarRepo
    ) {
        $this->courseRepository = $courseRepository;
        $this->courseToCategoryRepository = $courseToCategoryRepository;
        $this->teacherCourseRepository = $teacherCourseRepository;
        $this->calendarEventRepo = $calendarEventRepo;
        $this->calendarRepo = $calendarRepo;
    }

    public function make(array $data, int $courseId, Request $request): Course
    {
        $userId = Auth::id();
        /** @var Course $course */
        $course = $this->courseRepository->update($data, $courseId);
        $courseCategoryId = $data['course_category_id'] ?? 0;
        $courseTeacherId = $data['course_teacher_id'] ?? 0;
        $price = $data['product_price'] ?? 0;
        $discountPrice = $data['product_discount_price'] ?? 0;

        if ($courseCategoryId > 0) {
            $input = ['course_category_id' => $courseCategoryId];
            $this->courseToCategoryRepository->updateByCourse($input, $courseId);
        }

        if (is_numeric($courseTeacherId) && $courseTeacherId > 0) {
            $ids = $userId == $courseTeacherId ? [$courseTeacherId] : [$courseTeacherId, $userId];
            $course->teachers()->sync($ids);
        } elseif (is_array($courseTeacherId)) {
            if (!in_array($userId, $courseTeacherId)) {
                $courseTeacherId[] = $userId;
            }
            $course->teachers()->sync($courseTeacherId);
        }

        if ($price > 0 || $discountPrice > 0) {
            $product = $course->product;

            if ($price > 0) { $product->product_price = $price; }

            if ($discountPrice > 0) { $product->product_discount_price = $discountPrice; }

            $course->product()->save($product);
        }

        foreach (Course::MEDIA_COLLECTIONS as $param => $collectionName) {
            if (!$request->hasFile($param)) { continue; }

            $prevMedia = null;

            if ($param == Course::MEDIA_COLLECTION_COVER_IMAGE) {
                $prevMedia = $course->getFirstMedia($collectionName);
            }

            $medias = $course->storeMedia($param, $collectionName, $request);

            if (!empty($medias)) {
                if (!is_null($prevMedia)) {
                    $prevMedia->delete();
                }
            }
        }

        $eventInput = [];
        $calendarInput = [];

        if (isset($input['course_name'])) {
            $eventInput['event_name'] = $input['course_name'];
            $eventInput['event_description'] = $input['course_name'];
            $calendarInput['calendar_name'] = $input['course_name'];
        }
        if (isset($input['course_start_date'])) {
            $eventInput['event_start_date'] = new Carbon($input['course_start_date']);
        }
        if (isset($input['course_end_date'])) {
            $eventInput['event_end_date'] = new Carbon($input['course_end_date']);
        }

        if (!empty($calendarInput)) {
            $this->calendarRepo->updateByCourseId($calendarInput, $courseId);
        }
        if (!empty($eventInput)) {
            $this->calendarEventRepo->updateByCourseId($eventInput, $courseId);
        }

        return $course->refresh();
    }
}
