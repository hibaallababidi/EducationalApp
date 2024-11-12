<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\LoginRequest;
use App\Http\Requests\AuthRequests\EmailVerificationRequest;
use App\Http\Requests\AuthRequests\ForgotPasswordRequest;
use App\Http\Requests\AuthRequests\ResetPasswordRequest;
use App\Http\Requests\AuthRequests\UpdateForgotPasswordRequest;
use App\Http\Services\EmailVerification;
use App\Http\Services\Responses\AuthResponse;
use App\Models\Admin;
use App\Models\Educational;
use App\Notifications\ForgotPasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    private AuthResponse $response;
    private EmailVerification $emailVerification;

    public function __construct()
    {
        $this->response = new AuthResponse();
        $this->emailVerification = new EmailVerification();
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (!(Admin::query()->where('email', $request->email)->exists()))
            return $this->response->accountNotFoundResponse();
        $info = [
            'email' => $request->email,
            'password' => $request->password
        ];
        if (Auth::guard('admins')->attempt($info)) {
            $admin = Admin::query()->where('email', $request->email)->first();
//            if ($admin->email_verified_at) {
            /*
             * handle admin blocking
             */
            $data = $this->emailVerification->getToken($admin);
//                DeviceToken::where('user_id', $admin->id)
//                    ->update([
//                        'device_token' => $request->device_token
//                    ]);
            return $this->response->loginSuccessResponse($data);
//            } else {
//                $admin->delete();
//                return $this->response->emailNotVerifiedResponse($admin);
//            }
        } else {
            return $this->response->passwordErrorResponse();
        }
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $admin = Admin::query()->where('email', $request->email)->first();
        if (!isset($admin))
            return $this->response->accountNotFoundResponse();
        $admin->notify(new ForgotPasswordNotification());
        return $this->response->forgotPasswordResponse('admin');
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
        $admin = Admin::query()->where('email', $request->email)->first();
        $admin->update(['password' => Hash::make($request->password)]);
//        $admin->tokens()->delete();
        $data = $this->emailVerification->getToken($admin);
        return $this->response->passwordUpdatedResponse($data);
    }

    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $admin = Auth::user();
        if (Hash::check($request->current_password, $admin->getAuthPassword())) {
            $admin->update([
                'password' => Hash::make($request->new_password)
            ]);
            $data = $this->emailVerification->getToken($admin);
            return $this->response->passwordUpdatedResponse($data);
        } else
            return $this->response->passwordErrorResponse();
    }
}
