<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\DisplayTeacherProfileRequest;
use App\Http\Services\AdminServices;
use App\Http\Services\Responses\AdminResponse;
use App\Http\Services\Responses\StudentResponse;
use App\Http\Services\TeacherServices;
use App\Models\City;
use App\Models\Follow;
use App\Models\Post;
use App\Models\SocialLink;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use JetBrains\PhpStorm\Pure;

class DisplayUsersController extends Controller
{
    private AdminServices $services;
    private AdminResponse $responses;
    private TeacherServices $teacherServices;


    #[Pure] public function __construct()
    {
        $this->services = new AdminServices();
        $this->responses = new AdminResponse();
        $this->teacherServices = new TeacherServices();
    }

    public function displayTeachers(): JsonResponse
    {
        $teachers = $this->services->getTeachers();
        return $this->responses->displayTeachersResponse($teachers);
    }

    public function displayStudents(): JsonResponse
    {
        $students = $this->services->getStudents();
        return $this->responses->displayStudentsResponse($students);
    }

    public function displayEducationals(): JsonResponse
    {
        $educationals = $this->services->getEducationals();
        return $this->responses->displayEducationalsResponse($educationals);
    }

    public function displayTeacherProfile(DisplayTeacherProfileRequest $request)
    {
        $teacherId = $request->teacher_id;

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
        $homeData = $this->teacherServices->addPostLikes($homeData);

        // Count the number of followers for the teacher
        $followerCount = Follow::where('teacher_id', $teacherId)->count();

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
        return $this->responses->displayTeacherProfileResponse($display_myCourses_approved, $display_info, $homeData, $followerCount, $infoLocation, $infoSocaial);

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
