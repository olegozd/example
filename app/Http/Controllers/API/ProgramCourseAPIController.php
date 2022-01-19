<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateProgramCourseAPIRequest;
use App\Models\Course;
use App\Models\Media;
use App\Models\Program;
use App\Models\ProgramCourse;
use App\Repositories\ProgramCourseRepository;
use App\Repositories\ProgramRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Swagger\Annotations as SWG;

/**
 * Class ProgramController
 * @package App\Http\Controllers\API
 */

class ProgramCourseAPIController extends AppBaseController
{
    /** @var  ProgramCourseRepository */
    private $programCourseRepository;
    /** @var  ProgramRepository */
    private $programRepository;

    public function __construct(
        ProgramCourseRepository $programCourseRepository,
        ProgramRepository $programRepository
    )
    {
        $this->programCourseRepository = $programCourseRepository;
        $this->programRepository = $programRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/program_courses",
     *      summary="Get a listing of the Programs.",
     *      tags={"ProgramCourses"},
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
        $programCourses = $this->programCourseRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($programCourses->toArray(), 'Programs retrieved successfully');
    }

    /**
     * @param int $programId
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/programs/{program_id}/courses",
     *      summary="Display Courses list by the specified Program",
     *      tags={"Program"},
     *      description="Get Courses by Program",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="program_id",
     *          description="id of Program",
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
    public function getProgramCourses(int $programId): JsonResponse
    {
        $programCourses = $this->programCourseRepository->coursesByProgram($programId, 'course');

        if (empty($programCourses)) {
            return $this->sendError('ProgramCourses not found');
        }

        $collectionName = Course::MEDIA_COLLECTION_COVER_IMAGE;
        $courseIds = $programCourses->pluck('course.id')->all();
        $coursesMedia = Media::getMediaByModelIds($courseIds, $collectionName, Course::class);

        $programCourses->map(function (ProgramCourse $programCourse) use ($coursesMedia, $collectionName) {
            /** @var Course $course */
            $course = $programCourse['course'];
            $course[$collectionName] = $coursesMedia[$course->id][$collectionName] ?? null;
        });

        return $this->sendResponse($programCourses->toArray(), 'ProgramCourses retrieved successfully');
    }

    /**
     * @param CreateProgramCourseAPIRequest $request
     * @return JsonResponse
     *
     * @SWG\Post(
     *      path="/program_courses",
     *      summary="Store a course for Program in storage",
     *      tags={"ProgramCourse"},
     *      description="Store ProgramCourse",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Program that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/ProgramCourse")
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
     *                  ref="#/definitions/ProgramCourse"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateProgramCourseAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $programCourse = $this->programCourseRepository->create($input);

        return $this->sendResponse($programCourse->toArray(), 'Program course saved successfully');
    }

    /**
     * @param int $programId
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Put (
     *      path="/programs/{program_id}/courses",
     *      summary="Store courses for specified program",
     *      tags={"ProgramCourse"},
     *      description="Update Program",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="program_id",
     *          description="id of Program",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="Program that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/ProgramCourse")
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
     *                  @SWG\Items(ref="#/definitions/ProgramCourse")
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update(int $programId, Request $request): JsonResponse
    {
        $input = $request->all();

        $programCourses = collect();

        foreach ($input as $courseInfo) {
            $this->programCourseRepository->updateBy($courseInfo, $courseInfo['course_id'], $programId);

            /** @var ProgramCourse $programCourse */
            $programCourse = $this->programCourseRepository->findBy($courseInfo['course_id'], $programId);

            $programCourses->push($programCourse);
        }

        return $this->sendResponse($programCourses->toArray(), 'Program courses updated successfully');
    }

    /**
     * @param int $programId
     * @param int $courseId
     * @return JsonResponse
     *
     * @SWG\Delete(
     *      path="/programs/{program_id}/courses/{course_id}",
     *      summary="Remove the specified ProgramCourse from storage",
     *      tags={"ProgramCourse"},
     *      description="Delete ProgramCourse",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="program_id",
     *          description="id of Program",
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
    public function destroy(int $programId, int $courseId): JsonResponse
    {
        if (!$this->programCourseRepository->deleteBy($courseId, $programId)) {
            return $this->sendError('Cant delete program course');
        }

        return $this->sendSuccess('Program course deleted successfully');
    }
}
