<?php

namespace App\Http\Controllers;

use App\Http\Requests\Educational\SearchCVRequest;
use App\Http\Requests\Teacher\Profile\DisplayTeacherProfileRequest;
use App\Http\Requests\Teacher\Profile\TeacherCompleteInfoRequest;
use App\Http\Requests\Teacher\Profile\TeacherEditProfileRequest;
use App\Http\Services\Responses\TeacherResponse;
use App\Http\Services\Teacher\ProfileService;
use App\Models\Follow;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    private ProfileService $services;
    private TeacherResponse $response;

    public function __construct()
    {
        $this->response = new TeacherResponse();
        $this->services = new ProfileService();
    }

    public function searchCVs(SearchCVRequest $request): JsonResponse
    {
        $keywords = $request->input('keywords');
        $results = $this->services->searchCVs($keywords);

        return response()->json([
            'status' => true,
            'message' => 'Search results',
            'data' => $results,
        ], 200);
    }

    public function teacherInformation()
    {
        $teacherInformation = Teacher::query()
            ->where('id', Auth::id())
            ->first();
//            ->get();
        $photo = $teacherInformation->getMedia('ProfilePicture')->first();
        if ($photo != null) {
            $teacherInformation['photo'] = $photo->original_url;
        } else
            $teacherInformation['photo'] = null;
        $cv = $teacherInformation->getMedia('CV')->first();
        if ($cv != null)
            $teacherInformation['cv'] = $cv->original_url;
        else
            $teacherInformation['cv'] = null;
        return response()->json([
            'status' => true,
            'message' => trans('messages.teacher_profile'),
            'data' => $teacherInformation
        ], 200);

    }

    public function teacherCompleteInfo(TeacherCompleteInfoRequest $request): JsonResponse
    {
        $teacher = Auth::user();
        $teacher = $this->services->saveCompleteInfo($teacher, $request);
        $teacher = $this->services->getProfile($teacher, 'teacher');
        return $this->response->completeInfoResponse($teacher);

    }

    public function displayTeachers(): JsonResponse
    {
        $teachers = $this->services->getTeachers();
        return $this->response->displayTeachersResponse($teachers);
    }

    public function displayTeacherProfile(DisplayTeacherProfileRequest $request): JsonResponse
    {
        $teacher = $this->services->getTeacherProfile($request);
        return $this->response->displayTeacherProfileResponse($teacher);
    }

    public function teacherEditProfile(TeacherEditProfileRequest $request): JsonResponse
    {
        $teacher = Auth::user();
        $this->services->saveTeacherEditProfile($teacher, $request);
        return $this->response->teacherEditProfileResponse();
    }

    public function myFollowersCount(): JsonResponse
    {
        $count = Follow::query()
            ->where('teacher_id', Auth::id())
            ->count();
        return $this->response->myFollowersCountResponse($count);
    }
}
