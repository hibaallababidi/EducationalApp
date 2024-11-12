<?php

namespace App\Http\Controllers;


use App\Http\Requests\Student\StudentFilterRequest;
use App\Http\Services\Responses\StudentResponse;
use App\Models\City;
use App\Models\Course;
use App\Models\CourseEvaluation;
use App\Models\Location;
use App\Models\PrivateLesson;
use App\Models\Specialization;
use App\Models\Teacher;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\Pure;

class StudentFilterController extends Controller
{
    private StudentResponse $response;

    #[Pure] public function __construct()
    {
        $this->response = new StudentResponse();


    }

    public function studentFilter(StudentFilterRequest $request)
    {
        //done1
        if ($request->has('specialization_id') &&
            !$request->has('location_id') &&
            !$request->has('free') &&
            !$request->has('evaluation')) {

            $specializationId = $request->input('specialization_id');

            // Fetch teachers with their specializations
            $teachers = Teacher::join('teacher_specializations', 'teachers.id', '=', 'teacher_specializations.teacher_id')
                ->join('specializations', 'specializations.id', '=', 'teacher_specializations.specialization_id')
                ->where('teacher_specializations.specialization_id', $specializationId)
                ->get(['teachers.id', 'teachers.first_name', 'teachers.last_name', 'specializations.specialization as specialization_name']);

            // Group specializations by teacher
            $groupedTeachers = $teachers->groupBy('id');

            // Iterate over the grouped teachers and add the photo and specializations attributes
            $response = $groupedTeachers->map(function ($teacherGroup) {
                $teacher = $teacherGroup->first();
                $specializations = $teacherGroup->pluck('specialization_name');
                $photo = $teacher->getMedia('ProfilePicture')->first();
                return [
                    'id' => $teacher->id,
                    'first_name' => $teacher->first_name,
                    'last_name' => $teacher->last_name,
                    'specializations' => $specializations,
                    'picture' => $photo ? $photo->original_url : null
                ];
            })->values(); // Use values() to reset the keys of the collection

            return $this->response->getSpecializationResponse($response);
        }


        //done2
        if ($request->has('location_id')
            && !$request->has('specialization_id')
            && !$request->has('free')
            && !$request->has('evaluation')) {

            $locationId = $request->input('location_id');

            // Fetch teachers with their specializations
            $teachers = Teacher::join('locations', 'teachers.location_id', '=', 'locations.id')
                ->join('teacher_specializations', 'teachers.id', '=', 'teacher_specializations.teacher_id')
                ->join('specializations', 'specializations.id', '=', 'teacher_specializations.specialization_id')
                ->where('locations.id', $locationId)
                ->get(['teachers.id', 'teachers.first_name', 'teachers.last_name', 'specializations.specialization as specialization_name']);

            // Group specializations by teacher
            $groupedTeachers = $teachers->groupBy('id');

            // Iterate over the grouped teachers and add the photo and specializations attributes
            $response = $groupedTeachers->map(function ($teacherGroup) {
                $teacher = $teacherGroup->first();
                $specializations = $teacherGroup->pluck('specialization_name')->all();
                $photo = $teacher->getMedia('ProfilePicture')->first();
                return [
                    'id' => $teacher->id,
                    'first_name' => $teacher->first_name,
                    'last_name' => $teacher->last_name,
                    'specializations' => $specializations,
                    'picture' => $photo ? $photo->original_url : null
                ];
            })->values(); // Use values() to reset the keys of the collection

            return $this->response->getLocationResponse($response);
        }

        //done3
        if ($request->has('location_id') && $request->has('specialization_id')
            && !$request->has('free')
            && !$request->has('evaluation')) {

            // Fetch teachers with their specializations and location
            $teachers = Teacher::join('teacher_specializations', 'teachers.id', '=', 'teacher_specializations.teacher_id')
                ->join('specializations', 'specializations.id', '=', 'teacher_specializations.specialization_id')
                ->join('locations', 'locations.id', '=', 'teachers.location_id')
                ->join('cities', 'cities.id', '=', 'locations.city_id')
                ->where('teacher_specializations.specialization_id', $request->specialization_id)
                ->where('locations.id', $request->location_id)
                ->get(['teachers.id', 'teachers.first_name', 'teachers.last_name', 'specializations.specialization as specialization_name']);

            // Group specializations by teacher
            $groupedTeachers = $teachers->groupBy('id');

            // Iterate over the grouped teachers and add the photo and specializations attributes
            $response = $groupedTeachers->map(function ($teacherGroup) {
                $teacher = $teacherGroup->first();
                $specializations = $teacherGroup->pluck('specialization_name')->all();
                $photo = $teacher->getMedia('ProfilePicture')->first();
                return [
                    'id' => $teacher->id,
                    'first_name' => $teacher->first_name,
                    'last_name' => $teacher->last_name,
                    'specializations' => $specializations,
                    'picture' => $photo ? $photo->original_url : null
                ];
            })->values(); // Use values() to reset the keys of the collection

            return $this->response->getLocationSpecializationResponse($response);
        }

        //done4
        if ($request->has('free')
            && !$request->has('specialization_id')
            && !$request->has('location_id')
            && !$request->has('evaluation')) {

            $free = $request->input('free');

            // Fetch teachers and their free courses
            $courses = Teacher::query()
                ->join('courses', 'teachers.id', '=', 'courses.teacher_id')
                ->where('courses.is_free', $free)
                ->where('courses.status', 'published')
                ->get([
                    'courses.id as course_id',
                    'courses.course_name',
                    'courses.course_description',
                    'courses.is_free',
                    'courses.price',
                    'courses.created_at',
                    'courses.updated_at',
                    'teachers.id as teacher_id',
                    'teachers.first_name',
                    'teachers.last_name',
                ]);

            // Iterate over the courses and add the photo attribute if available
            $courses = $courses->map(function ($course) {
                $teacher = Teacher::find($course->teacher_id);
                $photo = $teacher->getMedia('ProfilePicture')->first();
                $picture = $photo ? $photo->original_url : null;

                return [
                    'id' => $course->course_id,
                    'course_name' => $course->course_name,
                    'course_description' => $course->course_description,
                    'is_free' => $course->is_free,
                    'price' => $course->price,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                    'teacher' => [
                        'id' => $course->teacher_id,
                        'first_name' => $course->first_name,
                        'last_name' => $course->last_name,
                        'picture' => $picture,
                    ],
                ];
            });

            return $this->response->getCourseResponse($courses);
        }

        //done5
        if ($request->has('specialization_id') && $request->has('free')
            && !$request->has('location_id')
            && !$request->has('evaluation')) {

            $specializationId = $request->input('specialization_id');
            $isFree = $request->input('free');

            // Fetch courses with their teachers and specializations
            $courses = Teacher::query()
                ->join('courses', 'teachers.id', '=', 'courses.teacher_id')
                ->join('teacher_specializations', 'teachers.id', '=', 'teacher_specializations.teacher_id')
                ->join('specializations', 'specializations.id', '=', 'teacher_specializations.specialization_id')
                ->where('teacher_specializations.specialization_id', $specializationId)
                ->where('courses.status', 'published')
                ->where('courses.is_free', $isFree)
                ->get([
                    'courses.id as course_id',
                    'courses.course_name',
                    'courses.course_description',
                    'courses.is_free',
                    'courses.price',
                    'courses.created_at',
                    'courses.updated_at',
                    'teachers.id as teacher_id',
                    'teachers.first_name',
                    'teachers.last_name'
                ]);

            // Map the courses to the required response structure
            $response = $courses->map(function ($course) {
                $teacher = Teacher::find($course->teacher_id);
                $photo = $teacher->getMedia('ProfilePicture')->first();
                $picture = $photo ? $photo->original_url : null;

                return [
                    'id' => $course->course_id,
                    'course_name' => $course->course_name,
                    'course_description' => $course->course_description,
                    'is_free' => $course->is_free,
                    'price' => $course->price,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                    'teacher' => [
                        'id' => $course->teacher_id,
                        'first_name' => $course->first_name,
                        'last_name' => $course->last_name,
                        'picture' => $picture,
                    ]
                ];
            });

            return $this->response->getCourseSpecializationResponse($response);
        }

        //done6
        if ($request->has('specialization_id') && $request->has('evaluation')
            && !$request->has('free')
            && !$request->has('location_id')
        ) {
            $specializationId = $request->input('specialization_id');

            // Fetch teacher IDs with the given specialization
            $teacherIds = Specialization::join('teacher_specializations', 'specializations.id', '=', 'teacher_specializations.specialization_id')
                ->join('teachers', 'teachers.id', '=', 'teacher_specializations.teacher_id')
                ->where('teacher_specializations.specialization_id', $specializationId)
                ->pluck('teachers.id');

            $teacherEvaluations = collect();

            // Fetch course evaluations for teachers
            $courseEvaluations = CourseEvaluation::query()
                ->join('courses', 'courses.id', '=', 'course_evaluations.course_id')
                ->where('courses.status', 'published')
                ->whereIn('courses.teacher_id', $teacherIds)
                ->get(['courses.teacher_id', 'course_evaluations.rate']);

            // Fetch private lesson evaluations for teachers
            $privateLessonEvaluations = PrivateLesson::whereIn('teacher_id', $teacherIds)
                ->whereNotNull('rate')
                ->get(['teacher_id', 'rate']);

            // Group evaluations by teacher
            $groupedCourseEvaluations = $courseEvaluations->groupBy('teacher_id');
            $groupedPrivateLessonEvaluations = $privateLessonEvaluations->groupBy('teacher_id');

            // Fetch teachers and their specializations manually
            $teachers = Teacher::whereIn('id', $teacherIds)->get();

            // Calculate average ratings and prepare the response
            foreach ($teachers as $teacher) {
                $teacherCourseEvaluations = $groupedCourseEvaluations->get($teacher->id, collect())->pluck('rate');
                $teacherPrivateLessonEvaluations = $groupedPrivateLessonEvaluations->get($teacher->id, collect())->pluck('rate');
                $allEvaluations = $teacherCourseEvaluations->merge($teacherPrivateLessonEvaluations);

                // Manually fetch the specializations for each teacher
                $specializations = Specialization::join('teacher_specializations', 'specializations.id', '=', 'teacher_specializations.specialization_id')
                    ->where('teacher_specializations.teacher_id', $teacher->id)
                    ->pluck('specializations.specialization');

                $photo = $teacher->getMedia('ProfilePicture')->first();
                $profilePicture = $photo ? $photo->original_url : null;

                $teacherEvaluations->push([
                    'id' => $teacher->id,
                    'first_name' => $teacher->first_name,
                    'last_name' => $teacher->last_name,
                    'specializations' => $specializations,
                    'picture' => $profilePicture,
                    'average_rating' => $allEvaluations->avg(),
                    'total_reviews' => $allEvaluations->count(),
                ]);
            }

            // Sort teachers by average rating
            $sortedTeacherEvaluations = $teacherEvaluations->sortByDesc('average_rating')->values();

            // Format the final response
            $response = $sortedTeacherEvaluations->map(function ($teacher) {
                return [
                    'id' => $teacher['id'],
                    'first_name' => $teacher['first_name'],
                    'last_name' => $teacher['last_name'],
                    'specializations' => $teacher['specializations'],
                    'picture' => $teacher['picture'],
                ];
            });

            return $this->response->get_Response($response);
        }


        //done7
        if ($request->has('location_id') && $request->has('evaluation')
            && !$request->has('specialization_id')
            && !$request->has('free')
        ) {
            $locationId = $request->input('location_id');

            // Fetch teacher IDs based on location
            $teacherIds = City::query()
                ->join('locations', 'cities.id', '=', 'locations.city_id')
                ->join('teachers', 'locations.id', '=', 'teachers.location_id')
                ->where('locations.id', $locationId)
                ->pluck('teachers.id');

            $teacherEvaluations = collect();

            // Fetch course evaluations for teachers
            $courseEvaluations = CourseEvaluation::query()
                ->join('courses', 'courses.id', '=', 'course_evaluations.course_id')
                ->where('courses.status', 'published')
                ->whereIn('courses.teacher_id', $teacherIds)
                ->get(['courses.teacher_id', 'course_evaluations.rate']);

            // Fetch private lesson evaluations for teachers
            $privateLessonEvaluations = PrivateLesson::whereIn('teacher_id', $teacherIds)
                ->whereNotNull('rate')
                ->get(['teacher_id', 'rate']);

            // Group evaluations by teacher
            $groupedCourseEvaluations = $courseEvaluations->groupBy('teacher_id');
            $groupedPrivateLessonEvaluations = $privateLessonEvaluations->groupBy('teacher_id');

            // Fetch teachers
            $teachers = Teacher::whereIn('id', $teacherIds)->get();

            // Calculate average ratings and prepare the response
            foreach ($teachers as $teacher) {
                $teacherCourseEvaluations = $groupedCourseEvaluations->get($teacher->id, collect())->pluck('rate');
                $teacherPrivateLessonEvaluations = $groupedPrivateLessonEvaluations->get($teacher->id, collect())->pluck('rate');
                $allEvaluations = $teacherCourseEvaluations->merge($teacherPrivateLessonEvaluations);

                // Manually fetch the specializations for each teacher
                $specializations = Specialization::join('teacher_specializations', 'specializations.id', '=', 'teacher_specializations.specialization_id')
                    ->where('teacher_specializations.teacher_id', $teacher->id)
                    ->pluck('specializations.specialization');

                $photo = $teacher->getMedia('ProfilePicture')->first();
                $profilePicture = $photo ? $photo->original_url : null;

                $teacherEvaluations->push([
                    'id' => $teacher->id,
                    'first_name' => $teacher->first_name,
                    'last_name' => $teacher->last_name,
                    'specializations' => $specializations,
                    'picture' => $profilePicture,
                ]);
            }

            // Sort teachers by average rating
            $sortedTeacherEvaluations = $teacherEvaluations->sortByDesc('average_rating')->values();

            // Format the final response
            $response = $sortedTeacherEvaluations->map(function ($teacher) {
                return [
                    'id' => $teacher['id'],
                    'first_name' => $teacher['first_name'],
                    'last_name' => $teacher['last_name'],
                    'specializations' => $teacher['specializations'],
                    'picture' => $teacher['picture'],
                ];
            });

            return $this->response->getResponse($response);
        }


        //done8
        if ($request->has('location_id') && $request->has('evaluation') && $request->has('specialization_id')
            && !$request->has('free')) {

            // Fetch teacher IDs with the given specialization and location
            $teacherIds = Specialization::join('teacher_specializations', 'specializations.id', '=', 'teacher_specializations.specialization_id')
                ->join('teachers', 'teachers.id', '=', 'teacher_specializations.teacher_id')
                ->join('locations', 'locations.id', '=', 'teachers.location_id')
                ->join('cities', 'cities.id', '=', 'locations.city_id')
                ->where('teacher_specializations.specialization_id', $request->specialization_id)
                ->where('locations.id', $request->location_id)
                ->pluck('teachers.id');

            $teacherEvaluations = collect();

            // Fetch course evaluations for teachers
            $courseEvaluations = CourseEvaluation::query()
                ->join('courses', 'courses.id', '=', 'course_evaluations.course_id')
                ->where('courses.status', 'published')
                ->whereIn('courses.teacher_id', $teacherIds)
                ->get(['courses.teacher_id', 'course_evaluations.rate']);

            // Fetch private lesson evaluations for teachers
            $privateLessonEvaluations = PrivateLesson::whereIn('teacher_id', $teacherIds)
                ->whereNotNull('rate')
                ->get(['teacher_id', 'rate']);

            // Group evaluations by teacher
            $groupedCourseEvaluations = $courseEvaluations->groupBy('teacher_id');
            $groupedPrivateLessonEvaluations = $privateLessonEvaluations->groupBy('teacher_id');

            // Fetch teachers
            $teachers = Teacher::whereIn('id', $teacherIds)->get();

            foreach ($teachers as $teacher) {
                $teacherCourseEvaluations = $groupedCourseEvaluations->get($teacher->id, collect())->pluck('rate');
                $teacherPrivateLessonEvaluations = $groupedPrivateLessonEvaluations->get($teacher->id, collect())->pluck('rate');
                $allEvaluations = $teacherCourseEvaluations->merge($teacherPrivateLessonEvaluations);

                // Manually fetch the specializations for each teacher
                $specializations = Specialization::join('teacher_specializations', 'specializations.id', '=', 'teacher_specializations.specialization_id')
                    ->where('teacher_specializations.teacher_id', $teacher->id)
                    ->pluck('specializations.specialization');

                $photo = $teacher->getMedia('ProfilePicture')->first();
                $profilePicture = $photo ? $photo->original_url : null;

                $teacherEvaluations->push([
                    'id' => $teacher->id,
                    'first_name' => $teacher->first_name,
                    'last_name' => $teacher->last_name,
                    'specializations' => $specializations,
                    'picture' => $profilePicture,
                ]);
            }

            // Sort teachers by average rating
            $sortedTeacherEvaluations = $teacherEvaluations->sortByDesc('average_rating')->values();

            // Format the final response
            $response = $sortedTeacherEvaluations->map(function ($teacher) {
                return [
                    'id' => $teacher['id'],
                    'first_name' => $teacher['first_name'],
                    'last_name' => $teacher['last_name'],
                    'specializations' => $teacher['specializations'],
                    'picture' => $teacher['picture'],
                ];
            });

            return $this->response->get__Response($response);
        }

        //done9
        if ($request->has('free') && $request->has('evaluation')
            && !$request->has('location_id')
            && !$request->has('specialization_id')
        ) {
            $free = $request->input('free');

            // Fetch teacher IDs who have free courses
            $teacherIds = Teacher::query()
                ->join('courses', 'teachers.id', '=', 'courses.teacher_id')
                ->where('courses.is_free', $free)
                ->where('courses.status', 'published')
                ->pluck('teachers.id');

            $teacherEvaluations = collect();

            // Fetch course evaluations for teachers
            $courseEvaluations = CourseEvaluation::query()
                ->join('courses', 'courses.id', '=', 'course_evaluations.course_id')
                ->where('courses.status', 'published')
                ->whereIn('courses.teacher_id', $teacherIds)
                ->get(['courses.teacher_id', 'course_evaluations.rate']);

            // Fetch private lesson evaluations for teachers
            $privateLessonEvaluations = PrivateLesson::whereIn('teacher_id', $teacherIds)
                ->whereNotNull('rate')
                ->get(['teacher_id', 'rate']);

            // Group evaluations by teacher
            $groupedCourseEvaluations = $courseEvaluations->groupBy('teacher_id');
            $groupedPrivateLessonEvaluations = $privateLessonEvaluations->groupBy('teacher_id');

            // Fetch courses and their teachers
            $courses = Course::query()
                ->join('teachers', 'courses.teacher_id', '=', 'teachers.id')
                ->where('courses.is_free', $free)
                ->where('courses.status', 'published')
                ->get([
                    'courses.id as course_id',
                    'courses.course_name',
                    'courses.course_description',
                    'courses.is_free',
                    'courses.price',
                    'courses.created_at',
                    'courses.updated_at',
                    'teachers.id as teacher_id',
                    'teachers.first_name',
                    'teachers.last_name'
                ]);

            $response = $courses->map(function ($course) use ($groupedCourseEvaluations, $groupedPrivateLessonEvaluations) {
                $teacher = Teacher::find($course->teacher_id);
                $photo = $teacher->getMedia('ProfilePicture')->first();
                $picture = $photo ? $photo->original_url : null;

                return [
                    'id' => $course->course_id,
                    'course_name' => $course->course_name,
                    'course_description' => $course->course_description,
                    'is_free' => $course->is_free,
                    'price' => $course->price,
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                    'teacher' => [
                        'id' => $course->teacher_id,
                        'first_name' => $course->first_name,
                        'last_name' => $course->last_name,
                        'picture' => $picture,
                    ]
                ];
            });

            return $this->response->getCourse($response);
        }


    }

}



