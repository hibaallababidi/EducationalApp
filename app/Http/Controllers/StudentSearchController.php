<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\StudentSearchRequest;
use App\Http\Services\Responses\StudentResponse;
use App\Models\Course;
use App\Models\CourseItem;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentSearchController extends Controller
{
    private StudentResponse $response;

    #[Pure] public function __construct()
    {
        $this->response = new StudentResponse();


    }

    public function studentSearch(StudentSearchRequest $request)
    {
        // Initialize result structure with empty arrays to ensure consistent return format
        $result = [
            'teachers' => [],
            'courses' => [],
            'course_items' => []
        ];

        // Search for teachers based on name
        $studentSearchTeacher = Teacher::where(function ($query) use ($request) {
            $query->where('teachers.first_name', 'LIKE', '%' . $request->name . '%')
                ->orWhere('teachers.last_name', 'LIKE', '%' . $request->name . '%');
        })
            ->whereNotNull('teachers.location_id') // Ensuring location_id is not null
            ->leftJoin('teacher_specializations', 'teachers.id', '=', 'teacher_specializations.teacher_id')
            ->leftJoin('specializations', 'specializations.id', '=', 'teacher_specializations.specialization_id')
            ->select('teachers.id', 'teachers.first_name', 'teachers.last_name', 'specializations.specialization')
            ->get();

        // Group the teachers by ID and aggregate their specializations
        if (!$studentSearchTeacher->isEmpty()) {
            $result['teachers'] = $studentSearchTeacher->groupBy('id')->map(function ($groupedTeacher) {
                $teacher = $groupedTeacher->first();
                $specializations = $groupedTeacher->pluck('specialization')->filter()->unique()->values();
                $profilePicture = $teacher->getFirstMediaUrl('ProfilePicture', 'thumb');

                return [
                    'id' => $teacher->id,
                    'first_name' => $teacher->first_name,
                    'last_name' => $teacher->last_name,
                    'specializations' => $specializations,
                    'picture' => $profilePicture,
                ];
            })->values();
        }

        // Search for courses
        $studentSearchCourses = Course::where('courses.status', '=', 'published')
            ->where('courses.course_name', 'LIKE', '%' . $request->name . '%')
            ->leftJoin('teachers', 'teachers.id', '=', 'courses.teacher_id')
            ->select(
                'courses.id',
                'courses.course_name',
                'courses.course_description',
                'courses.is_free',
                'courses.price',
                'courses.created_at',
                'courses.updated_at',
                'teachers.id as teacher_id',
                'teachers.first_name',
                'teachers.last_name'
            )
            ->get();

        if (!$studentSearchCourses->isEmpty()) {
            $result['courses'] = $studentSearchCourses->map(function ($course) {
                $teacher = Teacher::find($course->teacher_id);
                $picture = $teacher ? $teacher->getFirstMediaUrl('ProfilePicture', 'thumb') : null;
                return [
                    'id' => $course->id,
                    'course_name' => $course->course_name,
                    'course_description' => $course->course_description,
                    'is_free' => $course->is_free,
                    'price' => $course->price,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                    'teacher' => [
                        'id' => $course->teacher_id,
                        'first_name' => $course->first_name,
                        'last_name' => $course->last_name,
                        'picture' => $picture,
                    ],
                ];
            });
        }

// Search for course items with media
        $studentSearchCourseItem = CourseItem::query()
            ->with('media')
            ->where('course_items.item_name', 'LIKE', '%' . $request->name . '%')
            ->leftJoin('courses', 'courses.id', '=', 'course_items.course_id')
            ->leftJoin('teachers', 'teachers.id', '=', 'courses.teacher_id')
            ->select(
                'course_items.id',
                'course_items.item_name',
                'course_items.views',
                'course_items.item_description',
                'courses.id as course_id',
                'courses.is_free',
                'courses.price',
                'courses.course_name',
                'courses.course_description',
                'courses.created_at',
                'courses.updated_at',
                'teachers.id as teacher_id',
                'teachers.first_name',
                'teachers.last_name'
            )
            ->get();

        if (!$studentSearchCourseItem->isEmpty()) {
            $result['course_items'] = $studentSearchCourseItem->map(function ($courseItem) {
                $teacher = Teacher::find($courseItem->teacher_id);
                $picture = $teacher ? $teacher->getFirstMediaUrl('ProfilePicture', 'thumb') : null;
                $media = $courseItem->media->map(function ($mediaItem) {
                    return $mediaItem->only([
                        'id', 'model_type', 'model_id', 'uuid', 'collection_name', 'name',
                        'file_name', 'mime_type', 'disk', 'conversions_disk', 'size',
                        'manipulations', 'custom_properties', 'generated_conversions',
                        'responsive_images', 'order_column', 'created_at', 'updated_at',
                        'original_url', 'preview_url',
                    ]);
                });

                return [
                    'id' => $courseItem->id,
                    'item_name' => $courseItem->item_name,
                    'views' => $courseItem->views,
                    'item_description' => $courseItem->item_description,
                    'course' => [
                        'id' => $courseItem->course_id,
                        'course_name' => $courseItem->course_name,
                        'course_description' => $courseItem->course_description,
                        'is_free' => $courseItem->is_free,
                        'price' => $courseItem->price,
                        'created_at' => $courseItem->created_at,
                        'updated_at' => $courseItem->updated_at,
                    ],
                    'teacher' => [
                        'id' => $courseItem->teacher_id,
                        'first_name' => $courseItem->first_name,
                        'last_name' => $courseItem->last_name,
                        'picture' => $picture,
                    ],
                    'media' => $media,
                ];
            });
        }

        // Return the response
        return $this->response->studentSearchResponse($result);
    }


}





//        $name = $request->name;
//
//        $studentSearch = Teacher::join('courses', 'teachers.id', '=', 'courses.teacher_id')
//            ->join('course_items', 'courses.id', '=', 'course_items.course_id')
//            ->where(function($query) use ($name) {
//                $query->where('teachers.first_name', 'LIKE', '%' . $name . '%')
//                    ->orWhere('teachers.last_name', 'LIKE', '%' . $name . '%')
//             4       ->orWhere('courses.course_name', 'LIKE', '%' . $name . '%')
//                    ->orWhere('course_items.item_name', 'LIKE', '%' . $name . '%');
//            })
//            ->select('teachers.first_name', 'teachers.last_name', 'courses.course_name', 'course_items.item_name')
//            ->get();
//
//        return response()->json($studentSearch);
