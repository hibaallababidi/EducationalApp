<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\EducationalAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\StudentController;
use App\Http\Controllers\Auth\TeacherController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\BlockController;

use App\Http\Controllers\CommentsController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseOperationController;
use App\Http\Controllers\CourseOrderController;
use App\Http\Controllers\DisplayUsersController;

use App\Http\Controllers\EducationalController;
use App\Http\Controllers\EducationalFilterController;

//use App\Http\Controllers\EducationalReportController;
use App\Http\Controllers\EducationalStudentReportController;
use App\Http\Controllers\EducationalSubmissionsController;

use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PrivateLessonController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\ProfileEducationalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SpecializationController;

use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\StudentCoursesController;
use App\Http\Controllers\StudentOperationController;
use App\Http\Controllers\StudentFilterController;
use App\Http\Controllers\StudentProfileController;

use App\Http\Controllers\StudentSearchController;
use App\Http\Controllers\SuggestedController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group([
    'middleware' => 'localization',
], function () {
    Route::post('login', [LoginController::class, 'login']);
    Route::post('forgotPassword', [ForgotPasswordController::class, 'forgotPassword']);
    Route::get('locations', [LocationController::class, 'getLocations']);
    Route::get('getSpecializations', [SpecializationController::class, 'getSpecializations']);
    Route::post('search', [StudentSearchController::class, 'studentSearch']);
    Route::get('display_jobs', [JobController::class, 'display_all_jobs']);
    Route::get('displayCourseItems', [CourseController::class, 'displayCourseItems']);
    Route::post('student/display_teacher_Info', [StudentOperationController::class, 'displayTeacherInfo']);
    Route::post('success.payment', function () {
    })->name('payment');


    Route::group([
        'prefix' => 'admin',
        'controller' => AdminAuthController::class
    ], function () {
        Route::post('register', 'register');
        Route::post('verifyEmail', 'verifyEmail');
        Route::post('login', 'login');
        Route::post('forgotPassword', 'forgotPassword');
        Route::post('verifyPasswordEmail', 'verifyPasswordEmail');
        Route::post('updatePassword', 'updatePassword');
        Route::group([
            'middleware' => ['assign.guard:admins', 'jwt.auth']
        ], function () {
            Route::post('resetPassword', 'resetPassword');

            /*
             * Helez
             */
            Route::post('specialization_added', [SpecializationController::class, 'addSpecializations']);
            Route::post('statusAcceptCourse', [CourseOrderController::class, 'statusAcceptCourse']);
            Route::post('statusRejectCourse', [CourseOrderController::class, 'statusRejectCourse']);
            Route::get('displaySuggestedSpecialization', [SuggestedController::class, 'displaySuggestedSpecialization']);
            Route::get('display_courses_waiting', [CourseOrderController::class, 'displayCoursesWaiting']);
            Route::post('displayReport', [ReportController::class, 'displayReport']);
            Route::get('displayReports', [ReportController::class, 'displayReports']);

            Route::post('block_teacher', [BlockController::class, 'blockTeacher']);
            Route::post('block_student', [BlockController::class, 'blockStudent']);
            Route::post('block_educational', [BlockController::class, 'blockEducational']);

            Route::post('un_block_teacher', [BlockController::class, 'unBlockTeacher']);
            Route::post('un_block_student', [BlockController::class, 'unblockStudent']);
            Route::post('un_block_educational', [BlockController::class, 'unblockEducational']);

            Route::get('number_of_teacher_by_specialty', [StatisticsController::class, 'numberofTeacherbySpecialty']);
            Route::get('top_courses_have_subscriptions', [StatisticsController::class, 'topCoursesHaveSubscriptions']);
            /*
             * Hiba
             */
            Route::group([
                'controller' => EducationalSubmissionsController::class
            ], function () {
                Route::get('displayEducationalSubmissions', 'displayEducationalSubmissions');
                Route::post('acceptEducationalSubmission', 'acceptEducationalSubmission');
                Route::post('rejectEducationalSubmission', 'rejectEducationalSubmission');
            });

            Route::group([
                'controller' => DisplayUsersController::class
            ], function () {
                Route::get('displayTeachers', 'displayTeachers');
                Route::get('displayStudents', 'displayStudents');
                Route::get('displayEducationals', 'displayEducationals');
            });

            Route::get('displayTeacherProfile', [DisplayUsersController::class, 'displayTeacherProfile']);
            Route::post('displayEducationalProfile', [JobController::class, 'display_profile_educational']);
            Route::get('displayCourses', [CourseController::class, 'displayCoursesAdmin']);

            Route::get('getSpecializations', [SpecializationController::class, 'getSpecializations']);
            Route::get('displayCourseItems', [CourseController::class, 'displayCourseItemsAdmin']);

            Route::get('jobsPerEducational', [StatisticsController::class, 'jobsPerEducational']);
            Route::get('privateLessonsStatistics', [StatisticsController::class, 'privateLessonsStatistics']);
            Route::get('subscriptionStatistics', [StatisticsController::class, 'subscriptionStatistics']);
        });
    });

    Route::group([
        'prefix' => 'educational',
        'controller' => EducationalAuthController::class
    ], function () {
        Route::post('register', 'register');//->middleware('register.terminate');
        Route::post('verifyEmail', 'verifyEmail');
//    Route::post('login', 'login');
//        Route::post('forgotPassword', 'forgotPassword');
        Route::post('verifyPasswordEmail', 'verifyPasswordEmail');
        Route::post('updatePassword', 'updatePassword');

        Route::group([
            'middleware' => ['assign.guard:educationals', 'jwt.auth']
        ], function () {
            Route::post('resetPassword', [EducationalAuthController::class, 'resetPassword']);

            /*
             * Hiba
             */
            Route::post('addJob', [JobController::class, 'addJob']);
            Route::post('educationalFilter', [EducationalFilterController::class, 'educationalFilter']);
//            Route::get('displayCourseItems', [CourseController::class, 'displayCourseItems']);
            Route::get('displayItemComments', [CommentsController::class, 'displayItemComments']);
            Route::get('homePage', [EducationalController::class, 'educationalHomePage']);
            Route::get('displayMyProfile', [EducationalController::class, 'displayMyProfile']);
            Route::post('searchCV', [ProfileController::class, 'searchCVs']);

            /*
             * Helez
             */
            Route::post('add_report', [EducationalStudentReportController::class, 'addReportEducationalStudentTeacher']);
            Route::post('follow_unfollow', [FollowController::class, 'followAndUnfollow']);
            Route::post('complete_info', [ProfileEducationalController::class, 'educationalCompleteInfo']);
            Route::post('educational_edit_profile', [ProfileEducationalController::class, 'educationalEditProfile']);
            Route::post('educational_search', [StudentSearchController::class, 'studentSearch']);
//            Route::post('display_teacher_Info', [StudentOperationController::class, 'displayTeacherInfo']);
            Route::post('follow_unfollow', [FollowController::class, 'followAndUnfollow']);
        });
    });

    Route::group([
        'prefix' => 'teacher',
    ], function () {
        Route::group([
            'controller' => TeacherController::class
        ], function () {
            Route::post('register', 'register');
            Route::post('verifyEmail', 'verifyEmail');
//    Route::post('login', 'login');
//        Route::post('forgotPassword', 'forgotPassword');
            Route::post('verifyPasswordEmail', 'verifyPasswordEmail');
            Route::post('updatePassword', 'updatePassword');
        });

        Route::group([
            'middleware' => ['assign.guard:teachers', 'jwt.auth']
        ], function () {
            Route::post('resetPassword', [TeacherController::class, 'resetPassword']);

            /*
             * HELEZ
             */
            Route::post('diveceToken', [LoginController::class, 'deviceToken']);
            Route::post('suggest', [SuggestedController::class, 'suggested']);
            Route::post('add_post', [PostController::class, 'post']);
            Route::get('display_my_posts', [PostController::class, 'displayMyPost']);
            Route::post('display_details_Post', [PostController::class, 'display_details_Post']);
//            Route::get('display_jobs', [JobController::class, 'display_all_jobs']);
            Route::post('display_details_jobs', [JobController::class, 'display_details_jobs']);

            Route::post('delete_post', [PostController::class, 'delete_post']);
            Route::post('update_post', [PostController::class, 'update_post']);
            Route::post('display_profile_educational', [JobController::class, 'display_profile_educational']);
            Route::post('search', [SearchController::class, 'searchTeacher']);
            Route::post('search_specialization', [SearchController::class, 'searchSpecialization']);
            Route::post('follow_unfollow', [FollowController::class, 'followAndUnfollow']);
//            Route::post('un_follow', [FollowController::class, 'un_follow']);
            Route::get('display_mycourses', [CourseOperationController::class, 'display_myCourses_modification']);
            Route::get('display_myCourses_approved', [CourseOperationController::class, 'display_myCourses_approved']);
            Route::get('display_myCourses_waiting', [CourseOperationController::class, 'display_myCourses_waiting']);
            Route::post('update_course', [CourseOperationController::class, 'update_course']);
            Route::post('update_course_item', [CourseOperationController::class, 'update_course_item']);
            Route::post('delete_course_item', [CourseOperationController::class, 'delete_course_item']);
            Route::get('teacherInformation', [ProfileController::class, 'teacherInformation']);
            Route::post('delete_course', [CourseOperationController::class, 'delete_course']);
            Route::post('add_report', [EducationalStudentReportController::class, 'addReportEducationalStudentTeacher']);
            Route::post('add_report_teacher', [EducationalStudentReportController::class, 'addReportTeacher']);
            Route::get('notificationList', [NotificationController::class, 'notificationListTeacher']);

            /*
             * HIBA
             */
            Route::group([
                'controller' => ProfileController::class
            ], function () {
                Route::post('completeInfo', 'teacherCompleteInfo');
                Route::get('displayTeachers', 'displayTeachers');
                Route::get('displayTeacherProfile', 'displayTeacherProfile');
                Route::post('EditProfile', 'teacherEditProfile');
                Route::get('myFollowersCount', 'myFollowersCount');
            });

            Route::group([
                'controller' => PrivateLessonController::class
            ], function () {
                Route::get('getStudents', 'getStudents');
                Route::post('addLesson', 'addLesson');
                Route::post('editLesson', 'editLesson');
                Route::delete('deleteLesson', 'deleteLesson');
                Route::get('displayMyLessons', 'displayMyLessonsTeacher');
                Route::get('displayLessonDetails', 'displayLessonDetails');
            });

            Route::group([
                'controller' => CourseController::class
            ], function () {
                Route::post('addCourse', 'addCourse');
                Route::post('addCourseItem', 'addCourseItem');
                Route::post('submitCourseToAdmin', 'submitCourseToAdmin');
//                Route::get('displayCourseItems', 'displayCourseItems');
            });

            Route::get('displayPostsTeacher', [PostController::class, 'displayPostsTeacher']);
            Route::get('displayMySpecializations', [SpecializationController::class, 'displayMySpecializations']);
        });
    });

    Route::group([
        'prefix' => 'student',
    ], function () {
        Route::group([
            'controller' => StudentController::class
        ], function () {
            Route::post('register', 'register');
            Route::post('verifyEmail', 'verifyEmail');
            Route::post('verifyPasswordEmail', 'verifyPasswordEmail');
            Route::post('updatePassword', 'updatePassword');
        });
        Route::group([
            'middleware' => ['assign.guard:students', 'jwt.auth']
        ], function () {
            Route::post('resetPassword', [StudentController::class, 'resetPassword']);

            /*
             * Hiba
             */
            Route::post('pay_course', [PaymentController::class, 'payCourse']);
            Route::post('payLesson', [PaymentController::class, 'payLesson']);

            Route::group([
                'controller' => CourseController::class
            ], function () {
                Route::get('displayCourseItems', 'displayCourseItems');
                Route::post('subscribeToCourseFree', 'subscribeToCourseFree');
                Route::post('addView', 'addView');
            });

            Route::group([
                'controller' => EvaluationController::class
            ], function () {
                Route::post('evaluateCourse', 'evaluateCourse');
                Route::post('evaluateLesson', 'evaluateLesson');
            });

            Route::group([
                'controller' => CommentsController::class
            ], function () {
                Route::post('addComment', 'addComment');
                Route::get('displayItemComments', 'displayItemComments');
            });

            Route::get('displayMyLessons', [PrivateLessonController::class, 'displayMyLessonsStudent']);
            Route::get('displayHomePage', [PostController::class, 'displayHomePageStudent']);
            Route::post('blockTeacher', [BlockController::class, 'studentBlockTeacher']);
            Route::get('displayTopCourses', [StudentCoursesController::class, 'displayTopCourses']);
            Route::get('displayMyProfile', [StudentProfileController::class, 'displayMyProfile']);
            Route::get('notificationList', [NotificationController::class, 'notificationListStudent']);

            /*
             * Helez
             */
            Route::post('diveceToken', [LoginController::class, 'deviceToken']);
            Route::post('studentCompleteInfo', [StudentProfileController::class, 'studentCompleteInfo']);
            Route::post('student_edit_profile', [StudentProfileController::class, 'studentEditProfile']);
//            Route::post('display_teacher_Info', [StudentOperationController::class, 'displayTeacherInfo']);
            Route::post('like_disLike', [LikeController::class, 'likeAndDislike']);
            Route::post('add_report', [EducationalStudentReportController::class, 'addReportEducationalStudentTeacher']);
//            Route::post('studentSearch', [StudentSearchController::class, 'studentSearch']);
            Route::get('my_subscriptions_to_courses', [CourseController::class, 'mySubscriptionsCourses']);
            // Route::post('report_teacher', [ReportController::class, 'addReportStudent']);
            Route::post('filter', [StudentFilterController::class, 'studentFilter']);
            Route::post('follow_unfollow', [FollowController::class, 'followAndUnfollow']);
            Route::get('display_my_favorite_specialtiesResponse', [SuggestedController::class, 'displayMyFavoriteSpecialties']);
        });
    });
});
