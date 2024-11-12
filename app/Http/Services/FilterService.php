<?php

namespace App\Http\Services;

use App\Models\CourseEvaluation;
use App\Models\PrivateLesson;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Collection;

class FilterService
{
    public function filter($request)
    {
        if ($request->has('specialization_id') && !$request->has('location_id') && !$request->has('evaluation')) {
            return $this->filterWithSpec($request);
        }

        if (!$request->has('specialization_id') && $request->has('location_id') && !$request->has('evaluation')) {
            return $this->filterWithLoc($request);
        }

        if (!$request->has('specialization_id') && !$request->has('location_id') && $request->has('evaluation')) {
            return $this->filterWithEval($request);
        }

        if ($request->has('specialization_id') && $request->has('location_id') && !$request->has('evaluation')) {
            return $this->filterWithSpecAndLoc($request);
        }

        if ($request->has('specialization_id') && !$request->has('location_id') && $request->has('evaluation')) {
            return $this->filterWithSpecAndEval($request);
        }

        if (!$request->has('specialization_id') && $request->has('location_id') && $request->has('evaluation')) {
            return $this->filterWithLocAndEval($request);
        }

        if ($request->has('specialization_id') && $request->has('location_id') && $request->has('evaluation')) {
            return $this->filterWithSpecAndLocAndEval($request);
        } else
            return [];
    }

    private function filterWithSpec($request)
    {
        $result = Teacher::query()
            ->join('teacher_specializations as ts', 'teachers.id', '=', 'ts.teacher_id')
            ->where('ts.specialization_id', $request->specialization_id)
            ->get($this->teachersData());
        return $this->addPhoto($result);
    }

    private function filterWithLoc($request)
    {
        $result = Teacher::query()
            ->join('locations as l', 'teachers.location_id', '=', 'l.id')
            ->where('l.id', $request->location_id)
            ->get($this->teachersData());
        return $this->addPhoto($result);
    }

    private function filterWithEval($request)
    {
        $teacherIds = Teacher::query()->pluck('id');
        return $this->calculateEvaluation($teacherIds);
    }

    private function filterWithSpecAndLoc($request)
    {
        $result = Teacher::query()
            ->join('teacher_specializations as ts', 'teachers.id', '=', 'ts.teacher_id')
            ->join('locations as l', 'teachers.location_id', '=', 'l.id')
            ->where('ts.specialization_id', $request->specialization_id)
            ->where('l.id', $request->location_id)
            ->get($this->teachersData());
        return $this->addPhoto($result);
    }

    private function filterWithSpecAndEval($request)
    {
        $teacherIds = Teacher::query()
            ->join('teacher_specializations as ts', 'teachers.id', '=', 'ts.teacher_id')
            ->where('ts.specialization_id', $request->specialization_id)
            ->pluck('teachers.id');
//            ->get($this->teachersData());
//        return $this->addPhoto($result);
        return $this->calculateEvaluation($teacherIds);
    }

    private function filterWithLocAndEval($request)
    {
        $teacherIds = Teacher::query()
            ->join('locations as l', 'teachers.location_id', '=', 'l.id')
            ->where('l.id', $request->location_id)
//            ->get($this->teachersData());
            ->pluck('teachers.id');
//        return $this->addPhoto($result);
        return $this->calculateEvaluation($teacherIds);
    }

    private function filterWithSpecAndLocAndEval($request)
    {
        $teacherIds = Teacher::query()
            ->join('teacher_specializations as ts', 'teachers.id', '=', 'ts.teacher_id')
            ->join('locations as l', 'teachers.location_id', '=', 'l.id')
            ->where('ts.specialization_id', $request->specialization_id)
            ->where('l.id', $request->location_id)
//            ->get($this->teachersData());
            ->pluck('teachers.id');
//        return $this->addPhoto($result);
        return $this->calculateEvaluation($teacherIds);
    }

    private function teachersData(): array
    {
        return [
            'teachers.id',
            'first_name',
            'last_name',
        ];
    }

    private function addPhoto($teachers)
    {
        foreach ($teachers as $teacher) {
            $photo = $teacher->getMedia('ProfilePicture')->first();
            if ($photo != null) {
                $teacher['photo'] = $photo->original_url;
            } else
                $teacher['photo'] = null;
        }
        return $teachers;
    }

    private function calculateEvaluation($teacherIds)
    {
        $teacherEvaluations = collect();

        // Fetch all course evaluations for these teachers
        $courseEvaluations = $this->calculateCourseEvaluation($teacherIds);

        // Fetch all private lesson evaluations for these teachers //With Grouping
        $privateLessonEvaluations = $this->calcLessonsEvaluation($teacherIds);

        // Fetch teacher information
        return $this->getEvaluationTeachers($teacherEvaluations, $teacherIds, $courseEvaluations, $privateLessonEvaluations);
    }

    private function calculateCourseEvaluation($teacherIds): Collection|array
    {
        $courseEvaluations = CourseEvaluation::query()
            ->join('courses', 'courses.id', '=', 'course_evaluations.course_id')
            ->where('courses.status', 'published')
            ->whereIn('courses.teacher_id', $teacherIds)
            ->get(['courses.teacher_id', 'course_evaluations.rate']);
        // Group evaluations by teacher
        return $courseEvaluations->groupBy('teacher_id');
    }

    private function calcLessonsEvaluation($teacherIds): Collection|array
    {
        $privateLessonEvaluations = PrivateLesson::query()
            ->whereIn('teacher_id', $teacherIds)
            ->whereNotNull('rate')
            ->get(['teacher_id', 'rate']);
        // Group evaluations by teacher
        return $privateLessonEvaluations->groupBy('teacher_id');
    }

    private function getEvaluationTeachers($tEvaluations, $tIds, $cEvaluations, $pLEvaluations)
    {
        $teachers = Teacher::query()
            ->whereIn('id', $tIds)->get();
        foreach ($teachers as $teacher) {
            // Merge evaluations for this teacher
            $allEvaluations = $this->mergeTeacherEvaluations($teacher, $cEvaluations, $pLEvaluations);
            $tEvaluations = $this->pushData($teacher, $allEvaluations, $tEvaluations);
        }

        // Sort by average_rating from highest to lowest
        return $tEvaluations->sortByDesc('average_rating')->values();
    }

    private function mergeTeacherEvaluations($teacher, $cEvaluations, $pLEvaluations)
    {
        $teacherCourseEvaluations = $cEvaluations->get($teacher->id, collect())->pluck('rate');
        $teacherPrivateLessonEvaluations = $pLEvaluations->get($teacher->id, collect())->pluck('rate');
        return $teacherCourseEvaluations->merge($teacherPrivateLessonEvaluations);
    }

    private function pushData($teacher, $allEvaluations, $tEvaluations)
    {
        if ($allEvaluations->count() > 0) {
            $averageRating = $allEvaluations->avg();
            $photo = $teacher->getMedia('ProfilePicture')->first();
            $tEvaluations->push([
                'teacher_id' => $teacher->id,
                'first_name' => $teacher->first_name,
                'last_name' => $teacher->last_name,
                'photo' => $photo ? $photo->original_url : null,
                'average_rating' => $averageRating,
                'total_reviews' => $allEvaluations->count(),
            ]);
        }
        return $tEvaluations;
    }

}
