<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\CreateCourseStructureTypeAPIRequest;
use App\Http\Requests\API\UpdateCourseStructureTypeAPIRequest;
use App\Models\CourseStructureType;
use App\Repositories\CourseStructureTypeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;

/**
 * Class CourseStructureTypeController
 * @package App\Http\Controllers\API
 */

class CourseStructureTypeAPIController extends AppBaseController
{
    /** @var  CourseStructureTypeRepository */
    private $courseStructureTypeRepository;

    public function __construct(CourseStructureTypeRepository $courseStructureTypeRepo)
    {
        $this->courseStructureTypeRepository = $courseStructureTypeRepo;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/course_structure_types",
     *      summary="Get a listing of the CourseStructureTypes.",
     *      tags={"CourseStructureType"},
     *      description="Get all CourseStructureTypes",
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
     *                  @SWG\Items(ref="#/definitions/CourseStructureType")
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        $courseStructureTypes = $this->courseStructureTypeRepository->all(
            $request->except(['skip', 'limit']),
            $request->get('skip'),
            $request->get('limit')
        );

        return $this->sendResponse($courseStructureTypes->toArray(), 'Course Structure Types retrieved successfully');
    }

    /**
     * @param CreateCourseStructureTypeAPIRequest $request
     * @return JsonResponse
     *
     * @SWG\Post(
     *      path="/course_structure_types",
     *      summary="Store a newly created CourseStructureType in storage",
     *      tags={"CourseStructureType"},
     *      description="Store CourseStructureType",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="CourseStructureType that should be stored",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/CourseStructureType")
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
     *                  ref="#/definitions/CourseStructureType"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function store(CreateCourseStructureTypeAPIRequest $request)
    {
        $input = $request->all();

        $courseStructureType = $this->courseStructureTypeRepository->create($input);

        return $this->sendResponse($courseStructureType->toArray(), 'Course Structure Type saved successfully');
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/course_structure_types/{id}",
     *      summary="Display the specified CourseStructureType",
     *      tags={"CourseStructureType"},
     *      description="Get CourseStructureType",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of CourseStructureType",
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
     *                  ref="#/definitions/CourseStructureType"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function show($id)
    {
        /** @var CourseStructureType $courseStructureType */
        $courseStructureType = $this->courseStructureTypeRepository->find($id);

        if (empty($courseStructureType)) {
            return $this->sendError('Course Structure Type not found');
        }

        return $this->sendResponse($courseStructureType->toArray(), 'Course Structure Type retrieved successfully');
    }

    /**
     * @param int $id
     * @param UpdateCourseStructureTypeAPIRequest $request
     * @return JsonResponse
     *
     * @SWG\Put(
     *      path="/course_structure_types/{id}",
     *      summary="Update the specified CourseStructureType in storage",
     *      tags={"CourseStructureType"},
     *      description="Update CourseStructureType",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of CourseStructureType",
     *          type="integer",
     *          required=true,
     *          in="path"
     *      ),
     *      @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          description="CourseStructureType that should be updated",
     *          required=false,
     *          @SWG\Schema(ref="#/definitions/CourseStructureType")
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
     *                  ref="#/definitions/CourseStructureType"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function update($id, UpdateCourseStructureTypeAPIRequest $request)
    {
        $input = $request->all();

        /** @var CourseStructureType $courseStructureType */
        $courseStructureType = $this->courseStructureTypeRepository->find($id);

        if (empty($courseStructureType)) {
            return $this->sendError('Course Structure Type not found');
        }

        $courseStructureType = $this->courseStructureTypeRepository->update($input, $id);

        return $this->sendResponse($courseStructureType->toArray(), 'CourseStructureType updated successfully');
    }

    /**
     * @param int $id
     * @return JsonResponse
     *
     * @SWG\Delete(
     *      path="/course_structure_types/{id}",
     *      summary="Remove the specified CourseStructureType from storage",
     *      tags={"CourseStructureType"},
     *      description="Delete CourseStructureType",
     *      produces={"application/json"},
     *      @SWG\Parameter(
     *          name="id",
     *          description="id of CourseStructureType",
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
    public function destroy($id)
    {
        /** @var CourseStructureType $courseStructureType */
        $courseStructureType = $this->courseStructureTypeRepository->find($id);

        if (empty($courseStructureType)) {
            return $this->sendError('Course Structure Type not found');
        }

        $courseStructureType->delete();

        return $this->sendSuccess('Course Structure Type deleted successfully');
    }
}
