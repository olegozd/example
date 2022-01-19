<?php

namespace App\Repositories;

use App\Models\CourseToCategory;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class CourseToCategoryRepository
 * @package App\Repositories
 * @version June 29, 2021, 4:50 pm UTC
*/

class CourseToCategoryRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'course_id',
        'course_category_id'
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
        return CourseToCategory::class;
    }

    public function findBy(int $courseId, int $categoryId): ?CourseToCategory
    {
        return $this->model
            ->where('course_id', $courseId)
            ->where('course_category_id', $categoryId)
            ->first();
    }

    public function findByCourse(int $courseId): ?CourseToCategory
    {
        return $this->model
            ->where('course_id', $courseId)
            ->first();
    }

    public function updateByCourse(array $input, int $courseId): bool
    {
        return $this->model
            ->where('course_id', $courseId)
            ->update($input);
    }

    public function deleteBy(int $courseId, int $categoryId): bool
    {
        return $this->model
            ->where('course_id', $courseId)
            ->where('course_category_id', $categoryId)
            ->delete();
    }
}
