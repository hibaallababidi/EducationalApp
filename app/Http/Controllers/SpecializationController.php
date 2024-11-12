<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\SpecializationRequest;
use App\Http\Requests\Student\AddFavouriteSpecializationsRequest;
use App\Http\Services\Responses\AdminResponse;
use App\Http\Services\Responses\StudentResponse;
use App\Http\Services\StudentServices;
use App\Http\Services\TeacherServices;
use App\Models\Specialization;
use App\Http\Services\Responses\TeacherResponse;
use Illuminate\Http\JsonResponse;

use JetBrains\PhpStorm\Pure;

class SpecializationController extends Controller
{
    private TeacherResponse $teacher_response;
    private AdminResponse $admin_response;
    private TeacherServices $services;
    private StudentServices $studentServices;
    private StudentResponse $studentResponse;

    #[Pure] public function __construct()
    {
        $this->teacher_response = new TeacherResponse();
        $this->services = new TeacherServices();
        $this->admin_response = new AdminResponse();
        $this->studentServices = new StudentServices();
        $this->studentResponse = new StudentResponse();
    }

    public function addSpecializations(SpecializationRequest $request)
    {
        $addSpecializations = Specialization::create([
            'specialization' => $request->specialization,
        ]);
        return $this->admin_response->specializationResponse();


    }

    public function getSpecializations(): JsonResponse
    {
        $result = Specialization::query()->get(['id', 'specialization']);
        return $this->teacher_response->getSpecializationsResponse($result);
    }

    public function displayMySpecializations(): JsonResponse
    {
        $specializations = $this->services->getMySpecializations();
        return $this->teacher_response->displayMySpecializationsResponse($specializations);

    }

//    public function addFavouriteSpecializations(AddFavouriteSpecializationsRequest $request): JsonResponse
//    {
//        $this->studentServices->saveFavouriteSpecializations($request->specializations);
//        return $this->studentResponse->addFavouriteSpecializationsResponse();
//    }
}
