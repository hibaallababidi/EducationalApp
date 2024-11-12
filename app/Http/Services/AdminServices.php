<?php

namespace App\Http\Services;

use App\Models\Course;
use App\Models\CourseItem;
use App\Models\Educational;
use App\Models\Job;
use App\Models\Student;
use App\Models\StudentSubscription;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Collection;

class AdminServices
{
    public function getEducationalSubmissions(): Collection|array
    {
        $submissions = Educational::query()->where('is_accepted', 0)
//            ->whereNotNull('email_verified_at')
            ->get([
                'id',
                'name',
            ]);
        foreach ($submissions as $submission) {
            $photo = $submission->getMedia('ProfilePicture')->first();
            if ($photo != null) {
                $submission['photo'] = $photo->original_url;
            } else
                $submission['photo'] = null;
        }
        return $submissions;
    }

    public function acceptSubmission($request)
    {
        Educational::query()->find($request->educational_id)
            ->update(['is_accepted' => true]);
    }

    public function getTeachers(): Collection|array
    {
        $teachers = Teacher::query()->get($this->teachersData());
        foreach ($teachers as $teacher) {
            $photo = $teacher->getMedia('ProfilePicture')->first();
            if ($photo != null) {
                $teacher['photo'] = $photo->original_url;
            } else
                $teacher['photo'] = null;
        }
        return $teachers;
    }

    private function teachersData(): array
    {
        return [
            'id',
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'details',
            'is_available',
            'status'
        ];
    }

    public function getStudents(): Collection|array
    {
        $students = Student::query()->get($this->studentsData());
        foreach ($students as $student) {
            $photo = $student->getMedia('ProfilePicture')->first();
            if ($photo != null) {
                $student['photo'] = $photo->original_url;
            } else
                $student['photo'] = null;
            $coursesCount = StudentSubscription::query()
                ->where('student_id', $student->id)
                ->count();
            $student['courses_count'] = $coursesCount;
//            $teacher['photo'] = $teacher->getMedia('ProfilePicture')[0]->original_url;
        }
        return $students;
    }

    private function studentsData(): array
    {
        return [
            'id',
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'status'
        ];
    }

    public function getEducationals(): Collection|array
    {
        $educationals = Educational::query()
            ->where('is_accepted', 1)
            ->get($this->educationalData());
        foreach ($educationals as $educational) {
            $photo = $educational->getMedia('ProfilePicture')->first();
            if ($photo != null) {
                $educational['photo'] = $photo->original_url;
            } else
                $educational['photo'] = null;
        }
        return $educationals;
    }

    private function getJobs($educationals)
    {
        foreach ($educationals as $educational) {
            $jobs = Job::query()
                ->with('media')
                ->where('educational_id', $educational->id)
                ->get();
            $educational['jobs'] = $jobs;
        }
        return $educationals;
    }

    private function educationalData(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone_number',
            'details',
            'is_accepted',
            'type',
            'status'
        ];
    }

    public function getCourses(): Collection|array
    {
        $courses = Course::query()
            ->where('status', '=', 'published')
            ->get($this->coursesData());
        foreach ($courses as $course) {
            $itemsCount = CourseItem::query()
                ->where('course_id', $course->id)
                ->count();
            $subscribers_count = StudentSubscription::query()
                ->where('course_id', $course->id)
                ->count();
            $views = CourseItem::query()
                ->where('course_id', $course->id)
                ->sum('views');
            $course['items_count'] = $subscribers_count;
            $course['subscribers_count'] = $subscribers_count;
            $course['views'] = $views;
        }
        return $courses;
    }

    private function coursesData(): array
    {
        return [
            'id',
            'course_name',
            'course_description',
            'is_free',
            'price',
            'status',
            'teacher_id',
            'created_at',
            'updated_at'
        ];
    }
}
