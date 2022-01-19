<?php

namespace App\Repositories;

use App\Models\CoursesType;
use App\Repositories\BaseRepository;

/**
 * Class CoursesTypeRepository
 * @package App\Repositories
 * @version May 20, 2021, 2:28 pm UTC
*/

class CoursesTypeRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'course_type_name'
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
        return CoursesType::class;
    }
}
