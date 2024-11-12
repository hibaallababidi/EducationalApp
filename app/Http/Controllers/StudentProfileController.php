<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\StudentCompleteInfoRequest;
use App\Http\Requests\Student\StudentEditProfileRequest;
use App\Http\Requests\Student\VerifyEmailUpdateRequest;
use App\Http\Services\Responses\StudentResponse;
use App\Http\Services\StudentServices;
use App\Http\Services\Teacher\ProfileService;
use App\Models\City;
use App\Models\Location;
use App\Models\Student;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Ichtrojan\Otp\Otp;
use JetBrains\PhpStorm\Pure;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class StudentProfileController extends Controller
{
    private StudentResponse $response1;
    private StudentServices $studentServices;
    private ProfileService $profileService;


    #[Pure] public function __construct()
    {
        $this->response1 = new StudentResponse();
        $this->studentServices = new StudentServices();
        $this->profileService = new ProfileService();
    }

    public function studentCompleteInfo(StudentCompleteInfoRequest $request)
    {
        $completeInfo = Student::query()->where('id', Auth::id())
            ->first();
        $completeInfo->update(['phone_number' => $request->phone_number,]);
        if ($request->has('location_id')) {
            $completeInfo->update(['location_id' => $request->location_id,]);
        }
        if ($request->has('photo')) {
            $completeInfo->addMedia($request->photo)->toMediaCollection('ProfilePicture');
        }

        $this->studentServices->saveFavouriteSpecializations($request->specializations);
        $student = $this->profileService->getProfile($completeInfo, 'student');
        return $this->response1->studentCompleteInfoResponse($student);
    }

    public function studentEditProfile(StudentEditProfileRequest $request)
    {
        $student = Student::query()->where('id', Auth::id())->first();

        if ($request->has('photo')) {
            $student->clearMediaCollection('ProfilePicture');
            $student->addMedia($request->photo)->toMediaCollection('ProfilePicture');
        }
        if ($request->has('first_name')) {
            $student->first_name = $request->input('first_name');
        }
        if ($request->has('last_name')) {
            $student->last_name = $request->input('last_name');
        }
        if ($request->has('location_id')) {
            $student->location_id = $request->input('location_id');
        }
        if ($request->has('phone_number')) {
            $student->phone_number = $request->input('phone_number');
        }
        if ($request->has('Specializations')) {
            // Clear existing specializations
            DB::table('favourite_specializations')->where('student_id', $student->id)->delete();

            // Add new specializations
            $specializations = $request->input('Specializations');
            foreach ($specializations as $specializationId) {
                DB::table('favourite_specializations')->insert([
                    'student_id' => $student->id,
                    'specialization_id' => $specializationId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        $student->save();
        return $this->response1->studentEditProfileResponse();
    }
    //مؤجل
//    public function verifyEmailUpdate(VerifyEmailUpdateRequest $request)
//    {
//
//        $otp = $this->otp->validate($request->email, $request->code);
//        if (!$otp->status)
//            return response()->json([
//                'status' => $otp->status,
//                'message' => trans('messages.code_error'),
//                'data' => []
//            ], 401);
//        $user = Auth::user();
//
//        $user->update([
//            'email' => $request->email,
//            'email_verified_at' => now()
//        ]);
//        $token = JWTAuth::fromUser($user);
//        $data = $user;
//        $data['token'] = $token;
//        return response()->json([
//            'status' => true,
//            'message' => trans('messages.email_verified'),
//            'data' => $data,
//        ], 200);
//
//
//    }

    public function displayMyProfile(): JsonResponse
    {
        $profile = Student::query()
            ->find(Auth::id());
        $photo = $profile->getMedia('ProfilePicture')->first();
        if ($photo != null) {
            $profile['photo'] = $photo->original_url;
        } else
            $profile['photo'] = null;
        if ($profile->location_id != null) {
            $location = Location::query()->find($profile->location_id);
            $profile['location'] = $location->location_name;
            $city = City::query()->find($location->city_id);
            $profile['city'] = $city->city_name;
        }
        $profile['user_type'] = 'student';
        return response()->json([
            'status' => true,
            'message' => trans('messages.my_profile'),
            'data' => $profile
        ]);
    }
}
