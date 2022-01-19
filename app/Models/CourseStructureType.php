<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @SWG\Definition(
 *      definition="CourseStructureType",
 *      required={"course_structure_name", "course_structure_slug"},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="course_structure_name",
 *          description="course_structure_name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="course_structure_slug",
 *          description="course_structure_slug",
 *          type="string"
 *      )
 * )
 */
class CourseStructureType extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;

    const COURSE_TYPE = 'course';
    const PROGRAM_TYPE = 'program';
    const BUNDLE_TYPE = 'bundle';

    public $table = 'course_structure_types';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'course_structure_name',
        'course_structure_slug'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'course_structure_name' => 'string',
        'course_structure_slug' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'course_structure_name' => 'required',
        'course_structure_slug' => 'required'
    ];


    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;
}
