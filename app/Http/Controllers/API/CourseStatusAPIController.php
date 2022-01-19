<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateCourseStatusAPIRequest;
use App\Http\Requests\API\UpdateCourseStatusAPIRequest;
use App\Models\CourseStatus;
use App\Repositories\CourseStatusRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\JsonResponse;
use Swagger\Annotations as SWG;

/**
 * Class CourseStatusController
 * @package App\Http\Controllers\API
 */

class CourseStatusAPIController extends AppBaseController
{
    /** @var  CourseStatusRepository */
    private $courseStatusRepository;

    public function __construct(CourseStatusRepository $courseStatusRepo)
    {
        $this->courseStatusRepository = $courseStatusRepo;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/course_statuses",
     *      summary="Get a listing of the CourseStatuses.",
     *      tags={"CourseStatus"},
     *      description="Get all CourseStatuses",
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
     *                  @SWG\Items(ref="#/definitions/CourseStatus")
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
        $courseStatuses = $this->courseStatusRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($courseStatuses->toArray(), 'Course Statuses retrieved successfully');
    }

    /**
     * @param CreateCourseStatusAPIRequest $request
     * @return JsonResponse
     *
     * @SWG\Post(
     *      path="/course_statuses",
     *      summary="Store a newly created CourseStatus in storage",
     *      tags={"CourseStatus"},
     *      description="Store CourseStatus",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="CourseStatus that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/CourseStatus")
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
     *                  ref="#/definitions/CourseStatus"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateCourseStatusAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $courseStatus = $this->courseStatusRepository->create($input);

        return $this->sendResponse($courseStatus->toArray(), 'Course Status saved successfully');
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/course_statuses/{id}",
     *      summary="Display the specified CourseStatus",
     *      tags={"CourseStatus"},
     *      description="Get CourseStatus",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of CourseStatus",
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
     *                  ref="#/definitions/CourseStatus"
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
        /** @var CourseStatus $courseStatus */
        $courseStatus = $this->courseStatusRepository->find($id);

        if (empty($courseStatus)) {
            return $this->sendError('Course Status not found');
        }

        return $this->sendResponse($courseStatus->toArray(), 'Course Status retrieved successfully');
    }

    /**
     * @param int $id
     * @param UpdateCourseStatusAPIRequest $request
     * @return JsonResponse
     *
     * @SWG\Put(
     *      path="/course_statuses/{id}",
     *      summary="Update the specified CourseStatus in storage",
     *      tags={"CourseStatus"},
     *      description="Update CourseStatus",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of CourseStatus",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="CourseStatus that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/CourseStatus")
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
     *                  ref="#/definitions/CourseStatus"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update(int $id, UpdateCourseStatusAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var CourseStatus $courseStatus */
        $courseStatus = $this->courseStatusRepository->find($id);

        if (empty($courseStatus)) {
            return $this->sendError('Course Status not found');
        }

        $courseStatus = $this->courseStatusRepository->update($input, $id);

        return $this->sendResponse($courseStatus->toArray(), 'CourseStatus updated successfully');
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Delete(
     *      path="/course_statuses/{id}",
     *      summary="Remove the specified CourseStatus from storage",
     *      tags={"CourseStatus"},
     *      description="Delete CourseStatus",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of CourseStatus",
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
        /** @var CourseStatus $courseStatus */
        $courseStatus = $this->courseStatusRepository->find($id);

        if (empty($courseStatus)) {
            return $this->sendError('Course Status not found');
        }

        $courseStatus->delete();

        return $this->sendSuccess('Course Status deleted successfully');
    }
}
