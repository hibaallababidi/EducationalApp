<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\LoginRequest;
use App\Http\Services\EmailVerification;
use App\Http\Services\Responses\AuthResponse;
use App\Models\Educational;
use App\Models\Student;
use App\Models\Teacher;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    private AuthResponse $response;
    private EmailVerification $emailVerification;
    private string $userType;
    private $user;

    public function __construct()
    {
        $this->response = new AuthResponse();
        $this->emailVerification = new EmailVerification();
    }

    public function deviceToken(Request $request)
    {
        $user = Auth::user();
        $user->device_token = $request->device_token;
        $user->save();
        return response()->json([
            'status' => true,
            'message' => trans('messages.sucess'),
            'data' => []
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $info = [
            'email' => $request->email,
            'password' => $request->password
        ];
        if (Student::query()->where('email', $request->email)->exists()) {
            if (Auth::guard('students')->attempt($info)) {
                $this->studentLogIn($request);
            } else
                return $this->response->passwordErrorResponse();
        } else if (Educational::query()->where('email', $request->email)->exists()) {
            if (Auth::guard('educationals')->attempt($info)) {
                $this->user = Educational::query()->where('email', $request->email)->first();
                if ($this->user->is_accepted == 1)
                    $this->educationalLogIn();
                else {
                    return $this->response->educationalNotAcceptedResponse();
                }
            } else
                return $this->response->passwordErrorResponse();
        } else if (Teacher::query()->where('email', $request->email)->exists()) {
            if (Auth::guard('teachers')->attempt($info)) {
                $this->teacherLogIn($request);
            } else
                return $this->response->passwordErrorResponse();
        } else {
            return $this->response->accountNotFoundResponse();
        }

        if ($this->user->email_verified_at) {
            /*
             * handle admin blocking
             */
            $data = $this->emailVerification->getToken($this->user);
//                DeviceToken::where('user_id', $user->id)
//                    ->update([
//                        'device_token' => $request->device_token
//                    ]);
            $data['user_type'] = $this->userType;
//            $data['aaamedia']=$this->user->getMedia('ProfilePicture');
            return $this->response->loginSuccessResponse($data);
        } else {
            $this->user->notify(new EmailVerificationNotification());
            return $this->response->emailNotVerifiedResponse($this->userType);
        }
    }

    private function studentLogIn($request)
    {
        $this->user = Student::query()
            ->where('email', $request->email)->first();
        $this->userType = 'student';
        $this->getPhoto();
    }

    private function educationalLogIn()
    {
        $this->userType = 'educational';
        $this->getPhoto();
    }

    private function teacherLogIn($request)
    {
        $this->user = Teacher::query()->where('email', $request->email)->first();
        $this->userType = 'teacher';
//        $location = Location::query()->find($this->user->location_id);
//        $this->user['location_name'] = $location->location_name;
        $this->getPhoto();
        $this->getCv();
    }

    private function getPhoto()
    {
        $photo = $this->user->getMedia('ProfilePicture')->first();
        if ($photo != null) {
            $this->user['photo'] = $photo->original_url;
        } else
            $this->user['photo'] = null;
    }

    private function getCv()
    {
        $cv = $this->user->getMedia('CV')->first();
        if ($cv != null)
            $this->user['cv'] = $cv->original_url;
        else
            $this->user['cv'] = null;
    }
}
