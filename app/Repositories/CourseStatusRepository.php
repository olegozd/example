<?php

namespace App\Repositories;

use App\Models\CourseStatus;
use App\Repositories\BaseRepository;

/**
 * Class CourseStatuseRepository
 * @package App\Repositories
 * @version May 26, 2021, 11:26 am UTC
*/

class CourseStatusRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'status_name'
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
        return CourseStatus::class;
    }

    public function findDraft(): CourseStatus
    {
        return $this->model
            ->where('status_name', CourseStatus::STATUS_DRAFT)
            ->first();
    }
}
