<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Models\Course;
use App\Models\Product;
use App\Services\CourseCatalogService\CourseCatalogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Swagger\Annotations as SWG;

/**
 * Class CourseCatalogAPIController
 * @package App\Http\Controllers\API
 */

class CourseCatalogAPIController extends AppBaseController
{
    private $courseCatalogService;

    public function __construct(
        CourseCatalogServiceInterface $courseCatalogService
    ) {
        $this->courseCatalogService = $courseCatalogService;
    }

    /**
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/catalog/organizations",
     *      summary="Get catalog of course catagories.",
     *      tags={"CourseCategory"},
     *      description="Get CourseCategory array grouped by Organization with courses count",
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
     *                  @SWG\Items(
     *                      allOf={
     *                          @SWG\Schema(ref="#/definitions/Organization"),
     *                          @SWG\Schema(
     *                              type="object",
     *                              @SWG\Property(property="course_categories", type="object"),
     *                          )
     *                      }
     *                  )
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function getByOrganizations()
    {
        $categories = $this->courseCatalogService->getGroupedByOrganizations();

        return $this->sendResponse($categories->toArray(), 'Course catalog categories retrieved successfully');
    }

    /**
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/catalog/organizations/{organization_id}",
     *      summary="Get catalog of categories by specified organization.",
     *      tags={"CourseCategory"},
     *      description="Get Categories array grouped by Organization",
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
     *                  type="object",
     *                  ref="#/definitions/Organization"
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function getByOrganizationId(int $organizationId)
    {
        $categories = $this->courseCatalogService->getGroupedByOrganization($organizationId);

        return $this->sendResponse($categories->toArray(), 'Course catalog categories retrieved successfully');
    }

    /**
     * @return JsonResponse
     *
     * @SWG\Get(
     *      path="/catalog/organizations/{organization_id}/categories/{category_id}/courses",
     *      summary="Get catalog of courses by organization and catagory.",
     *      tags={"Course"},
     *      description="Get CourseCategory array grouped by Organization with courses count",
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
     *                  @SWG\Items(ref="#/definitions/Organization")
     *              ),
     *              @SWG\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
     *      )
     * )
     */
    public function getCourses(int $organizationId, int $courseCategoryId, Request $request)
    {
        $categoriesWithCourses = $this->courseCatalogService->getGroupedCourses($organizationId, $courseCategoryId, $request);

        return $this->sendResponse($categoriesWithCourses->toArray(), 'Course catalog categories retrieved successfully');
    }
}
