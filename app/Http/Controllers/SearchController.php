<?php

namespace App\Http\Controllers;


use App\Http\Requests\Teacher\SearchSpecRequest;
use App\Http\Requests\Teacher\SearchTeacherRequest;
use App\Models\Specialization;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{


    public function searchTeacher(SearchTeacherRequest $request)
    {
        $teacher = Teacher::
        where('teachers.first_name', 'LIKE', '%' . $request->name . '%')
            ->orWhere('teachers.last_name', 'LIKE', '%' . $request->name . '%')
            ->get([
                'teachers.id',
                'teachers.first_name',
                'teachers.last_name',
            ]);
        return response()->json([
            'status' => true,
            'message' => trans('messages.search'),
            'data' => $teacher,
        ]);
    }

    public function searchSpecialization(SearchSpecRequest $request)
    {
        $specialization = Specialization::
        join('teacher_specializations', 'teacher_specializations.specialization_id', '=', 'specializations.id')
            ->join('teachers', 'teachers.id', '=', 'teacher_specializations.teacher_id')
            ->where('teacher_specializations.specialization_id', $request->specialization_id)
            ->get([
                'teachers.id',
                'teachers.first_name',
                'teachers.last_name',
                'specializations.specialization',


            ]);
        return response()->json([
            'status' => true,
            'message' => trans('messages.searchSpecialization'),
            'data' => $specialization,
        ]);
    }
}
