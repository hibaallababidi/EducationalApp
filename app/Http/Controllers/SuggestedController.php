<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\SuggestSpecializationRequest;
use App\Http\Services\EmailVerification;
use App\Http\Services\Responses\AuthResponse;
use App\Http\Services\Responses\StudentResponse;
use App\Models\FavouriteSpecialization;
use App\Models\SuggestedSpecialization;
use App\Http\Services\Responses\TeacherResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuggestedController extends Controller
{
    private TeacherResponse $teacher_response;
    private StudentResponse $student_response;

    public function __construct()
    {
        $this->teacher_response = new TeacherResponse();
        $this->student_response = new StudentResponse();
    }

    public function displayMyFavoriteSpecialties()
    {
        $favourite_specializations = FavouriteSpecialization::query()
            ->where('favourite_specializations.student_id', Auth::id())
            ->join('specializations', 'specializations.id', '=', 'favourite_specializations.specialization_id')
            ->get([
                'specializations.specialization',
                'specializations.id',
            ]);
        return $this->student_response->displayMyFavoriteSpecialtiesResponse($favourite_specializations);

    }

    public function suggested(SuggestSpecializationRequest $request)
    {


        $suggest_specializationRequest = SuggestedSpecialization::create([
            'name' => $request->name,
        ]);
        return $this->teacher_response->suggestedResponse();


    }

    public function displaySuggestedSpecialization()
    {
        $suggestedSpecialization = SuggestedSpecialization::
        get([
            'name',
        ]);
        return response()->json([
            'status' => true,
            'message' => trans('messages.suggestedSpecialization'),
            'data' => $suggestedSpecialization,
        ]);
    }
}
