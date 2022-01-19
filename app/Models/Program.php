<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @SWG\Definition(
 *      definition="Program",
 *      required={""},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="course_id",
 *          description="course_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="course",
 *          ref="#/definitions/Course"
 *      )
 * )
 */
class Program extends Model
{
    use SoftDeletes, HasFactory, LogsActivity;

    public $table = 'programs';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'course_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'course_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    public function course()
    {
        return $this->hasOne(Course::class, 'id', 'course_id');
    }

    public function courses()
    {
        return $this->belongsToMany(
            Course::class,
            'program_courses',
            'program_id',
            'course_id');
    }
}
