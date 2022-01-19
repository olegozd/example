<?php

namespace App\Repositories;

use App\Models\CourseRule\CourseRuleAction;
use App\Repositories\BaseRepository;

/**
 * Class CourseRuleActionRepository
 * @package App\Repositories
 * @version October 28, 2021, 2:40 pm UTC
*/

class CourseRuleActionRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'course_rule_action_name',
        'course_rule_id',
        'action_id',
        'actionable_id',
        'actionable_type'
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
        return CourseRuleAction::class;
    }
}
