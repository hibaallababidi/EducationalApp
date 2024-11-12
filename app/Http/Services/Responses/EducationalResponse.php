<?php

namespace App\Http\Services\Responses;

use Illuminate\Http\JsonResponse;

class EducationalResponse
{

    public function educationalCompleteInfoResponse($educational)
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.educational_complete_info'),
            'data' => $educational
        ]);
    }

    public function educationalEditProfileResponse()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.educational_edit_profile'),
            'data' => []
        ]);
    }

    public function reportEducationalResponse()
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.report_educational'),
            'data' => []
        ]);
    }

    public function filterResponse($result): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.filter_educational'),
            'data' => $result
        ]);
    }

    public function educationalHomePageResponse($result): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.educationalHomePage'),
            'data' => $result
        ]);
    }

    public function displayMyProfileResponse($profile): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => trans('messages.my_profile'),
            'data' => $profile
        ]);
    }

}
