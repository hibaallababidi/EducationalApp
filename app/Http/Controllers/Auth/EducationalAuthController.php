<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\EducationalRegisterRequest;
use App\Http\Requests\AuthRequests\EmailVerificationRequest;
use App\Http\Requests\AuthRequests\ResetPasswordRequest;
use App\Http\Requests\AuthRequests\UpdateForgotPasswordRequest;
use App\Http\Services\EmailVerification;
use App\Http\Services\Responses\AuthResponse;
use App\Models\Educational;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EducationalAuthController extends Controller
{
    private AuthResponse $response;
    private EmailVerification $emailVerification;

    public function __construct()
    {
        $this->response = new AuthResponse();
        $this->emailVerification = new EmailVerification();
    }

    public function register(EducationalRegisterRequest $request): JsonResponse
    {
//        $educational = Educational::query()->where('email', $request->email)->first();
//        if (isset($educational) && !is_null($educational->email_verified_at))
//            return $this->response->emailTakenResponse();
//        else if(isset($educational) && is_null($educational->email_verified_at))
//            $educational->delete();
        $this->saveEducational($request);
        return $this->response->registerSuccessResponse();
    }

    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        $otp = $this->emailVerification->getOtp($request);
        if (!$otp->status)
            return $this->response->otpFailedResponse($otp);
        $educational = Educational::query()->where('email', $request->email)->first();
        $educational->update(['email_verified_at' => now()]);
//        $data = $this->emailVerification->getToken($educational);
        return $this->response->otpSuccessResponse($educational);
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
        $educational = Educational::query()->where('email', $request->email)->first();
        $educational->update(['password' => Hash::make($request->password)]);
//        $educational->tokens()->delete();
        $data = $this->emailVerification->getToken($educational);
        return $this->response->passwordUpdatedResponse($data);
    }

    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $educational = Auth::user();
        if (Hash::check($request->current_password, $educational->getAuthPassword())) {
            $educational->update([
                'password' => Hash::make($request->new_password)
            ]);
            $data = $this->emailVerification->getToken($educational);
            return $this->response->passwordUpdatedResponse($data);
        } else
            return $this->response->passwordErrorResponse();
    }

    /** @noinspection PhpUndefinedMethodInspection */
    public function saveEducational($request)
    {
        $educational = Educational::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'location_id' => $request->location_id,
            'type' => $request->type,
            'phone_number' => $request->phone_number,
        ]);
//        DeviceToken::create([
//            'user_id' => $user->id,
//            'device_token' => $request->device_token
//        ]);
        $educational->notify(new EmailVerificationNotification());
    }


    //    public function login(LoginRequest $request)
//    {
//        if(!(Educational::query()->where('email',$request->email)->exists()))
//            return $this->response->accountNotFoundResponse();
//        $info = [
//            'email' => $request->email,
//            'password' => $request->password
//        ];
//        if (Auth::guard('educationals')->attempt($info)) {
//            $educational = Educational::query()->where('email', $request->email)->first();
//            if ($educational->email_verified_at) {
//                /*
//                 * handle admin blocking
//                 */
//                $data=$this->emailVerification->getToken($educational);
////                DeviceToken::where('user_id', $educational->id)
////                    ->update([
////                        'device_token' => $request->device_token
////                    ]);
//                return $this->response->loginSuccessResponse($data);
//            } else {
//                $educational->delete();
//                return $this->response->emailNotVerifiedResponse($educational);
//            }
//        } else {
//            return $this->response->passwordErrorResponse();
//        }
//    }

//    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
//    {
//        $educational = Educational::query()->where('email', $request->email)->first();
//        if (!isset($educational))
//            return $this->response->accountNotFoundResponse();
//        $educational->notify(new ForgotPasswordNotification());
//        return $this->response->forgotPasswordResponse();
//    }

}
