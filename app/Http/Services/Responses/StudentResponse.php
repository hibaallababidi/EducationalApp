<?php

namespace App\Http\Services\Responses;

use Illuminate\Http\JsonResponse;

class StudentResponse
{

    public function get_Response($response): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.displayTeacher'),
            'data' => $response
        ]);
    }

    public function getResponse($response): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.displayTeacher'),
            'data' => $response
        ]);
    }

    public function get__Response($response): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.displayTeacher'),
            'data' => $response
        ]);
    }

    public function getCourse($response): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.displayCourses'),
            'data' => $response
        ]);
    }

    public function displayMyFavoriteSpecialtiesResponse($favourite_specializations): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.display_my_favorite_specialtiesResponse'),
            'data' => $favourite_specializations
        ]);
    }

    public function mySubscriptionsCoursesResponse($mySubscriptionsCourses): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.my_subscriptions_courses'),
            'data' => $mySubscriptionsCourses
        ]);
    }

    public function studentCompleteInfoResponse($student): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.complete_info_student'),
            'data' => $student
        ]);
    }

    public function studentEditProfileResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.student_edit_profile'),
            'data' => []
        ]);
    }

    public function displayTeacherInfoResponse($display_myCourses_approved, $display_info, $homeData, $followerCount, $isFollowing, $infoLocation, $infoSocaial): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.display_teacher_Info'),
            'data' => [
                'approved_courses' => $display_myCourses_approved,
                'teacher_info' => $display_info,
                'posts' => $homeData,
                'info' => [
                    'follower_count' => $followerCount,
                    'is_following' => $isFollowing,
                    'location_info' => $infoLocation,
                    'social_info' => $infoSocaial,
                ],
            ],
        ]);
    }

    public function likeResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.like_'),
            'data' => []
        ]);
    }

    public function dislikeResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.dislike_'),
            'data' => []
        ]);
    }

    public function studentSearchResponse($getSpecialization): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.search'),
            'data' => $getSpecialization
        ]);
    }


    //filter
    //1
    public function getSpecializationResponse($result): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.get_specialization'),
            'data' => $result
        ]);
    }

    public function getLocationResponse($result): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.get_location'),
            'data' => $result
        ]);
    }

    public function getLocationSpecializationResponse($result): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.get_location_specialization'),
            'data' => $result
        ]);
    }

    public function getCourseResponse($result): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.get_course'),
            'data' => $result
        ]);
    }

    public function getCourseSpecializationResponse($result): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.get_course_specialization'),
            'data' => $result
        ]);
    }

    public function reportStudentResponse1(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.add_report_student'),
            'data' => []
        ]);
    }

    public function subscribeToCourseResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.subscribeToCourse'),
            'data' => []
        ], 201);
    }

    public function addCommentResponse($comment): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.addComment'),
            'data' => $comment
        ], 201);
    }

    public function evaluateCourseResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.evaluateCourse'),
            'data' => []
        ], 201);
    }

    public function evaluateLessonResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.evaluateLesson'),
            'data' => []
        ], 201);
    }

    public function displayMyLessonsResponse($lessons): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.my_lessons'),
            'data' => $lessons
        ]);
    }

    public function addFavouriteSpecializationsResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.addFavouriteSpecializations'),
            'data' => []
        ], 201);
    }

    public function displayHomePageResponse($home): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.displayHomePage'),
            'data' => $home
        ]);
    }

    public function displayItemCommentsResponse($comments): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.displayItemComments'),
            'data' => $comments
        ]);
    }

    public function studentBlockTeacherResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.studentBlockTeacher'),
            'data' => []
        ]);
    }

    public function addViewResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => '',
            'data' => []
        ]);
    }

    public function displayTopCoursesResponse($courses): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.displayTopCourses'),
            'data' => $courses
        ]);
    }
}
