<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateProgramAPIRequest;
use App\Models\Course;
use App\Models\CourseStructureType;
use App\Models\Module;
use App\Models\Program;
use App\Models\ProgramCourse;
use App\Repositories\ProgramCourseRepository;
use App\Repositories\ProgramRepository;
use App\Repositories\TeacherCourseRepository;
use App\Repositories\UserRepository;
use App\Services\CourseService\CourseServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Swagger\Annotations as SWG;

/**
 * Class ProgramController
 * @package App\Http\Controllers\API
 */

class ProgramAPIController extends AppBaseController
{
    private $courseService;
    /** @var  ProgramRepository */
    private $programRepository;
    /** @var  ProgramCourseRepository */
    private $programCourseRepository;
    /** @var  TeacherCourseRepository */
    private $teacherCourseRepository;

    public function __construct(
        ProgramRepository $programRepo,
        ProgramCourseRepository $programCourseRepository,
        CourseServiceInterface $courseService,
        TeacherCourseRepository $teacherCourseRepository
    )
    {
        $this->courseService = $courseService;
        $this->programRepository = $programRepo;
        $this->programCourseRepository = $programCourseRepository;
        $this->teacherCourseRepository = $teacherCourseRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/programs",
     *      summary="Get a listing of the Programs.",
     *      tags={"Program"},
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
        $programs = $this->programRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        )->load('course');

        return $this->sendResponse($programs->toArray(), 'Programs retrieved successfully');
    }

    /**
     * @param CreateProgramAPIRequest $request
     * @return JsonResponse
     *
     * @SWG\Post(
     *      path="/programs",
     *      summary="Store a newly created Program in storage",
     *      tags={"Program"},
     *      description="Store Program",
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
     *          description="Program that should be stored",
     *          required=true,
     *     @SWG\Schema(
     *          allOf={
     *              @SWG\Schema(ref="#/definitions/Course"),
     *              @SWG\Schema(
     *                  type="object",
     *                  @SWG\Property(
     *                      property="courses_array",
     *                      @SWG\Items(
     *                          type="object",
     *                          @SWG\Property(property="program_year", type="integer"),
     *                          @SWG\Property(
     *                              property="semesters",
     *                              @SWG\Items(
     *                                  type="object",
     *                                  @SWG\Property(property="program_semester", type="integer"),
     *                                  @SWG\Property(property="courses",
     *                                      @SWG\Items(
     *                                          type="object",
     *                                          @SWG\Property(property="program_id", type="integer"),
     *                                          @SWG\Property(property="course_id", type="integer"),
     *                                      )
     *                                  ),
     *                              )
     *                          ),
     *                      )
     *                  ),
     *              )
     *          }
     *     )
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
     *                  ref="#/definitions/Program"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateProgramAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $course = $this->courseService->create($input, $this->getAuthenticatedUser(), CourseStructureType::PROGRAM_TYPE);

        $program = $this->programRepository->create(['course_id' => $course->id]);

        $coursesArr = $request->get('courses_array');

        $this->programCourseRepository->createByCoursesArray($coursesArr, $program->id);

        foreach (Course::MEDIA_COLLECTIONS as $param => $collectionName) {
            if (!$request->file($param)) { continue; }

            $medias = $course->storeMedia($param, $collectionName, $request);

            $course[$param] = $course->responseArrFromMedias($medias);
        }

        $program['course'] = $program->course()->with(Course::RELATIONS_TO_LOAD_ON_SINGLE_PAGE)->first();

        return $this->sendResponse($program->toArray(), 'Program saved successfully');
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/programs/{program_id}",
     *      summary="Display the specified Program",
     *      tags={"Program"},
     *      description="Get Program",
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
     *                  ref="#/definitions/Program"
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
        /** @var Program $program */
        $program = $this->programRepository->find($id);

        if (empty($program)) {
            return $this->sendError('Program not found');
        }

        $course = $program->course()->with(Course::RELATIONS_TO_LOAD_ON_SINGLE_PAGE)->first();
        $courseMedia = $course->getMediaByModelIds($course->id, Course::MEDIA_COLLECTIONS);

        foreach (Course::MEDIA_COLLECTIONS as $param => $collectionName) {
            $course[$param] = $courseMedia[$course->id][$collectionName] ?? null;
        }

        $program['course'] = $course;

        $programCourses = $this->programCourseRepository->findByProgramId($program->id);
        $courseIds = $programCourses->pluck('course_id')->toArray();

        $program['teachers'] = $this->teacherCourseRepository->getTeachersByCourseIds($courseIds);
        $program['courses_array'] = $this->programCourseRepository->coursesFormattedForSinglePage($program->id);

        return $this->sendResponse($program->toArray(), 'Program retrieved successfully');
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Post (
     *      path="/programs/{program_id}",
     *      summary="Update the specified Program in storage",
     *      tags={"Program"},
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
     *          description="Program that should be updated",
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
     *                  ref="#/definitions/Program"
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

        /** @var Program $program */
        $program = $this->programRepository->find($id);

        if (empty($program)) {
            return $this->sendError('Program not found');
        }

        $course = $this->courseService->update($input, $program->course_id, $request);
        $course->load(Course::RELATIONS_TO_LOAD_ON_SINGLE_PAGE);
        $courseMedia = $course->getMediaByModelIds($course->id, Course::MEDIA_COLLECTIONS);

        foreach (Course::MEDIA_COLLECTIONS as $param => $collectionName) {
            $course[$param] = $courseMedia[$course->id][$collectionName] ?? null;
        }

        $program['course'] = $course;

        $this->programCourseRepository->deleteByProgram($id);

        $coursesArr = $request->get('courses_array');
        if (!is_null($coursesArr)) {
            $this->programCourseRepository->createByCoursesArray($coursesArr, $program->id);

            $program['courses_array'] = $this->programCourseRepository->coursesFormattedForFrontend($program->id);
        }

        return $this->sendResponse($program->toArray(), 'Program updated successfully');
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Delete(
     *      path="/programs/{program_id}",
     *      summary="Remove the specified Program from storage",
     *      tags={"Program"},
     *      description="Delete Program",
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
    public function destroy(int $id): JsonResponse
    {
        /** @var Program $program */
        $program = $this->programRepository->find($id);

        if (empty($program)) {
            return $this->sendError('Program not found');
        }

        $program->course()->delete();

        $this->programCourseRepository->deleteByProgram($id);

        $program->delete();

        return $this->sendSuccess('Program deleted successfully');
    }
}
