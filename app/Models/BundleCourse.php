<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @SWG\Definition(
 *      definition="BundleCourse",
 *      required={""},
 *      @SWG\Property(
 *          property="course_id",
 *          description="course_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="bundle_id",
 *          description="bundle_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="course_position",
 *          description="course_position",
 *          type="integer",
 *          format="int32"
 *      ),
 * )
 */
class BundleCourse extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;

    public $table = 'bundle_courses';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'course_id',
        'bundle_id',
        'course_position'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'course_id' => 'integer',
        'bundle_id' => 'integer',
        'course_position' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'bundle_id' => 'required',
        'course_id' => 'required',
        'course_position' => '',
    ];

    protected static $logOnlyDirty = true;

    public function attributesToBeIgnored(): array
    {
        return ['id'];
    }

    public function bundle()
    {
        return $this->hasOne(Bundle::class, 'id', 'bundle_id');
    }

    public function course()
    {
        return $this->hasOne(Course::class, 'id', 'course_id')->with(Course::RELATIONS_TO_LOAD_ON_CARD);
    }
}
