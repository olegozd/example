<?php

namespace App\Repositories;

use App\Models\CourseStructureType;
use App\Repositories\BaseRepository;

/**
 * Class CourseStructureTypeRepository
 * @package App\Repositories
 * @version June 22, 2021, 2:08 pm UTC
*/

class CourseStructureTypeRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'course_structure_name',
        'course_structure_slug'
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
        return CourseStructureType::class;
    }

    public function findCourse(): CourseStructureType
    {
        return $this->model
            ->where('course_structure_slug', CourseStructureType::COURSE_TYPE)
            ->first();
    }
}
