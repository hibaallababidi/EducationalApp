<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\ReportStudentRequest;
use App\Http\Requests\Teacher\ReportRequest;
use App\Http\Services\Responses\AdminResponse;
use App\Http\Services\Responses\StudentResponse;
use App\Models\Report;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EducationalStudentReportController extends Controller
{

    private StudentResponse $response1;

    public function __construct()
    {


        $this->response1 = new StudentResponse();
    }

    public function addReportEducationalStudentTeacher(ReportStudentRequest $request)
    {

        $addReportStudent = Teacher::query()->where('id', $request->teacher_id)->first();
        Report::query()->create([
            'reporter_type' => $request->reporter_type,
            'reported_at_type' => 'teacher',
            'reporter_id' => Auth::id(),
            'reported_at_id' => $request->teacher_id,
            'message' => $request->message,
        ]);
        return $this->response1->reportStudentResponse1();


    }

    public function addReportTeacher(ReportRequest $request)
    {

        $addReportStudent = Teacher::query()->where('id', $request->teacher_id)->first();
        Report::query()->create([
            'reporter_type' => 'teacher',
            'reported_at_type' => $request->reported_at_type,
            'reporter_id' => Auth::id(),
            'reported_at_id' => $request->id,
            'message' => $request->message,
        ]);
        return $this->response1->reportStudentResponse1();


    }

}
