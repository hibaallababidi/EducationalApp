<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\DisplayTeacherInfoRequest;
use App\Http\Services\Responses\StudentResponse;
use App\Http\Services\Responses\TeacherResponse;
use App\Models\Follow;
use App\Models\Post;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use App\Http\Services\StudentServices;
use App\Http\Services\TeacherServices;
use App\Models\City;
use App\Models\SocialLink;

class StudentOperationController extends Controller
{
    private StudentResponse $response1;
    private TeacherResponse $teacher_response;
    private TeacherServices $teacherServices;
    private StudentServices $studentServices;

    public function __construct(
        StudentResponse $response1,
        TeacherResponse $teacher_response,
        TeacherServices $teacherServices,
        StudentServices $studentServices
    )
    {
        $this->response1 = $response1;
        $this->teacher_response = $teacher_response;
        $this->teacherServices = $teacherServices;
        $this->studentServices = $studentServices;
    }

    public function displayTeacherInfo(DisplayTeacherInfoRequest $request)
    {
        $teacherId = $request->teacher_id;
        $authUserId = $request->type_id;

        // Get the approved courses for the teacher
        $display_myCourses_approved = Teacher::query()
            ->join('courses', 'teachers.id', '=', 'courses.teacher_id')
            ->where('teachers.id', $teacherId)
            ->where('courses.status', '=', 'published')
            ->get([
                'courses.id as course_id',
                'courses.course_name',
                'courses.course_description',
                'courses.is_free',
                'courses.price',
                'courses.created_at',
                'courses.updated_at',
            ]);

        // Get the teacher info with media
        $display_info = Teacher::query()->with('media')
            ->where('teachers.id', $teacherId)
            ->get();

        // Get the posts by the teacher with media
        $teacherPosts = Post::query()->with('media')
            ->join('teachers', 'teachers.id', '=', 'posts.teacher_id')
            ->where('teacher_id', $teacherId)
            ->get([
                'posts.id',
                'teachers.id as teacher_id',
                'teachers.first_name',
                'teachers.last_name',
                'posts.text',
                'posts.created_at',
            ]);

        // Process the posts data
        $homeData = $this->teacherServices->addTeacherPhoto($teacherPosts);
        $homeData = $this->studentServices->addIsLiked($homeData);
        $homeData = $this->teacherServices->addIsFollowed($homeData, 'student');
        $homeData = $this->teacherServices->addPostLikes($homeData);

        // Count the number of followers for the teacher
        $followerCount = Follow::where('teacher_id', $teacherId)->count();

        // Check if the authenticated user follows the teacher
        $isFollowing = Follow::where('teacher_id', $teacherId)
            ->where('follower_type', $request->type)
            ->where('follower_id', $authUserId)
            ->exists();

        // Get the teacher's location info
        $infoLocation = City::query()
            ->join('locations', 'cities.id', '=', 'locations.city_id')
            ->join('teachers', 'locations.id', '=', 'teachers.location_id')
            ->where('teachers.id', $teacherId)
            ->get(['cities.city_name', 'locations.location_name']);

        // Get the teacher's social links
        $infoSocaial = SocialLink::query()
            ->where('teacher_id', $teacherId)
            ->get(['type', 'link']);
        return $this->response1->displayTeacherInfoResponse($display_myCourses_approved, $display_info, $homeData, $followerCount, $isFollowing, $infoLocation, $infoSocaial);

//        // Prepare the response data
//        return response()->json([
//            'approved_courses' => $display_myCourses_approved,
//            'teacher_info' => $display_info,
//            'posts' => $homeData,
//            'info' => [
//                'follower_count' => $followerCount,
//                'is_following' => $isFollowing,
//                'location_info' => $infoLocation,
//                'social_info' => $infoSocaial
//            ]
//        ]);
    }
}
