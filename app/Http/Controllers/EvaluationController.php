<?php

namespace App\Http\Controllers;


use App\Http\Requests\Student\EvaluateCourseRequest;
use App\Http\Requests\Student\EvaluateLessonRequest;
use App\Http\Services\Responses\StudentResponse;
use App\Http\Services\StudentServices;
use Illuminate\Http\JsonResponse;
use JetBrains\PhpStorm\Pure;

class EvaluationController extends Controller
{
    private StudentServices $services;
    private StudentResponse $response;

    #[Pure] public function __construct()
    {
        $this->services = new StudentServices();
        $this->response = new StudentResponse();
    }

    public function evaluateCourse(EvaluateCourseRequest $request): JsonResponse
    {
        $this->services->saveCourseEvaluation($request);
        return $this->response->evaluateCourseResponse();
    }

    public function evaluateLesson(EvaluateLessonRequest $request): JsonResponse
    {
        $this->services->saveLessonEvaluation($request);
        return $this->response->evaluateLessonResponse();
    }
}
