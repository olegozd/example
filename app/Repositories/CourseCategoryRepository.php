<?php

namespace App\Repositories;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class CourseCategoryRepository
 * @package App\Repositories
 * @version May 26, 2021, 11:45 am UTC
*/

class CourseCategoryRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'course_category_name'
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
        return CourseCategory::class;
    }

    public function getByOrganizationId($organizationIds, $withCourseCount = true): Collection
    {
        $categories = CourseCategory::query();

        $categories = $categories->whereHas('courses', function (Builder $query) use ($organizationIds) {
            if (is_array($organizationIds)) {
                $query->whereIn('course_organization_id', $organizationIds);
            } else {
                $query->where('course_organization_id', $organizationIds);
            }

            $query->where('course_status_id', CourseStatus::PUBLISHED);
            $query->distinct();
        });

        if ($withCourseCount) {
            $categories = $categories->withCount([
                'courses' => function (Builder $query) use ($organizationIds) {
                    if (is_array($organizationIds)) {
                        $query->whereIn('course_organization_id', $organizationIds);
                    } else {
                        $query->where('course_organization_id', $organizationIds);
                    }

                    $query->where('course_status_id', CourseStatus::PUBLISHED);
                    $query->distinct();
            }]);
        }

        return $categories->get();
    }

    /**
     * @param mixed $organizationIds
     * @param int $courseCategoryId
     * @param Request $request
     * @return CourseCategory|null
     */
    public function getCoursesByCategory($organizationIds, int $courseCategoryId, Request $request): ?CourseCategory
    {
        $category = CourseCategory::with([
            'courses' => function (BelongsToMany $query) use ($organizationIds, $request) {
                $limit     = $request->input('limit');
                $page      = $request->input('page');
                $search    = $request->except(['skip', 'page', 'limit', 'order', 'dir']);
                $orderBy   = $request->get('order');
                $direction = $request->get('dir');

                $query
                    ->with(Course::RELATIONS_TO_LOAD_ON_CARD)
                    //TODO add unique key(course_id,course_category_id) to course_to_category table
//                    ->distinct()
                ;

                if (is_array($organizationIds)) {
                    $query->whereIn('course_organization_id', $organizationIds);
                } else {
                    $query->where('course_organization_id', $organizationIds);
                }

                $query->where('course_status_id', CourseStatus::PUBLISHED);

                if (count($search)) {
                    foreach($search as $key => $value) {
                        if (!empty($value)) {
                            if ($key == 'course_name') {
                                $query->where($key, 'like', '%'.$value.'%');
                            } else {
                                if (strpos($value, ',') > 0) {
                                    $query->whereIn($key, explode(',', $value));
                                } else {
                                    $query->where($key, $value);
                                }
                            }
                        }
                    }
                }
                if (!empty($limit)) {
                    $query->limit($limit);
                    if (!empty($page)) {
                        $query->skip($limit * ($page - 1));
                    }
                }
                if (!is_null($orderBy)) {
                    if ($orderBy == 'product_price') {
                        $query->leftJoin('products as pr', 'pr.productable_id','=', 'courses.id');
                        if (!is_null($direction)) {
                            $query->orderBy('pr.'.$orderBy, $direction);
                        } else {
                            $query->orderBy('pr.'.$orderBy);
                        }
                    } else {
                        if (!is_null($direction)) {
                            $query->orderBy($orderBy, $direction);
                        } else {
                            $query->orderBy($orderBy);
                        }
                    }
                }
            }])
            ->whereHas('courses', function (Builder $query) use ($organizationIds) {
                $query
                    ->where('course_organization_id', $organizationIds)
                    ->distinct();
            });


        /** @var $category CourseCategory|null */
        return $category->find($courseCategoryId);
    }
}
