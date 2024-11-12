<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\UpdateBlockEducationalRequest;
use App\Http\Requests\Admin\UpdateBlockStudentRequest;
use App\Http\Requests\Admin\UpdateBlockTeacherRequest;
use App\Http\Requests\TeacherStudentBlockingRequest;
use App\Http\Services\Responses\AdminResponse;
use App\Http\Services\Responses\StudentResponse;
use App\Http\Services\StudentServices;
use App\Models\Block;
use App\Models\Educational;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    private AdminResponse $admin_response;
    private StudentServices $studentServices;
    private StudentResponse $studentResponse;

    public function __construct()
    {
        $this->admin_response = new AdminResponse();
        $this->studentServices = new StudentServices();
        $this->studentResponse = new StudentResponse();

    }


    //Black List (Admin)
    public function blockTeacher(UpdateBlockTeacherRequest $request)
    {
        $blockTeacher = Teacher::where('id', $request->teacher_id)->first();
//        if ($blockTeacher) {
        $blockTeacher->update(['status' => 0]);
        return $this->admin_response->blockTeacher();
//        }
    }

    public function unBlockTeacher(UpdateBlockTeacherRequest $request)
    {
        $blockTeacher = Teacher::where('id', $request->teacher_id)->first();
//        if ($blockTeacher) {
        $blockTeacher->update(['status' => 1]);
        return $this->admin_response->unblockTeacher();
//        }
    }

    /*Student*/
    public function blockStudent(UpdateBlockStudentRequest $request)
    {
        $blockStudent = Student::
        where('id', $request->student_id)
            ->first();
//        if ($blockStudent) {
        $blockStudent->update(['status' => 0]);
        return $this->admin_response->blockStudent();
    }

//    }
    public function unblockStudent(UpdateBlockStudentRequest $request)
    {
        $blockStudent = Student::
        where('id', $request->student_id)
            ->first();
//        if ($blockStudent) {
        $blockStudent->update(['status' => 1]);
        return $this->admin_response->unblockStudent();
//        }
    }

    /*Educational*/

    public function blockEducational(UpdateBlockEducationalRequest $request)
    {
        $blockEducational = Educational::
        where('id', $request->educational_id)
            ->first();
//        if ($blockEducational) {
        $blockEducational->update(['status' => 0]);
        return $this->admin_response->blockEducational();
//        }
    }

    public function unblockEducational(UpdateBlockEducationalRequest $request)
    {
        $blockEducational = Educational::
        where('id', $request->educational_id)
            ->first();
//        if ($blockEducational) {
        $blockEducational->update(['status' => 1]);
        return $this->admin_response->unblockEducational();
//        }
    }


    public function studentBlockTeacher(TeacherStudentBlockingRequest $request): JsonResponse
    {
        $this->studentServices->handleBlocking($request);
        return $this->studentResponse->studentBlockTeacherResponse();
    }

}
