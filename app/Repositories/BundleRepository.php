<?php

namespace App\Repositories;

use App\Models\Bundle;
use App\Repositories\BaseRepository;

/**
 * Class BundleRepository
 * @package App\Repositories
 * @version July 13, 2021, 8:15 am UTC
*/

class BundleRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'course_id'
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
        return Bundle::class;
    }
}
