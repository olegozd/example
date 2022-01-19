<?php

namespace App\Repositories;

use App\Models\BundleCourse;
use App\Models\Course;
use App\Models\Media;
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;

/**
 * Class BundleCourseRepository
 * @package App\Repositories
 * @version July 13, 2021, 8:46 am UTC
*/

class BundleCourseRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'course_id',
        'bundle_id',
        'course_position',
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return BundleCourse::class;
    }

    public function findBy(int $courseId, int $bundleId): ?BundleCourse
    {
        return $this->model
            ->where('course_id', $courseId)
            ->where('bundle_id', $bundleId)
            ->first();
    }

    public function findByBundleId(int $bundleId): ?Collection
    {
        return $this->model
            ->where('bundle_id', $bundleId)
            ->get();
    }

    public function createByCoursesArray(array $coursesArr, int $bundleId): array
    {
        $formattedCourses = [];

        foreach ($coursesArr as $coursePos) {
            if (!isset($coursePos['course_id'], $coursePos['course_position'])) { continue; }

            $bundleCourseInput = [
                'bundle_id' => $bundleId,
                'course_id' => $coursePos['course_id'],
                'course_position' => $coursePos['course_position']
            ];
            // TODO change to insert()
            /** @var BundleCourse $bundleCourse */
            $bundleCourse = $this->create($bundleCourseInput);
            $tmpCourse = $bundleCourse->course()->first();
            $tmpCourse['course_position'] = $bundleCourse->course_position;

            $formattedCourses[] = $tmpCourse;
        }

        return $formattedCourses;
    }

    public function coursesByBundle(int $bundleId): Collection
    {
        return $this->model
            ->with('course')
            ->where('bundle_id', $bundleId)
            ->orderBy('course_position')
            ->get();
    }

    public function getCoursesFormattedForFrontend(int $bundleId): array {
        $courses = $this->coursesByBundle($bundleId);

        $finalArr = [];
        $courseIds = $courses->pluck('course.id');
        $collectionName = Course::MEDIA_COLLECTION_COVER_IMAGE;
        $coursesMedia = Media::getMediaByModelIds($courseIds, $collectionName, Course::class);

        $courses->map(function (BundleCourse $bundleCourse) use (&$finalArr, $coursesMedia, $collectionName) {
            $courseForBundle = $bundleCourse['course'];
            $courseForBundle['course_position'] = $bundleCourse->course_position;
            $courseForBundle[$collectionName] = $coursesMedia[$courseForBundle->id][$collectionName][0] ?? null;

            $finalArr[] = $courseForBundle;
        });

        return $finalArr;
    }

    public function updateBy(array $input, int $courseId, int $bundleId): bool
    {
        return $this->model
            ->where('course_id', $courseId)
            ->where('bundle_id', $bundleId)
            ->update($input);
    }

    public function deleteBy(int $courseId, int $bundleId): bool
    {
        return $this->model
            ->where('course_id', $courseId)
            ->where('bundle_id', $bundleId)
            ->forceDelete();
    }

    public function deleteByBundle(int $bundleId): bool
    {
        return $this->model
            ->where('bundle_id', $bundleId)
            ->forceDelete();
    }
}
