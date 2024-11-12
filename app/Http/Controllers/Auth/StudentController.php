<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\StudentRegisterRequest;
use App\Http\Requests\AuthRequests\EmailVerificationRequest;
use App\Http\Requests\AuthRequests\ResetPasswordRequest;
use App\Http\Requests\AuthRequests\UpdateForgotPasswordRequest;
use App\Http\Services\EmailVerification;
use App\Http\Services\Responses\AuthResponse;
use App\Models\Student;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    private AuthResponse $response;
    private EmailVerification $emailVerification;

    public function __construct()
    {
        $this->response = new AuthResponse();
        $this->emailVerification = new EmailVerification();
    }

    public function register(StudentRegisterRequest $request): JsonResponse
    {
//        $student = Student::query()->where('email', $request->email)->first();
//        if (isset($student) && !is_null($student->email_verified_at))
//            return $this->response->emailTakenResponse();
//        else if (isset($student) && is_null($student->email_verified_at))
//            $student->delete();
        $this->saveStudent($request);
        return $this->response->registerSuccessResponse();
    }

    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        $otp = $this->emailVerification->getOtp($request);
        if (!$otp->status)
            return $this->response->otpFailedResponse($otp);
        $student = Student::query()->where('email', $request->email)->first();
        $student->update(['email_verified_at' => now()]);
        $data = $this->emailVerification->getToken($student);
        return $this->response->otpSuccessResponse($data);
    }

//    public function forgotPassword(ForgotPasswordRequest $request)
//    {
//        $student = Student::query()->where('email', $request->email)->first();
//        if (!isset($student))
//            return $this->response->accountNotFoundResponse();
//        $student->notify(new ForgotPasswordNotification());
//        return $this->response->forgotPasswordResponse();
//    }

    public function verifyPasswordEmail(EmailVerificationRequest $request): JsonResponse
    {
        $otp = $this->emailVerification->getOtp($request);
        if (!$otp->status)
            return $this->response->otpFailedResponse($otp);
        return $this->response->passwordEmailVerifiedResponse();
    }

    public function updatePassword(UpdateForgotPasswordRequest $request): JsonResponse
    {
        $student = Student::query()->where('email', $request->email)->first();
        $student->update(['password' => Hash::make($request->password)]);
//        $educational->tokens()->delete();
        $data = $this->emailVerification->getToken($student);
        return $this->response->passwordUpdatedResponse($data);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $student = Auth::user();
        if (Hash::check($request->current_password, $student->getAuthPassword())) {
            $student->update([
                'password' => Hash::make($request->new_password)
            ]);
            $data = $this->emailVerification->getToken($student);
            return $this->response->passwordUpdatedResponse($data);
        } else
            return $this->response->passwordErrorResponse();
    }

    private function saveStudent($request)
    {
        $student = Student::query()->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
//        DeviceToken::create([
//            'user_id' => $user->id,
//            'device_token' => $request->device_token
//        ]);
        $student->notify(new EmailVerificationNotification());
    }

//    public function login(Request $request)
//    {
//        $info = [
//            'email' => $request->email,
//            'password' => $request->password
//        ];
//        if (Auth::guard('students')->attempt($info)) {
//            $student = Student::query()->where('email', $request->email)->first();
//            if ($student->email_verified_at) {
//                /*
//                 * handle admin blocking
//                 */
//                $token = JWTAuth::fromUser($student);
//                $data = $student;
//                $data['token'] = $token;
////                DeviceToken::where('user_id', $educational->id)
////                    ->update([
////                        'device_token' => $request->device_token
////                    ]);
//                return $this->response->loginSuccessResponse($data);
//            } else {
//                $student->delete();
//                return $this->response->emailNotVerifiedResponse($student);
//            }
//        } else {
//            return $this->response->passwordErrorResponse();
//        }
//    }

}
