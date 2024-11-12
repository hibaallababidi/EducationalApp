<?php

namespace App\Http\Controllers\Auth;


use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\TeacherRegisterRequest;
use App\Http\Requests\AuthRequests\EmailVerificationRequest;
use App\Http\Requests\AuthRequests\ResetPasswordRequest;
use App\Http\Requests\AuthRequests\UpdateForgotPasswordRequest;
use App\Http\Services\EmailVerification;
use App\Http\Services\Responses\AuthResponse;
use App\Models\Teacher;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    private AuthResponse $response;
    private EmailVerification $emailVerification;

    public function __construct()
    {
        $this->response = new AuthResponse();
        $this->emailVerification = new EmailVerification();
    }

    public function register(TeacherRegisterRequest $request): JsonResponse
    {
//        $teacher = Teacher::query()->where('email', $request->email)->first();
//        if (isset($teacher) && !is_null($teacher->email_verified_at))
//            return $this->response->emailTakenResponse();
//        else if (isset($teacher) && is_null($teacher->email_verified_at))
//            $teacher->delete();
        $this->saveTeacher($request);
        return $this->response->registerSuccessResponse();
    }

    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        $otp = $this->emailVerification->getOtp($request);
        if (!$otp->status)
            return $this->response->otpFailedResponse($otp);
        $teacher = Teacher::query()->where('email', $request->email)->first();
        $teacher->update(['email_verified_at' => now()]);
        $data = $this->emailVerification->getToken($teacher);
        return $this->response->otpSuccessResponse($data);
    }

    public function verifyPasswordEmail(EmailVerificationRequest $request): JsonResponse
    {
        $otp = $this->emailVerification->getOtp($request);
        if (!$otp->status)
            return $this->response->otpFailedResponse($otp);
        return $this->response->passwordEmailVerifiedResponse();
    }

    public function updatePassword(UpdateForgotPasswordRequest $request): JsonResponse
    {
        $teacher = Teacher::query()->where('email', $request->email)->first();
        $teacher->update(['password' => Hash::make($request->password)]);
//        $educational->tokens()->delete();
        $data = $this->emailVerification->getToken($teacher);
        return $this->response->passwordUpdatedResponse($data);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $teacher = Auth::user();
        if (Hash::check($request->current_password, $teacher->getAuthPassword())) {
            $teacher->update([
                'password' => Hash::make($request->new_password)
            ]);
            $data = $this->emailVerification->getToken($teacher);
            return $this->response->passwordUpdatedResponse($data);
        } else
            return $this->response->passwordErrorResponse();
    }

    private function saveTeacher($request)
    {
        $teacher = Teacher::query()->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
//        DeviceToken::create([
//            'user_id' => $user->id,
//            'device_token' => $request->device_token
//        ]);
        $teacher->notify(new EmailVerificationNotification());
    }


//    public function login(Request $request)
//    {
//        $info = [
//            'email' => $request->email,
//            'password' => $request->password
//        ];
//        if (Auth::guard('teachers')->attempt($info)) {
//            $teachers = Teacher::query()->where('email', $request->email)->first();
//            if ($teachers->email_verified_at) {
//                /*
//                 * handle admin blocking
//                 */
//                $token = JWTAuth::fromUser($teachers);
//                $data = $teachers;
//                $data['token'] = $token;
////                DeviceToken::where('user_id', $educational->id)
////                    ->update([
////                        'device_token' => $request->device_token
////                    ]);
//                return $this->response->loginSuccessResponse($data);
//            } else {
//                $teachers->delete();
//                return $this->response->emailNotVerifiedResponse($teachers);
//            }
//        } else {
//            return $this->response->passwordErrorResponse();
//        }
//    }


//    public function forgotPassword(ForgotPasswordRequest $request)
//    {
//        $teacher = Teacher::query()->where('email', $request->email)->first();
//        if (!isset($teacher))
//            return $this->response->accountNotFoundResponse();
//        $teacher->notify(new ForgotPasswordNotification());
//        return $this->response->forgotPasswordResponse();
//    }

}
