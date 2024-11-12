<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teacher\PrivateLesson\AddLessonRequest;
use App\Http\Requests\Teacher\PrivateLesson\DeleteLessonRequest;
use App\Http\Requests\Teacher\PrivateLesson\DisplayLessonDetailsRequest;
use App\Http\Requests\Teacher\PrivateLesson\EditLessonRequest;
use App\Http\Services\Responses\StudentResponse;
use App\Http\Services\Responses\TeacherResponse;
use App\Http\Services\StudentServices;
use App\Http\Services\TeacherServices;
use App\Models\Notification;
use App\Models\PrivateLesson;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PrivateLessonController extends Controller
{
    private TeacherServices $teacherServices;
    private TeacherResponse $teacherResponse;
    private StudentServices $studentServices;
    private StudentResponse $studentResponse;

    public function __construct()
    {
        $this->teacherServices = new TeacherServices();
        $this->teacherResponse = new TeacherResponse();
        $this->studentServices = new StudentServices();
        $this->studentResponse = new StudentResponse();
    }

    public function getStudents(): JsonResponse
    {
        $students = Student::query()
            ->get(['id', 'first_name', 'last_name', 'email']);
        return $this->teacherResponse->getStudentsResponse($students);
    }

    public function confirmLessonPayment($lessonId)
    {
        if (!$lessonId) {
            return response()->json(['status' => false, 'message' => 'Course ID is required'], 400);
        }
        try {
            $this->studentServices->confirmLesson($lessonId);
//            return response()->json(['status' => true, 'message' => 'Subscription successful']);
            return view('payment_confirmed');
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function addLesson(AddLessonRequest $request): JsonResponse
    {
        $privateLesson = $this->teacherServices->savePrivateLesson($request);
        //send notification to student
        $user = Auth::user();
        $title = $user->first_name . ' added a lesson with you';
        $body = "You have a lesson at " . \Carbon\Carbon::parse($privateLesson->lesson_date);
        $student = Student::query()
            ->find($request->student_id);
        $device_token = $student->device_token;
        Notification::create([
            'type' => 'student',
            'user_id' => $student->id,
            'title' => $title,
            'body' => $body
        ]);
        $h = (new NotificationController)
            ->sendNotification($device_token, $body, $title);
//        return response()->json($privateLesson);
        return $this->teacherResponse->addLessonResponse();
    }

    public function editLesson(EditLessonRequest $request): JsonResponse
    {
        $lesson = PrivateLesson::query()->find($request->lesson_id);
        if (Carbon::now()->isAfter($lesson->lesson_date))
            return $this->teacherResponse->editLessonFailResponse();
        $this->teacherServices->updatePrivateLesson($lesson, $request);
        return $this->teacherResponse->editLessonResponse();
    }

    public function deleteLesson(DeleteLessonRequest $request): JsonResponse
    {
        $this->teacherServices->deleteLesson($request);
        return $this->teacherResponse->deleteLessonResponse();
    }

    public function displayMyLessonsTeacher(): JsonResponse
    {
        $lessons = $this->teacherServices->getMyLessons();
        return $this->teacherResponse->displayMyLessonsResponse($lessons);
    }

    public function displayLessonDetails(DisplayLessonDetailsRequest $request): JsonResponse
    {
        $lesson = $this->teacherServices->getLessonDetails($request->lesson_id);
        return $this->teacherResponse->displayMyLessonsResponse($lesson);
    }

    public function displayMyLessonsStudent(): JsonResponse
    {
        $lessons = $this->studentServices->getMyLessons();
        return $this->studentResponse->displayMyLessonsResponse($lessons);
    }
}
