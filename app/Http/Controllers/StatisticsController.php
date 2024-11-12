<?php /** @noinspection PhpMissingReturnTypeInspection */

namespace App\Http\Controllers;

use App\Http\Services\AdminServices;
use App\Http\Services\Responses\AdminResponse;
use App\Models\Course;
use App\Models\Educational;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
//    private AdminServices $services;
    private AdminResponse $admin_response;


    public function __construct()
    {
//        $this->services = new AdminServices();
        $this->admin_response = new AdminResponse();
    }

    public function topCoursesHaveSubscriptions()
    {
        // Step 1: Fetch top courses with the highest number of subscriptions
        $topCoursesHaveSubscriptions = Course::query()
            ->join('student_subscriptions', 'courses.id', '=', 'student_subscriptions.course_id')
            ->join('teachers', 'courses.teacher_id', '=', 'teachers.id')
            ->select(
                'courses.id as course_id',
                'courses.course_name',
                'courses.course_description',
                'courses.is_free',
                'courses.price',
                'teachers.id as teacher_id',
                'teachers.first_name as teacher_first_name',
                'teachers.last_name as teacher_last_name',
                DB::raw('count(student_subscriptions.id) as number_of_subscriptions')
            )
            ->groupBy(
                'courses.id',
                'courses.course_name',
                'courses.course_description',
                'courses.is_free',
                'courses.price',
                'teachers.id',
                'teachers.first_name',
                'teachers.last_name'
            )
            ->orderByDesc('number_of_subscriptions')
            ->limit(10)
            ->get();

        // Step 2: Format the response data
        $response = $topCoursesHaveSubscriptions->map(function ($course) {
            return [
                'course_id' => $course->course_id,
                'course_name' => $course->course_name,
                'course_description' => $course->course_description ?: 'null', // Convert null to 'null' string if necessary
                'is_free' => (int)$course->is_free, // Convert boolean to integer
                'price' => $course->price,
                'teacher' => [
                    'id' => $course->teacher_id,
                    'first_name' => $course->teacher_first_name,
                    'last_name' => $course->teacher_last_name,
                ],
                'number_of_subscriptions' => $course->number_of_subscriptions
            ];
        });

        // Step 3: Return the formatted response
        return $this->admin_response->displayTopCoursesHaveSubscriptionsResponse($response);
    }

    public function numberofTeacherbySpecialty()
    {
        // Step 1: Get the number of teachers per specialization
        $numberofTeacherbySpecialty = Teacher::query()
            ->join('teacher_specializations', 'teacher_specializations.teacher_id', '=', 'teachers.id')
            ->join('specializations', 'specializations.id', '=', 'teacher_specializations.specialization_id')
            ->select('specializations.id as specializations_id', 'specializations.specialization', DB::raw('count(teachers.id) as number_of_teachers'))
            ->groupBy('specializations.id', 'specializations.specialization')
            ->get();

        // Step 2: Calculate the total number of teachers
        $totalTeachers = $numberofTeacherbySpecialty->sum('number_of_teachers');

        // Step 3: Calculate the percentage for each specialization
        $numberofTeacherbySpecialtyWithPercentage = $numberofTeacherbySpecialty->map(function ($specialization) use ($totalTeachers) {
            $specialization->percentage = $totalTeachers > 0 ? round(($specialization->number_of_teachers / $totalTeachers) * 100, 2) . '%' : '0%';
            return $specialization;
        });

        // Step 4: Sort by percentage in descending order and take the top 5
        $topSpecializations = $numberofTeacherbySpecialtyWithPercentage
            ->sortByDesc(function ($specialization) {
                // Extract numeric percentage value for sorting
                return (float)rtrim($specialization->percentage, '%');
            })
            ->take(5)
            ->values(); // Ensure values are re-indexed as a simple array

        // Construct the response
        $response = $topSpecializations->map(function ($specialization) {
            return [
                'specializations_id' => $specialization->specializations_id,
                'specialization' => $specialization->specialization,
                'percentage' => $specialization->percentage
            ];
        });

        return response()->json([
            'status' => true,
            'message' => trans('messages.number_of_teacher_by_specialty'),
            'data' => $response
        ], 201);
    }

    public function jobsPerEducational(): JsonResponse
    {
        $statistic = Educational::query()
            ->join('jobs', 'educationals.id', '=', 'jobs.educational_id')
            ->select('jobs.educational_id', 'educationals.name', DB::raw('count(jobs.id) as job_count'))
            ->groupBy('jobs.educational_id', 'educationals.name')
            ->get();

        return $this->admin_response->jobsPerEducationalResponse($statistic);
    }

    public function privateLessonsStatistics()
    {
        $statistics = DB::table('private_lessons')
            ->join('teachers', 'private_lessons.teacher_id', '=', 'teachers.id')
            ->select(
                DB::raw('YEAR(private_lessons.lesson_date) AS year'),
                DB::raw('MONTH(private_lessons.lesson_date) AS month'),
                DB::raw('COUNT(private_lessons.id) AS number_of_lessons')
            )
            ->groupBy(
                DB::raw('CONCAT(teachers.first_name, " ", teachers.last_name)'),
                DB::raw('YEAR(private_lessons.lesson_date)'),
                DB::raw('MONTH(private_lessons.lesson_date)')
            )
            ->orderBy(DB::raw('YEAR(private_lessons.lesson_date)'))
            ->orderBy(DB::raw('MONTH(private_lessons.lesson_date)'))
            ->get();

        return $this->admin_response->privateLessonsStatisticsResponse($statistics);
    }

    public function subscriptionStatistics()
    {
        $currentYear = Carbon::now()->year;

        $totalSubscriptions = Educational::query()
            ->whereYear('created_at', $currentYear)->count();

        $subscriptions = Educational::query()
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as subscriptions')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $subscriptions->map(function ($subscription) use ($totalSubscriptions) {
            $subscription->percentage = ($subscription->subscriptions / $totalSubscriptions) * 100;
            return $subscription;
        });

        return $this->admin_response->subscriptionStatisticsResponse($subscriptions);
    }


}
