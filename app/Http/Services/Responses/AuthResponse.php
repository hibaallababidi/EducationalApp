<?php

namespace App\Http\Services\Responses;

use Illuminate\Http\JsonResponse;

class AuthResponse
{
    public function accountNotFoundResponse(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => trans('auth.account_not_found'),
            'data' => []
        ], 400);
    }

    /*
    public function emailTakenResponse(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => trans('messages.email_taken'),
            'data' => []
        ], 400);
    }
    */

    public function registerSuccessResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('auth.register'),
            'data' => []
        ], 201);
    }

    public function otpFailedResponse($otp): JsonResponse
    {
        return response()->json([
            'status' => $otp->status,
            'message' => $otp->message,//trans('messages.code_error'),
            'data' => []
        ], 401);
    }

    public function otpSuccessResponse($data): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('auth.email_verified'),
            'data' => $data,
        ]);
    }

    public function loginSuccessResponse($data): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('auth.login'),
            'data' => $data,
        ], 200);
    }

    public function educationalNotAcceptedResponse(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => trans('auth.educational_not_accepted'),
            'data' => [],
        ], 401);
    }

    public function emailNotVerifiedResponse($userType): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => trans('auth.email_not_verified'),
            'data' => ['user_type' => $userType],
        ], 401);
    }

    public function passwordErrorResponse(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => trans('auth.password_error'),
            'data' => []
        ], 401);
    }

    public function forgotPasswordResponse($userType): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('auth.verify_email'),
            'data' => ['user_type' => $userType],
        ]);
    }

    public function passwordEmailVerifiedResponse(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('auth.email_verified'),
            'data' => []
        ]);
    }

    public function passwordUpdatedResponse($data): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('auth.password_updated'),
            'data' => $data,
        ]);
    }

}
