<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\AcceptEducationalSubmissionRequest;
use App\Http\Services\AdminServices;
use App\Http\Services\Responses\AdminResponse;
use App\Models\Educational;
use Illuminate\Http\JsonResponse;
use JetBrains\PhpStorm\Pure;
use function Symfony\Component\String\s;

class EducationalSubmissionsController extends Controller
{
    private AdminServices $services;
    private AdminResponse $responses;


    #[Pure] public function __construct()
    {
        $this->services = new AdminServices();
        $this->responses = new AdminResponse();
    }


    public function displayEducationalSubmissions(): JsonResponse
    {
        $submissions = $this->services->getEducationalSubmissions();
        return $this->responses->displayEducationalSubmissionsResponse($submissions);
    }

    public function acceptEducationalSubmission(AcceptEducationalSubmissionRequest $request): JsonResponse
    {
        $this->services->acceptSubmission($request);
        //send notification
        return $this->responses->acceptEducationalSubmissionsResponse();
    }

    public function rejectEducationalSubmission(AcceptEducationalSubmissionRequest $request): JsonResponse
    {
        $educational = Educational::query()->find($request->educational_id);
        //send notification
        $educational->delete();
        return $this->responses->rejectEducationalSubmissionsResponse();
    }
}
