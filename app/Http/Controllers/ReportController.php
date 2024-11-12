<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\ReportRequest;
use App\Http\Requests\Student\ReportStudentRequest;
use App\Http\Services\Responses\AdminResponse;
use App\Http\Services\Responses\StudentResponse;
use App\Http\Services\Responses\TeacherResponse;
use App\Models\Report;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    private AdminResponse $admin_response;
    private StudentResponse $response1;
    public function __construct()
    {

        $this->admin_response = new AdminResponse();
        $this->response1 = new StudentResponse();
    }

    public function displayReport(ReportRequest $request)
    {
        $displayReport = Report::where('id', $request->report_id)->first();

        if ($displayReport) {
            $reporterType = $displayReport->reporter_type;
            $reportedAtType = $displayReport->reported_at_type;
            if ($reporterType == 'teacher') {
                if ($reportedAtType == 'teacher') {
                    return $this->reportedTeachert1($request->report_id);
                } elseif ($reportedAtType == 'student') {
                    return $this->reportedStudent($request->report_id);
                } elseif ($reportedAtType == 'educational') {
                    return $this->reportedEducationals($request->report_id);
                }
            } elseif ($reporterType == 'student') {
                return $this->reportedTeacher2($request->report_id);
            } elseif ($reporterType == 'educational') {
                return $this->reportedTeacher3($request->report_id);
            }
        }

    }

    /*

    public function reportedTeachert1($request){


        $displayDetailsReport=Report::where('reports.id', $request)
        ->join('teachers as t1','t1.id','=','reports.reporter_id')
            ->join('teachers as t2','t2.id','=','reports.reported_at_id')
            ->get([ 'reporter_type',
                'reported_at_type',
                'reporter_id',
                'reported_at_id',
                't1.first_name as first_name_reported ',
                't2.first_name as first_name_reporter ',
                't1.last_name as last_name_reported ',
                't2.last_name as last_name_reporter ',
                't1.email as email_reported',
                't2.email as email_reporter',
                't1.phone_number as phone_number_reported',
                't2.phone_number as phone_number_reporter',
                't1.details as details_reported',
                't2.details as details_reporter',
                'message']);
      //  return $displayDetailsReport;
        return $this->admin_response->displayDetailsReportResponse($displayDetailsReport);
    }

    public function reportedStudent($request){


        $displayDetailsReport = Report::where('reports.id', $request)
            ->join('teachers','teachers.id','=','reports.reporter_id')
            ->join('students','students.id','=','reports.reported_at_id')
            ->get([
                'reporter_type',
                'reported_at_type',
                'reporter_id',
                'reported_at_id',
                'teachers.first_name as first_name_reporter',
                'students.first_name as first_name_reported',
                'teachers.last_name as last_name_reporter',
                'students.last_name as last_name_reported',
                'teachers.email as email_reporter',
                'students.email as email_reported',
                'teachers.phone_number as phone_number_reporter',
                'students.phone_number as phone_number_reported',
                'message'
            ]);
        //return $displayDetailsReport;
        return $this->admin_response->displayReportStudentResponse($displayDetailsReport);
    }

    public function reportedEducationals($request){
        $displayDetailsReport = Report::where('reports.id', $request)
            ->join('teachers', 'teachers.id', '=', 'reports.reporter_id')
            ->join('educationals', 'educationals.id', '=', 'reports.reported_at_id')
            ->get([
                'reporter_type',
                'reported_at_type',
                'reporter_id',
                'reported_at_id',
                'teachers.first_name as first_name_reporter',
                'educationals.name as name_reported',
                'teachers.last_name as last_name_reporter',
                'teachers.email as email_reporter',
                'educationals.email as educationals_reported',
                'teachers.phone_number as phone_number_reporter',
                'educationals.phone_number as phone_number_reported',
                'educationals.type as type_educationals',
                'message'
            ]);
        //return $displayDetailsReport;
        return $this->admin_response->displayReportEducationalsResponse($displayDetailsReport);
    }

    public function reportedTeacher2($request){
        $displayDetailsReport = Report::where('reports.id', $request)
            ->join('students', 'students.id', '=', 'reports.reporter_id')
            ->join('teachers', 'teachers.id', '=', 'reports.reported_at_id')
            ->get([
                'reporter_type',
                'reported_at_type',
                'reporter_id',
                'reported_at_id',
                'teachers.first_name as first_name_reported',
                'students.first_name as first_name_reporter',
                'teachers.last_name as last_name_reported',
                'students.last_name as last_name_reporter',
                'teachers.email as email_reported',
                'students.email as email_reporter',
                'teachers.phone_number as phone_number_reported',
                'students.phone_number as phone_number_reporter',
                'message'
            ]);
    //    return $displayDetailsReport;
        return $this->admin_response->displayreportedTeacherResponse($displayDetailsReport);
    }

    public function reportedTeacher3($request){
        $displayDetailsReport = Report::where('reports.id', $request)
            ->join('educationals', 'educationals.id', '=', 'reports.reporter_id')
            ->join('teachers', 'teachers.id', '=', 'reports.reported_at_id')
            ->get([
                'reporter_type',
                'reported_at_type',
                'reporter_id',
                'reported_at_id',
                'teachers.first_name as first_name_reported',
                'teachers.last_name as last_name_reported',
                'educationals.name as name_reporter',
                'teachers.email as email_reported',
                'educationals.email as email_reporter',
                'teachers.phone_number as phone_number_reported',
                'educationals.phone_number as phone_number_reporter',
                'educationals.type as type_reporter',
                'message'
            ]);
     //   return $displayDetailsReport;
        return $this->admin_response->displayreport_TeacherResponse($displayDetailsReport);
    }

    public function displayReports()
    {
        $displayReports = Report::all();
        $results = [];

        foreach ($displayReports as $report) {
            $reporterType = $report->reporter_type;
            $reportedAtType = $report->reported_at_type;

            if ($reporterType == 'teacher') {
                if ($reportedAtType == 'teacher') {
                    $results[] = $this->reportedTeachert1($report->id);
                } elseif ($reportedAtType == 'student') {
                    $results[] = $this->reportedStudent($report->id);
                } elseif ($reportedAtType == 'educational') {
                    $results[] = $this->reportedEducationals($report->id);
                }
            } elseif ($reporterType == 'student') {
                $results[] = $this->reportedTeacher2($report->id);
            } elseif ($reporterType == 'educational') {
                $results[] = $this->reportedTeacher3($report->id);
            }
        }

        return $this->admin_response->resultReportsResponse1($results);
    }


    public function addReportStudent(ReportStudentRequest $request){

        $addReportStudent=Teacher::query()->where('id',$request->teacher_id)->first();
        Report::query()->create([
            'reporter_type'=>'student',
            'reported_at_type'=>'teacher',
            'reporter_id' => Auth::id(),
            'reported_at_id' => $request->teacher_id,
            'message'=>$request->message,
        ]);
        return $this->response1->reportStudentResponse1();


    }*/
    /**/

    public function getReportTeachert1Data($reportId)
    {
        return Report::where('reports.id', $reportId)
            ->join('teachers as t1', 't1.id', '=', 'reports.reporter_id')
            ->join('teachers as t2', 't2.id', '=', 'reports.reported_at_id')
            ->get([
                'reporter_type',
                'reported_at_type',
                'reporter_id',
                'reported_at_id',
                't1.first_name as first_name_reported',
                't2.first_name as first_name_reporter',
                't1.last_name as last_name_reported',
                't2.last_name as last_name_reporter',
                't1.email as email_reported',
                't2.email as email_reporter',
                't1.phone_number as phone_number_reported',
                't2.phone_number as phone_number_reporter',
                't1.details as details_reported',
                't2.details as details_reporter',
                'message'
            ]);
    }

    public function getReportStudentData($reportId)
    {
        return Report::where('reports.id', $reportId)
            ->join('teachers', 'teachers.id', '=', 'reports.reporter_id')
            ->join('students', 'students.id', '=', 'reports.reported_at_id')
            ->get([
                'reporter_type',
                'reported_at_type',
                'reporter_id',
                'reported_at_id',
                'teachers.first_name as first_name_reporter',
                'students.first_name as first_name_reported',
                'teachers.last_name as last_name_reporter',
                'students.last_name as last_name_reported',
                'teachers.email as email_reporter',
                'students.email as email_reported',
                'teachers.phone_number as phone_number_reporter',
                'students.phone_number as phone_number_reported',
                'message'
            ]);
    }

    public function getReportEducationalsData($reportId)
    {
        return Report::where('reports.id', $reportId)
            ->join('teachers', 'teachers.id', '=', 'reports.reporter_id')
            ->join('educationals', 'educationals.id', '=', 'reports.reported_at_id')
            ->get([
                'reporter_type',
                'reported_at_type',
                'reporter_id',
                'reported_at_id',
                'teachers.first_name as first_name_reporter',
                'educationals.name as name_reported',
                'teachers.last_name as last_name_reporter',
                'teachers.email as email_reporter',
                'educationals.email as educationals_reported',
                'teachers.phone_number as phone_number_reporter',
                'educationals.phone_number as phone_number_reported',
                'educationals.type as type_educationals',
                'message'
            ]);
    }

    public function getReportTeacher2Data($reportId)
    {
        return Report::where('reports.id', $reportId)
            ->join('students', 'students.id', '=', 'reports.reporter_id')
            ->join('teachers', 'teachers.id', '=', 'reports.reported_at_id')
            ->get([
                'reporter_type',
                'reported_at_type',
                'reporter_id',
                'reported_at_id',
                'teachers.first_name as first_name_reported',
                'students.first_name as first_name_reporter',
                'teachers.last_name as last_name_reported',
                'students.last_name as last_name_reporter',
                'teachers.email as email_reported',
                'students.email as email_reporter',
                'teachers.phone_number as phone_number_reported',
                'students.phone_number as phone_number_reporter',
                'message'
            ]);
    }

    public function getReportTeacher3Data($reportId)
    {
        return Report::where('reports.id', $reportId)
            ->join('educationals', 'educationals.id', '=', 'reports.reporter_id')
            ->join('teachers', 'teachers.id', '=', 'reports.reported_at_id')
            ->get([
                'reporter_type',
                'reported_at_type',
                'reporter_id',
                'reported_at_id',
                'teachers.first_name as first_name_reported',
                'teachers.last_name as last_name_reported',
                'educationals.name as name_reporter',
                'teachers.email as email_reported',
                'educationals.email as email_reporter',
                'teachers.phone_number as phone_number_reported',
                'educationals.phone_number as phone_number_reporter',
                'educationals.type as type_reporter',
                'message'
            ]);
    }

    public function reportedTeachert1($request)
    {
        $displayDetailsReport = $this->getReportTeachert1Data($request);
        return $this->admin_response->displayDetailsReportResponse($displayDetailsReport);
    }

    public function reportedStudent($request)
    {
        $displayDetailsReport = $this->getReportStudentData($request);
        return $this->admin_response->displayReportStudentResponse($displayDetailsReport);
    }

    public function reportedEducationals($request)
    {
        $displayDetailsReport = $this->getReportEducationalsData($request);
        return $this->admin_response->displayReportEducationalsResponse($displayDetailsReport);
    }

    public function reportedTeacher2($request)
    {
        $displayDetailsReport = $this->getReportTeacher2Data($request);
        return $this->admin_response->displayreportedTeacherResponse($displayDetailsReport);
    }

    public function reportedTeacher3($request)
    {
        $displayDetailsReport = $this->getReportTeacher3Data($request);
        return $this->admin_response->displayreport_TeacherResponse($displayDetailsReport);
    }

    public function displayReports()
    {
        $displayReports = Report::all();
        $results = [];

        foreach ($displayReports as $report) {
            $reporterType = $report->reporter_type;
            $reportedAtType = $report->reported_at_type;
            $reportId = $report->id;

            if ($reporterType == 'teacher') {
                if ($reportedAtType == 'teacher') {
                    $results[] = $this->getReportTeachert1Data($reportId);
                } elseif ($reportedAtType == 'student') {
                    $results[] = $this->getReportStudentData($reportId);
                } elseif ($reportedAtType == 'educational') {
                    $results[] = $this->getReportEducationalsData($reportId);
                }
            } elseif ($reporterType == 'student') {
                $results[] = $this->getReportTeacher2Data($reportId);
            } elseif ($reporterType == 'educational') {
                $results[] = $this->getReportTeacher3Data($reportId);
            }
        }

        return $this->admin_response->resultReportsResponse1($results);
    }


}
