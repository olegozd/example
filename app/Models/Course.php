<?php

namespace App\Models;

use App\Models\GradingScale\GradingScale;
use App\Models\Resource\Resource;
use App\Traits\LogsActivity;
use App\Traits\MediaActions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *      definition="Course",
 *      required={""},
 *      @SWG\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="course_name",
 *          description="course_name",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="course_organization_id",
 *          description="course_organization_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="course_type_id",
 *          description="course_type_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="course_structure_type_id",
 *          description="course_structure_type_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="course_subject_id",
 *          description="course_subject_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="course_start_date",
 *          description="course_start_date",
 *          type="string",
 *          format="date"
 *      ),
 *      @SWG\Property(
 *          property="course_end_date",
 *          description="course_end_date",
 *          type="string",
 *          format="date"
 *      ),
 *      @SWG\Property(
 *          property="course_language_id",
 *          description="course_language_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="course_timezone",
 *          description="course_timezone",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="grading_scale_id",
 *          description="grading_scale_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="course_semester",
 *          description="course_semester",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="course_description",
 *          description="course_description",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="course_credits",
 *          description="course_credits",
 *          type="number",
 *          format="number"
 *      ),
 *      @SWG\Property(
 *          property="course_status_id",
 *          description="course_status_id",
 *          type="integer",
 *          format="int32"
 *      ),
 *      @SWG\Property(
 *          property="courses_access_code",
 *          description="courses_access_code",
 *          type="string"
 *      ),
 *      @SWG\Property(
 *          property="is_required_access_code",
 *          description="is_required_access_code",
 *          type="boolean"
 *      ),
 *      @SWG\Property(
 *          property="created_at",
 *          description="created_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @SWG\Property(
 *          property="updated_at",
 *          description="updated_at",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class Course extends Model implements HasMedia
{
    use SoftDeletes, HasFactory, LogsActivity, MediaActions;

    public const MEDIA_COLLECTION_COVER_IMAGE = 'course_cover_image';

    /**
     * Media collection names
     *
     * @var array
     */
    public const MEDIA_COLLECTIONS = [
        'course_cover_image' => self::MEDIA_COLLECTION_COVER_IMAGE,
        'course_content_image' => 'course_content_image',
        'course_content_files' => 'course_content_files',
    ];

    const RELATIONS_TO_LOAD_ON_CARD = [
        'structureType:id,course_structure_name',
        'subject:id,subject_name',
        'product:id,product_price,product_discount_price,productable_id,productable_type',
        'type:id,course_type_name',
        'teachers:id,user_first_name,user_last_name',
        'program:id,course_id',
        'bundle:id,course_id',
        'categories:id',
    ];

    const RELATIONS_TO_LOAD_ON_SINGLE_PAGE = [
        'structureType',
        'product',
        'type',
        'organization',
        'categories',
        'teachers'
    ];

    public $table = 'courses';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'course_name',
        'course_organization_id',
        'course_type_id',
        'course_structure_type_id',
        'course_subject_id',
        'course_start_date',
        'course_end_date',
        'course_language_id',
        'course_timezone',
        'grading_scale_id',
        'course_semester',
        'course_description',
        'course_credits',
        'course_status_id',
        'course_uuid',
        'courses_access_code',
        'is_required_access_code',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'course_organization_id' => 'integer',
        'course_type_id' => 'integer',
        'course_structure_type_id' => 'integer',
        'course_subject_id' => 'integer',
        'course_start_date' => 'date',
        'course_end_date' => 'date',
        'course_language_id' => 'integer',
        'grading_scale_id' => 'integer',
        'course_semester' => 'integer',
        'course_category_id' => 'integer',
        'course_teacher_id' => 'integer',
        'course_credits' => 'double',
        'course_description' => 'string',
        'course_status_id' => 'integer',
        'is_required_access_code' => 'boolean',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'course_name' => 'required',
        'course_organization_id' => 'exists:organizations,id',
        'course_type_id' => 'required|exists:courses_types,id',
        'course_subject_id' => 'required|exists:subjects,id',
        'course_language_id' => 'required|exists:languages,id',
        'grading_scale_id' => 'required|exists:grading_scales,id',
        'course_timezone' => 'required',
        'course_start_date' => 'required',
        'course_end_date' => 'required',
        'course_category_id' => 'required|exists:course_categories,id',
        'course_teacher_id' => 'required|exists:users,id',
        'product_price' => 'numeric|nullable',
    ];

    protected static $logAttributes = ['*'];

    protected static $logOnlyDirty = true;

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'course_organization_id');
    }

    public function type()
    {
        return $this->belongsTo(CoursesType::class, 'course_type_id');
    }

    public function structureType()
    {
        return $this->belongsTo(CourseStructureType::class, 'course_structure_type_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'course_subject_id');
    }

    public function categories()
    {
        return $this->belongsToMany(
            CourseCategory::class,
            'course_to_categories',
            'course_id',
            'course_category_id'
        );
    }

    public function category()
    {
        return $this->categories()->first();
    }

    public function students()
    {
        return $this->belongsToMany(
            User::class,
            'student_courses',
            'course_id',
            'user_id'
        )->where('approved_after_payment', true);
    }

    public function studentsEnrolled()
    {
        return $this->students()->whereNotNull('user_enrolled_date');
    }

    public function studentsPurchased()
    {
        return $this->students()->whereNull('user_enrolled_date');
    }

    public function teachers()
    {
        return $this->belongsToMany(
            User::class,
            'teacher_courses',
            'course_id',
            'teacher_id'
        )->distinct();
    }

    public function relatedCourses()
    {
        return $this->belongsToMany(
            Course::class,
            'related_courses',
            'related_course_id',
            'course_id'
        );
    }

    public function modulesWithItems()
    {
        return $this->hasMany(
            Module::class,
            'course_id',
            'id'
        )->with('moduleItems');
    }

    public function modules()
    {
        return $this->hasMany(
            Module::class,
            'course_id',
            'id'
        );
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'course_language_id');
    }

    public function gradingScale()
    {
        return $this->belongsTo(GradingScale::class, 'grading_scale_id');
    }

    public function status()
    {
        return $this->belongsTo(CourseStatus::class, 'course_status_id');
    }

    public function bundle()
    {
        return $this->hasOne(Bundle::class, 'course_id', 'id');
    }

    public function program()
    {
        return $this->hasOne(Program::class, 'course_id', 'id');
    }

    /**
     * Get the course's prices.
     */
    public function product()
    {
        return $this->morphOne(Product::class, 'productable');
    }

    /**
     * Get the competencies.
     */
    public function competencies()
    {
        return $this->morphToMany(Competency::class, 'competenciable');
    }

    /**
     * Get the resources.
     */
    public function resources()
    {
        return $this->morphToMany(Resource::class, 'resourceable');
    }

    public function registerMediaCollections(): void
    {
        foreach (self::MEDIA_COLLECTIONS as $param => $collectionName) {
            $this->addMediaCollection($collectionName)
                ->useDisk('s3_courses_media');
        }
    }
}
