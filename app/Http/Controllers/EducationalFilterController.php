<?php

namespace App\Http\Controllers;

use App\Http\Requests\Educational\EducationalFilterRequest;
use App\Http\Services\FilterService;
use App\Http\Services\Responses\EducationalResponse;
use Illuminate\Http\JsonResponse;
use JetBrains\PhpStorm\Pure;

class EducationalFilterController extends Controller
{
    private FilterService $service;
    private EducationalResponse $response;

    #[Pure] public function __construct()
    {
        $this->service = new FilterService();
        $this->response = new EducationalResponse();
    }


    public function educationalFilter(EducationalFilterRequest $request): JsonResponse
    {
        $result = $this->service->filter($request);
        return $this->response->filterResponse($result);
    }


}
