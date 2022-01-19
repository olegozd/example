<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateCoursesTypeAPIRequest;
use App\Http\Requests\API\UpdateCoursesTypeAPIRequest;
use App\Models\CoursesType;
use App\Repositories\CoursesTypeRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\JsonResponse;
use Swagger\Annotations as SWG;

/**
 * Class CoursesTypeController
 * @package App\Http\Controllers\API
 */

class CoursesTypeAPIController extends AppBaseController
{
    /** @var  CoursesTypeRepository */
    private $coursesTypeRepository;

    public function __construct(CoursesTypeRepository $coursesTypeRepo)
    {
        $this->coursesTypeRepository = $coursesTypeRepo;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/course_types",
     *      summary="Get a listing of the CoursesTypes.",
     *      tags={"CoursesType"},
     *      description="Get all CoursesTypes",
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
     *                  @SWG\Items(ref="#/definitions/CoursesType")
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
        $coursesTypes = $this->coursesTypeRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($coursesTypes->toArray(), 'Courses Types retrieved successfully');
    }

    /**
     * @param CreateCoursesTypeAPIRequest $request
     * @return JsonResponse
     *
     * @SWG\Post(
     *      path="/course_types",
     *      summary="Store a newly created CoursesType in storage",
     *      tags={"CoursesType"},
     *      description="Store CoursesType",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="CoursesType that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/CoursesType")
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
     *                  ref="#/definitions/CoursesType"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateCoursesTypeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        $coursesType = $this->coursesTypeRepository->create($input);

        return $this->sendResponse($coursesType->toArray(), 'Courses Type saved successfully');
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/course_types/{id}",
     *      summary="Display the specified CoursesType",
     *      tags={"CoursesType"},
     *      description="Get CoursesType",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of CoursesType",
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
     *                  ref="#/definitions/CoursesType"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function show($id): JsonResponse
    {
        /** @var CoursesType $coursesType */
        $coursesType = $this->coursesTypeRepository->find($id);

        if (empty($coursesType)) {
            return $this->sendError('Courses Type not found');
        }

        return $this->sendResponse($coursesType->toArray(), 'Courses Type retrieved successfully');
    }

    /**
     * @param int $id
     * @param UpdateCoursesTypeAPIRequest $request
     * @return JsonResponse
     *
     * @SWG\Put(
     *      path="/course_types/{id}",
     *      summary="Update the specified CoursesType in storage",
     *      tags={"CoursesType"},
     *      description="Update CoursesType",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of CoursesType",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="CoursesType that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/CoursesType")
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
     *                  ref="#/definitions/CoursesType"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update(int $id, UpdateCoursesTypeAPIRequest $request): JsonResponse
    {
        $input = $request->all();

        /** @var CoursesType $coursesType */
        $coursesType = $this->coursesTypeRepository->find($id);

        if (empty($coursesType)) {
            return $this->sendError('Courses Type not found');
        }

        $coursesType = $this->coursesTypeRepository->update($input, $id);

        return $this->sendResponse($coursesType->toArray(), 'CoursesType updated successfully');
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Delete(
     *      path="/course_types/{id}",
     *      summary="Remove the specified CoursesType from storage",
     *      tags={"CoursesType"},
     *      description="Delete CoursesType",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of CoursesType",
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
        /** @var CoursesType $coursesType */
        $coursesType = $this->coursesTypeRepository->find($id);

        if (empty($coursesType)) {
            return $this->sendError('Courses Type not found');
        }

        $coursesType->delete();

        return $this->sendSuccess('Courses Type deleted successfully');
    }
}
