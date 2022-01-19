<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateCourseCategoryAPIRequest;
use App\Http\Requests\API\UpdateCourseCategoryAPIRequest;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Repositories\CourseCategoryRepository;
use App\Repositories\CourseRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\JsonResponse;
use Swagger\Annotations as SWG;

/**
 * Class CourseCategoryController
 * @package App\Http\Controllers\API
 */

class CourseCategoryAPIController extends AppBaseController
{
    /** @var  CourseCategoryRepository */
    private $courseCategoryRepository;
    /** @var  CourseRepository */
    private $courseRepository;

    public function __construct(
        CourseCategoryRepository $courseCategoryRepo,
        CourseRepository $courseRepository
    )
    {
        $this->courseCategoryRepository = $courseCategoryRepo;
        $this->courseRepository = $courseRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/course_categories",
     *      summary="Get a listing of the CourseCategories.",
     *      tags={"CourseCategory"},
     *      description="Get all CourseCategories",
     *      produces={"application/json"},
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="array",
     *                  @SWG\Items(ref="#/definitions/CourseCategory")
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $courseCategories = $this->courseCategoryRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($courseCategories->toArray(), 'Course Categories retrieved successfully');
    }

    /**
     * @param CreateCourseCategoryAPIRequest $request
     * @return JsonResponse
     *
     * @SWG\Post(
     *      path="/course_categories",
     *      summary="Store a newly created CourseCategory in storage",
     *      tags={"CourseCategory"},
     *      description="Store CourseCategory",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="CourseCategory that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/CourseCategory")
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/CourseCategory"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateCourseCategoryAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $courseCategory = $this->courseCategoryRepository->create($input);

        return $this->sendResponse($courseCategory->toArray(), 'Course Category saved successfully');
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/course_categories/{id}",
     *      summary="Display the specified CourseCategory",
     *      tags={"CourseCategory"},
     *      description="Get CourseCategory",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of CourseCategory",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/CourseCategory"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function show(int $id): JsonResponse
    {
        /** @var CourseCategory $courseCategory */
        $courseCategory = $this->courseCategoryRepository->find($id);

        if (empty($courseCategory)) {
            return $this->sendError('Course Category not found');
        }

        return $this->sendResponse($courseCategory->toArray(), 'Course Category retrieved successfully');
    }

    /**
     * @param int $courseId
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/courses/{course_id}/categories",
     *      summary="Display the specified CourseCategory",
     *      tags={"CourseCategory"},
     *      description="Get CourseCategory",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="course_id",
     *          description="id of Course",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/CourseCategory"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function getByCourseId(int $courseId): JsonResponse
    {
        /** @var Course $course */
        $course = $this->courseRepository->find($courseId);

        if (empty($course)) {
            return $this->sendError('Course not found');
        }

        $categories = $course->categories()->get();

        return $this->sendResponse($categories->toArray(), 'Course Categories retrieved successfully');
    }

    /**
     * @param int $id
     * @param UpdateCourseCategoryAPIRequest $request
     * @return JsonResponse
     *
     * @SWG\Put(
     *      path="/course_categories/{id}",
     *      summary="Update the specified CourseCategory in storage",
     *      tags={"CourseCategory"},
     *      description="Update CourseCategory",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of CourseCategory",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="CourseCategory that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/CourseCategory")
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  ref="#/definitions/CourseCategory"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update(int $id, UpdateCourseCategoryAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var CourseCategory $courseCategory */
        $courseCategory = $this->courseCategoryRepository->find($id);

        if (empty($courseCategory)) {
            return $this->sendError('Course Category not found');
        }

        $courseCategory = $this->courseCategoryRepository->update($input, $id);

        return $this->sendResponse($courseCategory->toArray(), 'CourseCategory updated successfully');
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Delete(
     *      path="/course_categories/{id}",
     *      summary="Remove the specified CourseCategory from storage",
     *      tags={"CourseCategory"},
     *      description="Delete CourseCategory",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of CourseCategory",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="successful operation",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @SWG\Property(
     *                  property="data",
     *                  type="string"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var CourseCategory $courseCategory */
        $courseCategory = $this->courseCategoryRepository->find($id);

        if (empty($courseCategory)) {
            return $this->sendError('Course Category not found');
        }

        $courseCategory->delete();

        return $this->sendSuccess('Course Category deleted successfully');
    }
}
