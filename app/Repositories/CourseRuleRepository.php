<?php

namespace App\Repositories;

use App\Models\CourseRule\CourseRule;
use App\Repositories\BaseRepository;

/**
 * Class CourseRuleRepository
 * @package App\Repositories
 * @version October 28, 2021, 2:31 pm UTC
*/

class CourseRuleRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'course_id',
        'competency_id',
        'rule_name',
        'rule_days_at_least',
        'rule_first_sign',
        'rule_first_percent',
        'rule_second_sign',
        'rule_second_percent'
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
        return CourseRule::class;
    }

    public function getByCourse(int $courseId) {
        return $this->model
            ->where('course_id', $courseId)
            ->get();
    }
}
