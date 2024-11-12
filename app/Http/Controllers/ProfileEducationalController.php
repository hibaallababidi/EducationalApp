<?php

namespace App\Http\Controllers;

use App\Http\Requests\Educational\CompleteInfoRequest;
use App\Http\Requests\Educational\EditCompleteInfoRequest;
use App\Http\Services\EducationalServices;
use App\Http\Services\Responses\EducationalResponse;
use App\Http\Services\Teacher\ProfileService;
use App\Models\Educational;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProfileEducationalController extends Controller
{
    private EducationalResponse $educational_response;
    private ProfileService $profileServices;

    public function __construct(EducationalResponse $educational_response)
    {
        $this->educational_response = $educational_response;
        $this->profileServices = new ProfileService();
    }

    public function educationalCompleteInfo(CompleteInfoRequest $request): JsonResponse
    {
        $educationalCompleteInfo = Educational::query()->where('id', Auth::id())->first();

        $updateData = [];
        if ($request->has('location_id')) {
            $updateData['location_id'] = $request->location_id;
        }
        if ($request->has('details')) {
            $updateData['details'] = $request->details;
        }

        if (!empty($updateData)) {
            $educationalCompleteInfo->update($updateData);
        }

        if ($request->has('photo')) {
//            foreach ($request->photos as $photo) {
            $educationalCompleteInfo->addMedia($request->photo)->toMediaCollection('ProfilePicture');
            //     }
        }
        $educational = $this->profileServices->getProfile($educationalCompleteInfo, 'educational');

        return $this->educational_response->educationalCompleteInfoResponse($educational);
    }

    public function educationalEditProfile(EditCompleteInfoRequest $request)
    {

        $educational = Educational::query()->where('id', Auth::id())->first();
        if ($request->has('photo')) {
            $educational->clearMediaCollection('ProfilePicture');
            $educational->addMedia($request->photo)->toMediaCollection('ProfilePicture');
        }
        if ($request->has('name')) {
            $educational->name = $request->input('name');
        }
        if ($request->has('details')) {
            $educational->details = $request->input('details');
        }
        if ($request->has('type')) {
            $educational->type = $request->input('type');
        }
        if ($request->has('location_id')) {
            $educational->location_id = $request->input('location_id');
        }
        if ($request->has('phone_number')) {
            $educational->phone_number = $request->input('phone_number');
        }

        $educational->save();
        return $this->educational_response->educationalEditProfileResponse();
    }


}
