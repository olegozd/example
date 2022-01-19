<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateBundleCourseAPIRequest;
use App\Models\BundleCourse;
use App\Models\Course;
use App\Models\Media;
use App\Repositories\BundleCourseRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Swagger\Annotations as SWG;

/**
 * Class ProgramController
 * @package App\Http\Controllers\API
 */

class BundleCourseAPIController extends AppBaseController
{
    /** @var  BundleCourseRepository */
    private $bundleCourseRepository;

    public function __construct(
        BundleCourseRepository $bundleCourseRepository
    )
    {
        $this->bundleCourseRepository = $bundleCourseRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/bundle_courses",
     *      summary="Get a listing of the BundleCourses.",
     *      tags={"BundleCourse"},
     *      description="Get all Programs",
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
     *                  @SWG\Items(ref="#/definitions/Program")
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
        $bundleCourses = $this->bundleCourseRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($bundleCourses->toArray(), 'Bundle courses retrieved successfully');
    }

    /**
     * @param int $bundleId
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/bundles/{bundle_id}/courses",
     *      summary="Display Courses list by the specified Bundle",
     *      tags={"BundleCourse"},
     *      description="Get Courses by Bundle",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="bundle_id",
     *          description="id of Bundle",
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
     *                  @SWG\Items(ref="#/definitions/Course")
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function getBundleCourses(int $bundleId): JsonResponse
    {
        $bundleCourses = $this->bundleCourseRepository->coursesByBundle($bundleId);

        if (empty($bundleCourses)) {
            return $this->sendError('Bundle courses not found');
        }

        $collectionName = Course::MEDIA_COLLECTION_COVER_IMAGE;
        $courseIds = $bundleCourses->pluck('course.id')->toArray();

        $coursesMedia = Media::getMediaByModelIds($courseIds, $collectionName, Course::class);

        $bundleCourses->map(function (BundleCourse $bundleCourse) use ($coursesMedia, $collectionName){
            /** @var Course $course */
            $course = $bundleCourse['course'];
            $course[$collectionName] = $coursesMedia[$course->id][$collectionName] ?? null;
        });

        return $this->sendResponse($bundleCourses->toArray(), 'BundleCourses retrieved successfully');
    }

    /**
     * @param CreateBundleCourseAPIRequest $request
     * @return JsonResponse
     *
     * @SWG\Post(
     *      path="/bundle_courses",
     *      summary="Store a newly created ProgramCourse in storage",
     *      tags={"BundleCourse"},
     *      description="Store ProgramCourse",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Program that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/BundleCourse")
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
     *                  ref="#/definitions/BundleCourse"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateBundleCourseAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $bundleCourse = $this->bundleCourseRepository->create($input);

        return $this->sendResponse($bundleCourse->toArray(), 'Bundle course saved successfully');
    }

    /**
     * @param int $bundleId
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Put (
     *      path="/bundles/{bundle_id}/courses",
     *      summary="Update course positions by bundle",
     *      tags={"BundleCourse"},
     *      description="Update course positions",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="bundle_id",
     *          description="id of bundle",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Program that should be updated",
     *          required=false,
     *          @SWG\Schema(@SWG\Items(ref="#/definitions/BundleCourse"))
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
     *                  @SWG\Items(ref="#/definitions/BundleCourse")
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update(int $bundleId, Request $request): JsonResponse
    {
        $input = $request->all();

        $bundleCourses = collect();

        foreach ($input as $courseInfo) {
            $this->bundleCourseRepository->updateBy($courseInfo, $courseInfo['course_id'], $bundleId);

            /** @var BundleCourse $bundleCourse */
            $bundleCourse = $this->bundleCourseRepository->findBy($courseInfo['course_id'], $bundleId);

            $bundleCourses->push($bundleCourse);
        }

        return $this->sendResponse($bundleCourses->toArray(), 'BundleCourses updated successfully');
    }

    /**
     * @param int $bundleId
     * @param int $courseId
     * @return JsonResponse
     *
     * @SWG\Delete(
     *      path="/bundles/{bundle_id}/courses/{course_id}",
     *      summary="Remove the specified BundleCourse from storage",
     *      tags={"BundleCourse"},
     *      description="Delete BundleCourse",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="bundle_id",
     *          description="id of Bundle",
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
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function destroy(int $bundleId, int $courseId): JsonResponse
    {
        if (!$this->bundleCourseRepository->deleteBy($courseId, $bundleId)) {
            return $this->sendError('Cant delete bundle course');
        }

        return $this->sendSuccess('Bundle course deleted successfully');
    }
}
