<?php

namespace App\Http\Services\Responses;

use Illuminate\Http\JsonResponse;

class AdminResponse
{

    public function displayProfileEducationalResponse($display_profile_educational, $display_jobs_educational)
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.display_profile_educational'),
            'data' => [
                'profile_educational' => $display_profile_educational,
                'jobs' => $display_jobs_educational,]

        ]);
    }

    public function displayTopCoursesHaveSubscriptionsResponse($topCoursesHaveSubscriptions)
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.topCoursesHaveSubscriptions'),
            'data' => $topCoursesHaveSubscriptions
        ], 201);
    }

    public function displayNumberofTeacherbySpecialtyResponse($numberofTeacherbySpecialty)
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.number_of_teacher_by_specialty'),
            'data' => $numberofTeacherbySpecialty
        ], 201);
    }

    public function specializationResponse()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.specialization_added'),
            'data' => []
        ], 201);
    }

    public function resultReportsResponse1($results)
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.report_r'),
            'data' => $results
        ], 201);
    }

    public function displayDetailsReportResponse($displayDetailsReport)
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.report_details'),
            'data' => $displayDetailsReport
        ], 201);
    }

    public function displayReportStudentResponse($displayDetailsReport)
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.report_details'),
            'data' => $displayDetailsReport
        ], 201);
    }

    public function displayReportEducationalsResponse($displayDetailsReport)
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.report_details'),
            'data' => $displayDetailsReport
        ], 201);
    }

    public function displayreportedTeacherResponse($displayDetailsReport)
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.report_details'),
            'data' => $displayDetailsReport
        ], 201);
    }

    public function displayreport_TeacherResponse($displayDetailsReport)
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.report_details'),
            'data' => $displayDetailsReport
        ], 201);
    }


    public function updateStatusCourseResponse()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.update_status_course'),
            'data' => []
        ]);
    }

    public function displayReportResponse($displayReport)
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.displayReport'),
            'data' => $displayReport,
        ]);
    }

    public function blockTeacher()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.block_teacher'),
            'data' => [],
        ]);
    }

    public function unblockTeacher()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.unblock_teacher'),
            'data' => [],
        ]);
    }

    /*student*/
    public function blockStudent()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.block_student'),
            'data' => [],
        ]);
    }

    public function unblockStudent()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.unblock_student'),
            'data' => [],
        ]);
    }

    /*Educational*/
    public function blockEducational()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.block_educational'),
            'data' => [],
        ]);
    }

    public function unblockEducational()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.unblock_educational'),
            'data' => [],
        ]);
    }

    public function displayEducationalSubmissionsResponse($submissions): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.displayEducationalSubmissions'),
            'data' => $submissions
        ]);
    }

    public function acceptEducationalSubmissionsResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.acceptEducationalSubmissions'),
            'data' => []
        ]);
    }

    public function rejectEducationalSubmissionsResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.rejectEducationalSubmissions'),
            'data' => []
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

    public function displayStudentsResponse($students): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.teachers'),
            'data' => $students
        ]);
    }

    public function displayCoursesResponse($courses): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.displayCoursesResponse'),
            'data' => $courses
        ]);
    }

    public function displayEducationalsResponse($educationals): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.displayEducationals'),
            'data' => $educationals
        ]);
    }

    public function jobsPerEducationalResponse($result): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.jobsPerEducational'),
            'data' => $result
        ]);
    }

    public function privateLessonsStatisticsResponse($result): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.privateLessonsStatistics'),
            'data' => $result
        ]);
    }

    public function subscriptionStatisticsResponse($result): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.subscriptionStatistics'),
            'data' => $result
        ]);
    }

    public function displayTeacherProfileResponse($display_myCourses_approved, $display_info, $homeData, $followerCount, $infoLocation, $infoSocaial): JsonResponse
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
                    'location_info' => $infoLocation,
                    'social_info' => $infoSocaial,
                ],
            ],
        ]);
    }

}
