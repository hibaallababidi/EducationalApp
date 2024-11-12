<?php

namespace App\Http\Controllers;

use App\Http\Services\Responses\StudentResponse;
use App\Http\Services\StudentServices;
use App\Models\CourseEvaluation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentCoursesController extends Controller
{
    private StudentServices $services;
    private StudentResponse $response;

    public function __construct()
    {
        $this->services = new StudentServices();
        $this->response = new StudentResponse();
    }


    public function displayTopCourses(): JsonResponse
    {
        $courses = $this->services->getTopCourses();
        return $this->response->displayTopCoursesResponse($courses);
    }
}
