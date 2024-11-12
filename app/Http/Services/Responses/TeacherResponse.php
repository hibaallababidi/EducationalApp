<?php

namespace App\Http\Services\Responses;

use Illuminate\Http\JsonResponse;

class TeacherResponse
{
    public function displayMySpecializationsResponse($specializations): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.get_specializations'),
            'data' => $specializations
        ]);

    }

    public function displayPostsTeacherResponse($posts): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.display_post'),
            'data' => $posts
        ]);
    }

    public function suggestedResponse()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.suggestion_added'),
            'data' => []
        ], 201);
    }

    public function suggested_Response()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.suggestion_no_added'),
            'data' => []
        ], 400);
    }

    public function PostResponse()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.post_added'),
            'data' => []
        ], 201);
    }

    public function displayPostResponse()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.display_post'),
            'data' => []
        ], 200);
    }

    public function deletePostResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.delete_post'),
            'data' => []
        ], 200);
    }

    public function updatePostResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.update_post'),
            'data' => []
        ]);
    }

    public function updateCourseResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.update_course'),
            'data' => []
        ]);
    }

    public function updateCourseItemResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.update_course_item'),
            'data' => []
        ], 200);
    }

    public function deleteCourseItemResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.delete_course_item'),
            'data' => []
        ]);
    }

    public function followResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.follow'),
            'data' => []
        ], 200);
    }

    public function follow_Response(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.follow_Response'),
            'data' => []
        ]);
    }

    public function deleteFollowResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.delete_Follow'),
            'data' => []
        ]);
    }

    public function delete_FollowResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.unFollow'),
            'data' => []
        ]);
    }

    public function there_is_mistake(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => trans('messages.there_is_mistake'),
            'data' => []
        ], 201);
    }

    public function deleteCourseResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.delete_course'),
            'data' => []
        ]);
    }

    public function completeInfoResponse($user): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.complete_info'),
            'data' => $user
        ]);
    }

    public function getSpecializationsResponse($result): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.get_specializations'),
            'data' => $result
        ]);
    }

    public function getStudentsResponse($students): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.getStudents'),
            'data' => $students,
        ]);
    }

    public function addLessonResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.add_lesson'),
            'data' => []
        ], 201);
    }

    public function editLessonResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.edit_lesson'),
            'data' => []
        ]);
    }

    public function deleteLessonResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.deleteLesson'),
            'data' => []
        ]);
    }

    public function editLessonFailResponse(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => trans('messages.edit_lesson_fail'),
            'data' => []
        ], 400);
    }

    public function displayMyLessonsResponse($lessons): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.my_lessons'),
            'data' => $lessons
        ]);
    }

    public function displayTeachersResponse($teachers): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.teachers'),
            'data' => $teachers
        ]);
    }

    public function displayTeacherProfileResponse($teacher): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.teacher_profile'),
            'data' => $teacher
        ]);
    }

    public function addCourseResponse($course): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.add_course'),
            'data' => $course
        ], 201);
    }

    public function addCourseItemResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.add_course_item'),
            'data' => []
        ], 201);
    }

    public function submitCourseResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.submit_course'),
            'data' => []
        ]);
    }

    public function displayCourseItemsResponse($items): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.course_items'),
            'data' => $items
        ]);
    }

    public function teacherEditProfileResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.edit_profile'),
            'data' => []
        ]);
    }

    public function myFollowersCountResponse($count): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.myFollowersCount'),
            'data' => $count
        ]);
    }
}
