<?php

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;

use App\Http\Controllers\API\{AssessmentAPIController,
    AssessmentTypeAPIController,
    BundleAPIController,
    BundleCourseAPIController,
    Calendar\CalendarAPIController,
    Calendar\CalendarEventAPIController,
    Chat\ChatAPIController,
    Chat\ChatFoldersAPIController,
    Chat\ChatMessageAPIController,
    Competency\AssessmentCompetencyAPIController,
    Competency\CourseCompetencyAPIController,
    Competency\ResourceCompetencyAPIController,
    ContactAPIController,
    CourseRule\CourseRuleActionAPIController,
    CourseRule\CourseRuleAPIController,
    Dashboard\DashboardAPIController,
    Dashboard\UserDashboardWidgetAPIController,
    FilesLibrary\AvatarsAPIController,
    Forms\CompetencyFormsAPIController,
    Forms\FormsAPIController,
    ForumAPIController,
    GradingScaleItemAPIController,
    JobTitlesAPIController,
    LessonAPIController,
    NewsAPIController,
    NotificationAPIController,
    OrganizationInvitationAPIController,
    OrganizationInvitationTemplatesAPIController,
    PostAPIController,
    ProgramAPIController,
    ProgramCourseAPIController,
    Resource\CourseResourceAPIController,
    Resource\ResourceAPIController,
    Resource\ResourceTypeAPIController,
    SubjectAPIController,
    CoursesTypeAPIController,
    OrganizationTypeAPIController,
    CountryAPIController,
    OrganizationAPIController,
    PermissionAPIController,
    RoleAPIController,
    LanguageAPIController,
    GradingScaleAPIController,
    CourseStatusAPIController,
    ModuleAPIController,
    ModuleItemAPIController,
    ModuleItemTypeAPIController,
    CompetencyAPIController,
    Auth\AuthController,
    Auth\VerifyEmailController,
    CourseAPIController,
    CourseCategoryAPIController,
    OrganizationCategoryAPIController,
    StudentCourseAPIController,
    SublessonAPIController,
    TeacherCourseAPIController,
    Competency\ModuleCompetencyAPIController,
    UserFriend\UserFriendAPIController,
    UserFriend\UserFriendshipStatusAPIController,
    User\TeacherAPIController,
    User\UserAPIController,
    User\UserBulkActionsAPIController,
    User\UserOrganizationAPIController,
    Group\GroupVisibilityAPIController,
    Group\GroupTypeAPIController,
    Group\GroupAPIController,
    Group\GroupUserAPIController,
    CourseStructureTypeAPIController,
    CourseCatalogAPIController,
    RelatedCourseAPIController,
    MyCoursesAPIController,
    CartAPIController};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['subdomain'])->group(function () {

    Route::post('stripeWebHook', [CartAPIController::class, 'stripeWebHook'])->name('stripeWebHook');

    Route::group(['namespace' => 'auth'], function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify_email'])->name('verification.verify');
        Route::post('password/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::get('password/reset/{token}', [AuthController::class, 'redirectToFront'])->name('password.reset');
        Route::post('password/reset', [AuthController::class, 'passwordReset']);

    });

    Route::middleware(['activity', 'auth:api'])->group(function () {
        Route::group(['middleware' => ['role:Super Admin|Platform Provider|Teacher']], function (Router $route) {
            $route->post('programs', [ProgramAPIController::class, 'store']);
            $route->post('programs/{program_id}', [ProgramAPIController::class, 'update']);
            $route->delete('programs/{program_id}', [ProgramAPIController::class, 'destroy']);

            $route->put('programs/{program_id}/courses', [ProgramCourseAPIController::class, 'update']);
            $route->delete('programs/{program_id}/courses/{course_id}', [ProgramCourseAPIController::class, 'destroy']);

            $route->post('program_courses', [ProgramCourseAPIController::class, 'store']);

            $route->post('bundles', [BundleAPIController::class, 'store']);
            $route->post('bundles/{bundle_id}', [BundleAPIController::class, 'update']);
            $route->delete('bundles/{bundle_id}', [BundleAPIController::class, 'destroy']);

            $route->put('bundles/{bundle_id}/courses', [BundleCourseAPIController::class, 'update']);
            $route->delete('bundles/{bundle_id}/courses/{course_id}', [BundleCourseAPIController::class, 'destroy']);
            $route->post('bundle_courses', [BundleCourseAPIController::class, 'store']);

            $route->get('courses/', [CourseAPIController::class, 'getCoursesList']);
            $route->post('courses/', [CourseAPIController::class, 'store']);
            $route->post('courses/{course_id}', [CourseAPIController::class, 'update']);
            $route->delete('courses/{id}', [CourseAPIController::class, 'destroy']);
            $route->delete('courses/{course_id}/media/{media_id}', [CourseAPIController::class, 'destroyMedia']);

            $route->get('courses/{course_id}/teachers', [TeacherCourseAPIController::class, 'getTeachersByCourseId']);
            $route->get('courses/{course_id}/students', [StudentCourseAPIController::class, 'getStudentsByCourse']);
            $route->get('courses/{course_id}/user_roles_count', [CourseAPIController::class, 'getRolesCountByCourse']);

            $route->get('courses/{course_id}/related', [RelatedCourseAPIController::class, 'show']);
            $route->post('courses/{course_id}/related/{related_course_id}', [RelatedCourseAPIController::class, 'store']);
            $route->delete('courses/{course_id}/related/{related_course_id}', [RelatedCourseAPIController::class, 'destroy']);
        });

    });

});
