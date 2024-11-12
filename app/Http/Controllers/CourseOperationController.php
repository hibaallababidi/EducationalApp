<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teacher\DeleteCourseItemRequest;
use App\Http\Requests\Teacher\DeleteCourseRequest;
use App\Http\Requests\Teacher\UpdateCourseItemRequest;
use App\Http\Requests\Teacher\UpdateCourseRequest;
use App\Http\Services\Responses\TeacherResponse;
use App\Models\Course;
use App\Models\CourseItem;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseOperationController extends Controller
{
    private TeacherResponse $teacher_response;
    public function __construct()
    {
        $this->teacher_response = new TeacherResponse();

    }

    public function display_myCourses_modification()
    {
        $display_myCourses_modification = Teacher::
        join('courses', 'teachers.id', '=', 'courses.teacher_id')
            ->where('courses.teacher_id', Auth::id())
            ->where('courses.status', '=', 'editing')
            ->get([
                'teachers.id as teacher_id ',
                'courses.id as course_id',
                'courses.course_name',
                'courses.course_description',
                'courses.is_free',
                'courses.price',
                'courses.created_at',
                'courses.updated_at',

            ]);
        return response()->json([
            'status' => true,
            'message' => trans('messages.display_my_courses'),
            'data' => $display_myCourses_modification
        ]);

    }

    public function display_myCourses_approved()
    {
        $display_myCourses_approved = Teacher::
        join('courses', 'teachers.id', '=', 'courses.teacher_id')
            ->where('courses.teacher_id', Auth::id())
            ->where('courses.status', '=', 'published')
            ->get([
                'teachers.id as teacher_id ',
                'courses.id as course_id',
                'courses.course_name',
                'courses.course_description',
                'courses.is_free',
                'courses.price',
                'courses.created_at',
                'courses.updated_at',
            ]);
        return response()->json([
            'status' => true,
            'message' => trans('messages.display_my_courses'),
            'data' => $display_myCourses_approved
        ]);

    }

    public function display_myCourses_waiting()
    {
        $display_myCourses_waiting = Teacher::
        join('courses', 'teachers.id', '=', 'courses.teacher_id')
            ->where('courses.teacher_id', Auth::id())
            ->where('courses.status', '=', 'waiting')
            ->get([
                'teachers.id as teacher_id ',
                'courses.id as course_id',
                'courses.course_name',
                'courses.course_description',
                'courses.is_free',
                'courses.price',
                'courses.created_at',
                'courses.updated_at',
            ]);
        return response()->json([
            'status' => true,
            'message' => trans('messages.display_my_courses'),
            'data' => $display_myCourses_waiting
        ]);

    }

    public function update_course(UpdateCourseRequest $request)
    {
        $update_course = Course::where('id', $request->course_id)
            ->where('teacher_id', Auth::id())
            ->first();
        if ($update_course) {
            if ($request->has('course_name')) {
                $update_course->update(['course_name' => $request->course_name]);
            }
            if ($request->has('course_description')) {
                $update_course->update(['course_description' => $request->course_description]);
            }
            if ($request->has('is_free')) {
                $update_course->update(['is_free' => $request->is_free]);
            }
            if ($request->has('price')) {

                $check = Course::where('id', $request->course_id)
                    ->where('status', 'published')
                    ->first();
                if ($check) {
                    return $this->teacher_response->there_is_mistake();
                } else {
                    $update_course->update(['price' => $request->price]);
                }

            }
            return $this->teacher_response->updateCourseResponse();


        }

        return response()->json(['message' => 'Post not found.'], 404);
    }
    //update_course_item هذا تابع فيه مشكلة لدي
    public function update_course_item(UpdateCourseItemRequest $request)
    {
        $upd_c_it = CourseItem::
        where('course_items.id', $request->course_item_id)
            ->first();
//        if ($upd_c_it) {
            if ($request->has('item_name')) {
                $upd_c_it->update(['item_name' => $request->item_name]);

            }
            if ($request->has('item_description')) {
                $upd_c_it->update(['item_description' => $request->item_description]);

            }
            if ($request->has('item_order')) {
                $upd_c_it->update(['item_order' => $request->item_order]);

            }

            $upd_c_it->save();
            return $this->teacher_response->updateCourseItemResponse();
//        }
    }

    public function delete_course_item(DeleteCourseItemRequest $request)
    {
        $delete_course_item = CourseItem::where('id', $request->course_item_id)
            ->first();

        if ($delete_course_item) {
            $delete_course_item->clearMediaCollection();
            $delete_course_item->delete();
        }

        return $this->teacher_response->deleteCourseItemResponse();
    }

    public function delete_course(DeleteCourseRequest $request)
    {
        $course = Course::where('id', $request->course_id)
            ->where('teacher_id', Auth::id())
            ->where(function ($query) {
                $query->where('status', 'waiting')
                    ->orWhere('status', 'editing');
            })
            ->first();

        if ($course) {
            $course->delete();
            return $this->teacher_response->deleteCourseResponse();
        } else {
            return response()->json(['error' => 'Course not found or not eligible for deletion.'], 404);
        }
    }

}
