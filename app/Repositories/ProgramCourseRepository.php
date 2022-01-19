<?php

namespace App\Repositories;

use App\Models\Course;
use App\Models\Media;
use App\Models\Program;
use App\Models\ProgramCourse;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class ProgramCourseRepository
 * @package App\Repositories
 * @version July 13, 2021, 8:34 am UTC
*/

class ProgramCourseRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'course_id',
        'program_id'
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
        return ProgramCourse::class;
    }

    public function createByCoursesArray(array $coursesArr, int $programId) {
        $resp = [];

        foreach ($coursesArr as $year) {
            if (!isset($year['program_year'])) { continue; }

            $programYear = $year['program_year'];

            foreach ($year['semesters'] as $semester) {
                if (!isset($semester['program_semester'])) { continue; }

                $programSemester = $semester['program_semester'];

                foreach ($semester['courses'] as $coursePos) {
                    if (!isset($coursePos['course_id'], $coursePos['course_position'])) { continue; }

                    $programCourseInput = [
                        'program_id' => $programId,
                        'course_id' => $coursePos['course_id'],
                        'course_position' => $coursePos['course_position'],
                        'program_year' => $programYear,
                        'program_semester' => $programSemester,
                    ];

                    $resp[] = $this->create($programCourseInput);
                }
            }
        }

        return $resp;
    }

    public function findBy(int $courseId, int $programId): ?ProgramCourse
    {
        return $this->model
            ->where('course_id', $courseId)
            ->where('program_id', $programId)
            ->first();
    }

    public function coursesByProgram(int $programId, $relations = []): Collection
    {
        return $this->model
            ->with($relations)
            ->where('program_id', $programId)
            ->orderBy('program_year')
            ->orderBy('program_semester')
            ->orderBy('course_position')
            ->get();
    }

    public function findByProgramId(int $programId): Collection
    {
        return $this->model
            ->where('program_id', $programId)
            ->get();
    }

    public function coursesFormattedForFrontend(int $programId): array {
        $courses = $this->coursesByProgram($programId, 'course');

        $courseIds = $courses->map(function ($course) use (&$courseIds) {
            return $course->course->id;
        })->toArray();
        if (count($courseIds) == 0) {
            return [];
        }
        $collectionName = Course::MEDIA_COLLECTION_COVER_IMAGE;
        $coursesMedia = Media::getMediaByModelIds($courseIds, $collectionName, Course::class);
        $preArray = [];

        $courses->map(function (ProgramCourse $programCourse) use ($coursesMedia, $collectionName, &$preArray) {
            $courseForProgram = $programCourse['course'];

            $courseForProgram[$collectionName] = $coursesMedia[$courseForProgram->id][$collectionName] ?? null;

            $courseForProgram['course_position'] = $programCourse->course_position;
            $preArray[$programCourse->program_year][$programCourse->program_semester][] = $courseForProgram;
        });

        $finalArr = [];

        foreach ($preArray as $year => $semesters) {
            $tmpYear = [];
            $tmpYear['program_year'] = $year;

            foreach ($semesters as $semester => $semesterCourses) {
                $tmpSemester = [];
                $tmpSemester['program_semester'] = $semester;
                $tmpSemester['courses'] = $semesterCourses;
                $tmpYear['semesters'][] = $tmpSemester;
            }
            $finalArr[] = $tmpYear;
        }

        return $finalArr;
    }


    public function coursesFormattedForSinglePage(int $programId): array {
        $programCourses = $this->coursesByProgram($programId, 'courseWithModules:id,course_name');

        $courseIds = $programCourses->pluck('course_id');
        if ($courseIds->isEmpty()) {
            return [];
        }
        $collectionName = Course::MEDIA_COLLECTION_COVER_IMAGE;
        $coursesMedia = Media::getMediaByModelIds($courseIds, $collectionName, Course::class);
        $preArray = [];

        foreach ($programCourses->toArray() as $programCourse) {
            $courseForProgram = $programCourse['course_with_modules'];

            $courseForProgram[$collectionName] = $coursesMedia[ $courseForProgram['id'] ][$collectionName] ?? null;

            $courseForProgram['course_position'] = $programCourse['course_position'];
            $preArray[ $programCourse['program_year'] ][ $programCourse['program_semester'] ][] = $courseForProgram;
        }

        $finalArr = [];

        foreach ($preArray as $year => $semesters) {
            $tmpYear = [];
            $tmpYear['program_year'] = $year;

            foreach ($semesters as $semester => $semesterCourses) {
                $tmpSemester = [];
                $tmpSemester['program_semester'] = $semester;
                $tmpSemester['courses'] = $semesterCourses;
                $tmpYear['semesters'][] = $tmpSemester;
            }
            $finalArr[] = $tmpYear;
        }

        return $finalArr;
    }

    public function updateBy(array $input, int $courseId, int $programId): bool
    {
        return $this->model
            ->where('course_id', $courseId)
            ->where('program_id', $programId)
            ->update($input);
    }

    public function deleteBy(int $courseId, int $programId): bool
    {
        return $this->model
            ->where('course_id', $courseId)
            ->where('program_id', $programId)
            ->forceDelete();
    }

    public function deleteByProgram(int $programId): bool
    {
        return $this->model
            ->where('program_id', $programId)
            ->delete();
    }
}
