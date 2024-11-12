<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Http\Services\Responses\AdminResponse;
use App\Http\Services\Responses\TeacherResponse;
use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseOrderController extends Controller
{

    private AdminResponse $admin_response;


    public function __construct()
    {
        $this->admin_response = new AdminResponse();

    }

    public function displayCoursesWaiting()
    {
        $display_order_waiting = Teacher::
        join('courses', 'teachers.id', '=', 'courses.teacher_id')
            ->where('courses.status', '=', 'waiting')
            ->get([
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
            ]);
        return response()->json([
            'status' => true,
            'message' => trans('messages.display_order_waiting'),
            'data' => $display_order_waiting
        ]);

    }

    public function statusAcceptCourse(UpdateCourseRequest $request)
    {
        $update_course = Course::
        where('courses.id', $request->course_id)
            ->first();
        if ($update_course) {
            $update_course->update(['status' => 'published']);
            return $this->admin_response->updateStatusCourseResponse();
        }
        return response()->json(['error' => 'Course not found or unauthorized.'], 400);

    }

    public function statusRejectCourse(UpdateCourseRequest $request)
    {
        $update_course = Course::
        where('courses.id', $request->course_id)
            ->first();
        if ($update_course) {
            $update_course->update(['status' => 'editing']);
            return $this->admin_response->updateStatusCourseResponse();
        }
        return response()->json(['error' => 'Course not found or unauthorized.'], 400);

    }

}
