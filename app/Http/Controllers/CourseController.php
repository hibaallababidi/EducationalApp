<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\Student\AddViewRequest;
use App\Http\Requests\Student\SubscribeToCourseRequest;
use App\Http\Requests\Teacher\Course\AddCourseItemRequest;
use App\Http\Requests\Teacher\Course\AddCourseRequest;
use App\Http\Requests\Teacher\Course\DisplayCourseDetailsRequest;
use App\Http\Requests\Teacher\Course\SubmitCourseToAdminRequest;
use App\Http\Services\AdminServices;
use App\Http\Services\Responses\AdminResponse;
use App\Http\Services\Responses\StudentResponse;
use App\Http\Services\Responses\TeacherResponse;
use App\Http\Services\StudentServices;
use App\Http\Services\TeacherServices;
use App\Models\CourseEvaluation;
use App\Models\StudentSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    private TeacherServices $t_services;
    private TeacherResponse $t_response;
    private AdminServices $a_services;
    private AdminResponse $a_responses;
    private StudentResponse $s_response;
    private StudentServices $s_services;


    public function __construct()
    {
        $this->t_services = new TeacherServices();
        $this->t_response = new TeacherResponse();
        $this->a_services = new AdminServices();
        $this->a_responses = new AdminResponse();
        $this->s_response = new StudentResponse();
        $this->s_services = new StudentServices();
    }

    public function mySubscriptionsCourses()
    {
        $studentId = Auth::id();
        $mySubscriptionsCourses = StudentSubscription::query()
            ->join('courses', 'courses.id', '=', 'student_subscriptions.course_id')
            ->join('teachers', 'teachers.id', '=', 'courses.teacher_id')
            ->where('student_subscriptions.student_id', $studentId)
            ->select([
                'courses.id as course_id',
                'courses.course_name',
                'courses.course_description',
                'courses.is_free',
                'courses.price',
                'courses.teacher_id',
                'teachers.first_name as teacher_first_name',
                'teachers.last_name as teacher_last_name',
                'courses.created_at',
                'courses.updated_at'
            ])
            ->get();

        $courseRatings = CourseEvaluation::query()
            ->join('courses', 'courses.id', '=', 'course_evaluations.course_id')
            ->where('courses.status', 'published')
            ->select([
                'course_evaluations.course_id',
                DB::raw('AVG(course_evaluations.rate) as course_rate')
            ])
            ->groupBy('course_evaluations.course_id')
            ->pluck('course_rate', 'course_evaluations.course_id');

        $response = $mySubscriptionsCourses->map(function ($course) use ($courseRatings) {
            return [
                'course_id' => $course->course_id,
                'course_name' => $course->course_name,
                'course_description' => $course->course_description,
                'price' => $course->price,
                'is_free' => $course->is_free,
                'teacher_id' => $course->teacher_id,
                'teacher_first_name' => $course->teacher_first_name,
                'teacher_last_name' => $course->teacher_last_name,
                'created_at' => $course->created_at,
                'updated_at' => $course->updated_at,
                'course_rate' => $courseRatings->get($course->course_id, '0.0000')
            ];
        });


        return $this->s_response->mySubscriptionsCoursesResponse($response);
    }

    public function addCourse(AddCourseRequest $request): JsonResponse
    {
        $course = $this->t_services->saveCourse($request);
        return $this->t_response->addCourseResponse($course);
    }

    public function addCourseItem(AddCourseItemRequest $request): JsonResponse
    {
        $this->t_services->saveCourseItem($request);
        return $this->t_response->addCourseItemResponse();
    }

    public function submitCourseToAdmin(SubmitCourseToAdminRequest $request): JsonResponse
    {
        $this->t_services->submitCourse($request);
        return $this->t_response->submitCourseResponse();
    }

    public function displayCourseItems(DisplayCourseDetailsRequest $request): JsonResponse
    {
        $items = $this->t_services->getCourseItems($request);
        $is_subscribed = $this->t_services->getIsSubscribed($request);
        $course_rate = $this->t_services->getCourseEvaluation($request);
        $teacher = $this->t_services->getCourseTeacher($request);
        if ($course_rate != null)
            $rate = $course_rate->course_rate;
        else
            $rate = null;
        $count = $this->t_services->getSubscribersCount($request);
        $data = [
            'teacher' => $teacher,
            'is_subscribed' => $is_subscribed,
            'course_rate' => $rate,
            'subscribers_count' => $count,
            'items' => $items
        ];
        return $this->t_response->displayCourseItemsResponse($data);
    }

    public function displayCoursesAdmin(): JsonResponse
    {
        $courses = $this->a_services->getCourses();
        return $this->a_responses->displayCoursesResponse($courses);
    }

    public function displayCourseItemsAdmin(DisplayCourseDetailsRequest $request): JsonResponse
    {
        $items = $this->t_services->getCourseItems($request);
        $course_rate = $this->t_services->getCourseEvaluation($request);
        $teacher = $this->t_services->getCourseTeacher($request);
        if ($course_rate != null)
            $rate = $course_rate->course_rate;
        else
            $rate = null;
        $count = $this->t_services->getSubscribersCount($request);
        $data = [
            'teacher' => $teacher,
            'course_rate' => $rate,
            'subscribers_count' => $count,
            'items' => $items
        ];
        return $this->t_response->displayCourseItemsResponse($data);
    }

//    public function subscribeToCourse(SubscribeToCourseRequest $request): JsonResponse
//    {
//        $this->s_services->saveCourseSubscription($request);
//        return $this->s_response->subscribeToCourseResponse();
//    }

    public function subscribeToCourseFree(SubscribeToCourseRequest $request): JsonResponse
    {
        $this->s_services->saveCourseSubscription($request->course_id, Auth::id());
        $user = Auth::user();
        $teacher = Teacher::query()
            ->join('courses', 'teachers.id', '=', 'courses.teacher_id')
            ->where('courses.id', '=', $request->course_id)
            ->get([
                'teachers.id as teacher_id'
            ])
            ->first();
        $title = 'Subscribe';
        $body = $user->first_name . ' subscribed to your course';
        Notification::create([
            'type' => 'teacher',
            'user_id' => $teacher->teacher_id,
            'title' => $title,
            'body' => json_encode($body),
            'actor_id' => $user->id
        ]);
        return $this->s_response->subscribeToCourseResponse();
    }


    public function subscribeToCourse($courseId, $studentId)
    {
        if (!$courseId) {
            return response()->json(['status' => false, 'message' => 'Course ID is required'], 400);
        }
        try {
            $this->s_services->saveCourseSubscription($courseId, $studentId);
            $teacher = Teacher::query()
                ->join('courses', 'teachers.id', '=', 'courses.teacher_id')
                ->where('courses.id', '=', $courseId)
                ->get([
                    'teachers.id as teacher_id'
                ])
                ->first();
            $student = Student::query()->find($studentId);
            $title = 'Subscribe';
            $body = $student->first_name . ' subscribed to your course';
            Notification::create([
                'type' => 'teacher',
                'user_id' => $teacher->teacher_id,
                'title' => $title,
                'body' => $body,
                'actor_id' => $studentId
            ]);
            return view('subscribe', ['course_id' => $courseId, 'student_id' => $studentId]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }


    public function addView(AddViewRequest $request): JsonResponse
    {
        $this->s_services->addView($request);
        return $this->s_response->addViewResponse();
    }

//    public function update_course_item(UpdateCourseItemRequest $request)
//    {
//        $update_course_item = CourseItem::query()
////            ->join('courses', 'courses.id', '=', 'course_items.course_id')
//            ->where('course_items.id', $request->course_item_id)
////            ->where('courses.teacher_id', Auth::id())
//            ->first();
//        if ($update_course_item) {
//            if ($request->has('item_name')) {
//                $update_course_item->update(['item_name' => $request->item_name]);
//
//            }
//            if ($request->has('item_description')) {
//                $update_course_item->update(['item_description' => $request->item_description]);
//
//            }
//            if ($request->has('item_order')) {
//                $update_course_item->update(['item_order' => $request->item_order]);
//
//            }
//
//            $update_course_item->save();
//            return response()->json($update_course_item);//$this->teacher_response->updateCourseItemResponse();
//        }
//        return response()->json(['error' => 'Course item not found or unauthorized.'], 404);
//    }

}
