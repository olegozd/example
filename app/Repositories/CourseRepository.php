<?php

namespace App\Repositories;

use App\Enums\CourseStatus;
use App\Enums\StructureType;
use App\Models\Course;
use App\Models\CourseStructureType;
use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class CourseRepository
 * @package App\Repositories
 * @version May 26, 2021, 11:53 am UTC
*/

class CourseRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'course_name',
        'course_organization_id',
        'course_structure_type_id',
        'course_type_id',
        'course_subject_id',
        'course_start_date',
        'course_end_date',
        'course_language_id',
        'course_timezone',
        'grading_scale_id',
        'course_semester',
        'course_description',
        'course_credits',
        'course_status_id',
        'courses_access_code',
        'is_required_access_code',
    ];

    /**
     * @var array
     */
    public $fieldsForCard = [
        'courses.id',
        'course_name',
        'course_start_date',
        'course_end_date',
        'course_organization_id',
        'course_structure_type_id',
        'course_type_id',
        'course_subject_id',
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
        return Course::class;
    }

    public function getCoursesByRequest($courseStructureType, Request $request): Collection
    {
        $search    = $request->except(['skip', 'page', 'limit', 'order', 'dir']);

        $query = $this->model->newQuery();

        if (count($search)) {
            foreach($search as $key => $value) {
                if (in_array($key, $this->getFieldsSearchable()) && !empty($value)) {
                    $query->where($key, $value);
                }
            }
        }
        $this->limitToQueryByRequest($query, $request);
        $this->orderToQueryByRequest($query, $request);

        $courseStructureTypeId = CourseStructureType::where('course_structure_slug', $courseStructureType)->first()->id;
        $query->where('course_structure_type_id', '=', $courseStructureTypeId);

        $query->with(Course::RELATIONS_TO_LOAD_ON_CARD);
        $query->with('organization:id,organization_name');

        return $query->get();
    }

    public function getCoursesByOrganization($organizationId, Request $request): Collection
    {
        $query = $this->queryByRequest($request);

        if (!is_null($search = $request->input('search'))) {
            $query->whereRaw('LOWER(`courses`.`course_name`) like ' . "LOWER('%{$search}%')");
        }

        if (($orderBy = $request->input('order')) == 'product_price') {
            $query->join('products as pr', 'pr.productable_id','=', 'courses.id');

            if ($request->has('dir')) {
                $query->reorder('pr.'.$orderBy, $request->input('dir'));
            } else {
                $query->reorder('pr.'.$orderBy);
            }
        } elseif (empty($orderBy)) {
            $query->orderByDesc('id');
        }

        $query->where('course_organization_id', $organizationId);
        $query->with(Course::RELATIONS_TO_LOAD_ON_CARD);

        return $query->get($this->fieldsForCard);
    }

    public function getPurchasedCourses(int $userId, Request $request, bool $onlyEnrolled = false): Collection
    {
        $limit       = $request->input('limit');
        $page        = $request->input('page');
        $search      = $request->except(['skip', 'page', 'limit', 'order', 'dir']);
        $orderBy     = $request->get('order');
        $direction   = $request->get('dir');

        $courses     = $this->model->with(Course::RELATIONS_TO_LOAD_ON_CARD);
        $courses->leftJoin('student_courses as sc', 'sc.course_id', 'courses.id');
        $courses->leftJoin('student_course_statuses as scs', 'scs.id', 'sc.student_course_status_id');
        $courses->where('sc.user_id', $userId);
        $courses->where('sc.approved_after_payment', true);

        if ($onlyEnrolled) {
            $courses->whereNotNull('sc.user_enrolled_date');
        } else {
            $courses->whereNull('sc.user_enrolled_date');
        }

        if (!empty($search)) {
            foreach($search as $key => $value) {
                if (in_array($key, $this->getFieldsSearchable()) && !empty($value)) {
                    if ($key == 'course_name') {
                        $courses->where($key, 'like', '%'.$value.'%');
                    } else {
                        if (strpos($value, ',') > 0) {
                            $courses->whereIn($key, explode(',', $value));
                        } else {
                            $courses->where($key, $value);
                        }
                    }
                }
            }
        }

        if (!empty($limit)) {
            $courses->limit($limit);
            if (!empty($page)) {
                $courses->skip($limit * ($page - 1));
            }
        }

        if (!is_null($orderBy)) {
            if ($orderBy == 'product_price') {
                $courses->join('products as pr', 'pr.productable_id','=', 'courses.id');

                if (!is_null($direction)) {
                    $courses->orderBy('pr.'.$orderBy, $direction);
                } else {
                    $courses->orderBy('pr.'.$orderBy);
                }
            } else {
                if (!is_null($direction)) {
                    $courses->orderBy('courses.'.$orderBy, $direction);
                } else {
                    $courses->orderBy('courses.'.$orderBy);
                }
            }
        }

        $fields = $this->fieldsForCard;
        $fields = arrayReplaceValue($fields,'id', 'courses.id');
        $fields[] = 'scs.status_name';

        return $courses->get($fields);
    }

    public function getTeacherCourses(int $teacherId, Request $request, int $statusId = null): Collection
    {
        $limit       = $request->input('limit');
        $page        = $request->input('page');
        $search    = $request->except(['skip', 'page', 'limit', 'order', 'dir']);
        $orderBy   = $request->get('order');
        $direction = $request->get('dir');

        $courses = $this->model->with(Course::RELATIONS_TO_LOAD_ON_CARD);
        $courses->leftJoin('teacher_courses as tc', 'tc.course_id', 'courses.id');
        $courses->where('tc.teacher_id', $teacherId);

        if (!empty($statusId)) {
            if ($statusId == CourseStatus::DRAFT) {
                $courses->whereIn('course_status_id', [CourseStatus::DRAFT, CourseStatus::UNPUBLISHED]);
            } else {
                $courses->where('course_status_id', $statusId);
            }
        }

        if (!empty($search)) {
            foreach($search as $key => $value) {
                if (in_array($key, $this->getFieldsSearchable()) && !empty($value)) {
                    if ($key == 'course_name') {
                        $courses->where($key, 'like', '%'.$value.'%');
                    } else {
                        if (strpos($value, ',') > 0) {
                            $courses->whereIn($key, explode(',', $value));
                        } else {
                            $courses->where($key, $value);
                        }
                    }
                }
            }
        }

        if (!empty($limit)) {
            $courses->limit($limit);
            if (!empty($page)) {
                $courses->skip($limit * ($page - 1));
            }
        }

        if (!is_null($orderBy)) {
            if ($orderBy == 'product_price') {
                $courses->join('products as pr', 'pr.productable_id','=', 'courses.id');
                if (!is_null($direction)) {
                    $courses->orderBy('pr.'.$orderBy, $direction);
                } else {
                    $courses->orderBy('pr.'.$orderBy);
                }
            } else {
                if (!is_null($direction)) {
                    $courses->orderBy('courses.'.$orderBy, $direction);
                } else {
                    $courses->orderBy('courses.'.$orderBy);
                }
            }
        }

        return $courses->get($this->fieldsForCard);
    }

    public function getIdsByOrganizationAndDates(string $organizationId, string $firstDate = null, string $lastDate = null): Collection {
        $query = $this->model->newQuery();
        $query->where('course_organization_id', $organizationId);

        if (!is_null($firstDate) && !is_null($lastDate)) {
            $query->whereRaw("(
            (`course_start_date` BETWEEN '{$firstDate}' AND '{$lastDate}')
            OR (`course_end_date` BETWEEN '{$firstDate}' AND '{$lastDate}')
            )");
        }

        return $query->get(['id']);
    }

    public function addMediaByCollectionNameToCourses(Collection $courses, string $mediaCollection = ''): Collection
    {
        $courseIds = $courses->pluck('id')->all();
        $courseMedia = Media::getMediaByModelIds($courseIds, $mediaCollection, Course::class);

        $courses->map(function ($course) use ($courseMedia, $mediaCollection) {
            $course[$mediaCollection] = $courseMedia[$course->id][$mediaCollection] ?? null;
        });

        return $courses;
    }

    public function getCoursesCountByOrganizationIds($organizationIds): int
    {
        $query = $this->model->newQuery();

        if (is_array($organizationIds)) {
            $query->whereIn('course_organization_id', $organizationIds);
        } else {
            $query->where('course_organization_id', $organizationIds);
        }

        return $query
            ->where('course_structure_type_id', StructureType::COURSE)
            ->count();
    }
}
