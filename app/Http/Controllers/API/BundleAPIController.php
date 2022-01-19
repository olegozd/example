<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateBundleAPIRequest;
use App\Models\Bundle;
use App\Models\Course;
use App\Models\CourseStructureType;
use App\Models\Media;
use App\Models\Module;
use App\Repositories\BundleCourseRepository;
use App\Repositories\BundleRepository;
use App\Repositories\TeacherCourseRepository;
use App\Services\CourseService\CourseServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Swagger\Annotations as SWG;

/**
 * Class BundleController
 * @package App\Http\Controllers\API
 */

class BundleAPIController extends AppBaseController
{
    /** @var  BundleRepository */
    private $bundleRepository;
    /** @var  BundleCourseRepository */
    private $bundleCourseRepository;
    /** @var  TeacherCourseRepository */
    private $teacherCourseRepository;
    private $courseService;

    public function __construct(
        BundleRepository $bundleRepo,
        BundleCourseRepository $bundleCourseRepository,
        CourseServiceInterface $courseService,
        TeacherCourseRepository $teacherCourseRepository
    )
    {
        $this->bundleRepository = $bundleRepo;
        $this->bundleCourseRepository = $bundleCourseRepository;
        $this->courseService = $courseService;
        $this->teacherCourseRepository = $teacherCourseRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/bundles",
     *      summary="Get a listing of the Bundles.",
     *      tags={"Bundle"},
     *      description="Get all Bundles",
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
     *                  @SWG\Items(ref="#/definitions/Bundle")
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
        $bundles = $this->bundleRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        )->load('course');

        return $this->sendResponse($bundles->toArray(), 'Bundles retrieved successfully');
    }

    /**
     * @param CreateBundleAPIRequest $request
     * @return JsonResponse
     *
     * @SWG\Post(
     *      path="/bundles",
     *      summary="Store a newly created Bundle in storage",
     *      tags={"Bundle"},
     *      description="Store Bundle",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="course_cover_image",
     *          in="formData",
     *          type="file",
     *          description="Course cover image that should be stored",
     *          required=false
     *      ),
     *      @SWG\Parameter(
     *          name="course_content_image[0]",
     *          in="formData",
     *          type="file",
     *          description="Course content image(s) that should be stored",
     *          required=false
     *      ),
     *      @SWG\Parameter(
     *          name="course_content_video[0]",
     *          in="formData",
     *          type="file",
     *          description="Course content video(s) that should be stored",
     *          required=false
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Bundle that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Course")
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
     *                  ref="#/definitions/Bundle"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateBundleAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $course = $this->courseService->create($input, $this->getAuthenticatedUser(), CourseStructureType::BUNDLE_TYPE);

        $bundle = $this->bundleRepository->create(['course_id' => $course->id]);

        foreach (Course::MEDIA_COLLECTIONS as $param => $collectionName) {
            if (!$request->file($param)) { continue; }

            $medias = $course->storeMedia($param, $collectionName, $request);

            $course[$param] = $course->responseArrFromMedias($medias);
        }

        $bundle['course'] = $bundle->course()->with(Course::RELATIONS_TO_LOAD_ON_SINGLE_PAGE)->first();

        $coursesArr = $request->get('courses_array');

        $bundle['courses_array'] = $this->bundleCourseRepository->createByCoursesArray($coursesArr, $bundle->id);

        return $this->sendResponse($bundle->toArray(), 'Bundle saved successfully');
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/bundles/{bundle_id}",
     *      summary="Display the specified Bundle",
     *      tags={"Bundle"},
     *      description="Get Bundle",
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
     *                  ref="#/definitions/Bundle"
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
        /** @var Bundle $bundle */
        $bundle = $this->bundleRepository->find($id);

        if (empty($bundle)) {
            return $this->sendError('Bundle not found');
        }
        /** @var Course $course */
        $course = $bundle->course()->with(Course::RELATIONS_TO_LOAD_ON_SINGLE_PAGE)->first();
        $courseMedia = $course->getMediaByModelIds($course->id, Course::MEDIA_COLLECTION_COVER_IMAGE);

        foreach (Course::MEDIA_COLLECTIONS as $param => $collectionName) {
            $course[$param] = $courseMedia[$course->id][$collectionName] ?? null;
        }

        $bundleCourses = $this->bundleCourseRepository->findByBundleId($bundle->id);
        $courseIds = $bundleCourses->pluck('course_id')->toArray();

        $bundle['teachers'] = $this->teacherCourseRepository->getTeachersByCourseIds($courseIds);
        $bundle['course'] = $course;

        $bundle['courses_array'] = $this->bundleCourseRepository->getCoursesFormattedForFrontend($bundle->id);

        return $this->sendResponse($bundle->toArray(), 'Bundle retrieved successfully');
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/bundles/{bundle_id}/modules",
     *      summary="Display the course names with modules by specified Bundle",
     *      tags={"Bundle"},
     *      description="Get Bundle",
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
     *                  ref="#/definitions/Bundle"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function getModules(int $id): JsonResponse
    {
        /** @var Bundle $bundle */
        $bundle = $this->bundleRepository->find($id);

        if (empty($bundle)) {
            return $this->sendError('Bundle not found');
        }

        $modules = $bundle->courses()->with('modulesWithItems')->get(['id', 'course_name']);
        $moduleIds = $modules->map(function ($module) use (&$moduleIds){
            return $module->id;
        });
        $collectionName = Module::MEDIA_COLLECTION_COVER_IMAGE;
        $moduleMedias = Media::getMediaByModelIds($moduleIds, $collectionName, Module::class);
        $modules->map(function ($module) use ($collectionName, $moduleMedias){
            $module[$collectionName] = $moduleMedias[$module->id][$collectionName] ?? null;
        });

        return $this->sendResponse($modules->toArray(), 'Bundle retrieved successfully');
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Post (
     *      path="/bundles/{bundle_id}",
     *      summary="Update the specified Bundle in storage",
     *      tags={"Bundle"},
     *      description="Update Bundle",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="bundle_id",
     *          description="id of Bundle",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="course_cover_image",
     *          in="formData",
     *          type="file",
     *          description="Course cover image that should be stored",
     *          required=false
     *      ),
     *      @SWG\Parameter(
     *          name="course_content_image[0]",
     *          in="formData",
     *          type="file",
     *          description="Course content image(s) that should be stored",
     *          required=false
     *      ),
     *      @SWG\Parameter(
     *          name="course_content_video[0]",
     *          in="formData",
     *          type="file",
     *          description="Course content video(s) that should be stored",
     *          required=false
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Bundle that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/Course")
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
     *                  ref="#/definitions/Bundle"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $input = $request->all();
        $coursesArr = $request->get('courses_array');

        /** @var Bundle $bundle */
        $bundle = $this->bundleRepository->find($id);

        if (empty($bundle)) {
            return $this->sendError('Bundle not found');
        }

        $this->courseService->update($input, $bundle->course_id, $request);
        /** @var Course $course */
        $course = $bundle->course()->with(Course::RELATIONS_TO_LOAD_ON_SINGLE_PAGE)->first();
        $courseMedia = $course->getMediaByModelIds($course->id, Course::MEDIA_COLLECTIONS);

        foreach (Course::MEDIA_COLLECTIONS as $param => $collectionName) {
            $course[$param] = $courseMedia[$course->id][$collectionName] ?? null;
        }

        $bundle['course'] = $course;

        $this->bundleCourseRepository->deleteByBundle($id);
        $this->bundleCourseRepository->createByCoursesArray($coursesArr, $bundle->id);

        $bundle['courses_array'] = $this->bundleCourseRepository->getCoursesFormattedForFrontend($bundle->id);

        return $this->sendResponse($bundle->toArray(), 'Bundle updated successfully');
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Delete(
     *      path="/bundles/{bundle_id}",
     *      summary="Remove the specified Bundle from storage",
     *      tags={"Bundle"},
     *      description="Delete Bundle",
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
    public function destroy(int $id): JsonResponse
    {
        /** @var Bundle $bundle */
        $bundle = $this->bundleRepository->find($id);

        if (empty($bundle)) {
            return $this->sendError('Bundle not found');
        }

        $bundle->course()->delete();

        $this->bundleCourseRepository->deleteByBundle($id);

        $bundle->delete();

        return $this->sendSuccess('Bundle deleted successfully');
    }
}
