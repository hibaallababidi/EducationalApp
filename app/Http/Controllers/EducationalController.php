<?php

namespace App\Http\Controllers;

use App\Http\Services\EducationalServices;
use App\Http\Services\Responses\EducationalResponse;
use App\Models\City;
use App\Models\Educational;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EducationalController extends Controller
{
    private EducationalServices $services;
    private EducationalResponse $response;

    public function __construct()
    {
        $this->services = new EducationalServices();
        $this->response = new EducationalResponse();
    }


    public function educationalHomePage()
    {
        $result = $this->services->getHome();
        return $this->response->educationalHomePageResponse($result);
    }

    public function displayMyProfile(): JsonResponse
    {
        $profile = $this->services->getMyProfile();
        return $this->response->displayMyProfileResponse($profile);
    }

}
