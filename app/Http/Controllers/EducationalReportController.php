<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\ReportStudentRequest;
use App\Http\Services\Responses\EducationalResponse;
use App\Models\Educational;
use App\Models\Report;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EducationalReportController extends Controller
{
    private EducationalResponse $educational_response;

    public function __construct(EducationalResponse $educational_response)
    {
        $this->educational_response = $educational_response;
    }

    public function addReportEducational(ReportStudentRequest $request)
    {

        $addReport = Educational::query()->where('id', Auth::id())->first();
        Report::query()->create([
            'reporter_type' => 'educational',
            'reported_at_type' => 'teacher',
            'reporter_id' => Auth::id(),
            'reported_at_id' => $request->teacher_id,
            'message' => $request->message,
        ]);
        return $this->educational_response->reportEducationalResponse();


    }

}
