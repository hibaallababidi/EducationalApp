<?php

namespace App\Http\Services;

use App\Models\Block;
use App\Models\Comment;
use App\Models\CourseEvaluation;
use App\Models\CourseItem;
use App\Models\FavouriteSpecialization;
use App\Models\Like;
use App\Models\Post;
use App\Models\PrivateLesson;
use App\Models\StudentSubscription;
use App\Models\Teacher;
use App\Models\TeacherSpecialization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentServices
{

    private TeacherServices $teacherServices;

    public function __construct()
    {
        $this->teacherServices = new TeacherServices();
    }


    public function confirmLesson($lessonId)
    {
        PrivateLesson::query()
            ->find($lessonId)
            ->update(['is_confirmed' => true]);
    }

    public function saveCourseSubscription($courseId, $studentId)
    {
        StudentSubscription::query()
            ->create([
                'course_id' => $courseId,
                'student_id' => $studentId
            ]);
    }

    public function saveComment($request): Model|Builder
    {
        return Comment::query()
            ->create([
                'student_id' => Auth::id(),
                'item_id' => $request->item_id,
                'comment' => $request->comment
            ]);
    }

    public function saveCourseEvaluation($request)
    {
        CourseEvaluation::query()
            ->create([
                'course_id' => $request->course_id,
                'rate' => $request->rate
            ]);
    }

    public function saveLessonEvaluation($request)
    {
        PrivateLesson::query()
            ->find($request->lesson_id)
            ->update([
                'rate' => $request->rate
            ]);
    }

    public function getMyLessons(): Collection|array
    {
        return PrivateLesson::query()
            ->join('teachers', 'private_lessons.teacher_id', '=', 'teachers.id')
            ->where('student_id', Auth::id())
            ->get($this->lessonDataToGet());
    }

    private function lessonDataToGet(): array
    {
        return [
            'private_lessons.id as lesson_id',
            'private_lessons.student_id as student_id',
            'private_lessons.lesson_date',
            'private_lessons.price',
            'teachers.id as teacher_id',
            'teachers.first_name as teacher_f_name',
            'teachers.last_name as teacher_l_name',
            'private_lessons.rate as lesson_rate',
            'private_lessons.meet_link',
            'private_lessons.is_confirmed',
        ];
    }

    public function saveFavouriteSpecializations($specializations)
    {
        foreach ($specializations as $specialization) {
            FavouriteSpecialization::query()->create([
                'student_id' => Auth::id(),
                'specialization_id' => $specialization
            ]);
        }
    }

    public function getHome()
    {
        $follow = $this->getFollowPosts();
        $followPosts = $follow['posts'];
        $ids = $follow['ids'];
        $specPosts = $this->getFavouritePosts($ids);
        if ($followPosts->isEmpty()) {
            $homeData = $specPosts;
        } else {
            $followPosts = collect($followPosts);
            $specPosts = collect($specPosts);
            $homeData = $followPosts->merge($specPosts);
        }
        $homeData = $this->teacherServices->addTeacherPhoto($homeData);
        $homeData = $this->addIsLiked($homeData);
        $homeData = $this->teacherServices->addIsFollowed($homeData, 'student');
        return $this->teacherServices->addPostLikes($homeData);
    }

    private function getFollowPosts()
    {
        $posts = Post::query()
            ->with('media')
            ->join('follows', 'posts.teacher_id', '=', 'follows.teacher_id')
            ->join('teachers', 'posts.teacher_id', '=', 'teachers.id')
            ->where('follower_type', '=', 'student')
            ->where('follower_id', Auth::id())
            ->orderByDesc('posts.updated_at')
//            ->selectRaw('posts.*, 1 as is_followed')
            ->get($this->teacherServices->postsData());
        $ids = $posts->pluck('id');
        return [
            'posts' => $posts,
            'ids' => $ids
        ];
//        return $this->teacherServices->addIsFollowed($posts, 1);
    }

    private function getFavouritePosts($ids): Collection|array
    {
        $specializations = FavouriteSpecialization::query()
            ->where('student_id', Auth::id())
            ->pluck('specialization_id');
        return Post::query()
            ->with('media')
            ->join('teacher_specializations', 'posts.teacher_id', '=',
                'teacher_specializations.teacher_id')
            ->join('teachers', 'posts.teacher_id', '=', 'teachers.id')
            ->whereIn('teacher_specializations.specialization_id', $specializations)
            ->whereNotIn('posts.id', $ids)
            ->orderByDesc('posts.updated_at')
//            ->selectRaw('posts.*, 0 as is_followed')
            ->get($this->teacherServices->postsData());
//        return $this->teacherServices->addIsFollowed($posts, 0);
    }

    public function addIsLiked($posts)
    {
        foreach ($posts as $post) {
            $like = Like::query()
                ->where('post_id', $post->id)
                ->where('student_id', Auth::id())
                ->exists();
            $post['is_liked'] = $like;
        }
        return $posts;
    }

    /*
    private function getTeachers()
    {
        $specializations = FavouriteSpecialization::query()
            ->where('student_id', Auth::id())
            ->pluck('specialization_id');
        $teachers = Teacher::query()
            ->with('media')
            ->join('teacher_specializations', 'teachers.id', '=',
                'teacher_specializations.teacher_id')
            ->whereIn('teacher_specializations.specialization_id', $specializations)
            ->get($this->teachersData());
        return $this->getTeachersPhotos($teachers);
    }

    private function teachersData(): array
    {
        return [
            'teachers.id',
            'teachers.first_name',
            'teachers.last_name'
        ];
    }

    private function getTeachersPhotos($teachers)
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
    */

    public function getItemComments($item_id): Collection|array
    {
        return Comment::query()
            ->join('students', 'comments.student_id', '=', 'students.id')
            ->where('item_id', $item_id)
            ->orderByDesc('comments.created_at')
            ->get($this->commentsData());
    }

    private function commentsData(): array
    {
        return [
            'comments.id as comment_id',
            'comments.comment',
            'comments.created_at',
            'comments.updated_at',
            'students.id as student_id',
            'students.first_name',
            'students.last_name'
        ];
    }

    public function handleBlocking($request)
    {
        switch ($request->status) {
            case 1:
                $this->block($request);
                break;
            case 0:
                $this->unblock($request);
                break;
        }
    }

    private function block($request)
    {
        Block::query()->create([
            'type' => 'student_reporting',
            'teacher_id' => $request->blocked_id,
            'student_id' => Auth::id()
        ]);
    }

    private function unblock($request)
    {
        Block::query()
            ->where('type', '=', 'student_reporting')
            ->where('teacher_id', $request->blocked_id)
            ->where('student_id', Auth::id())
            ->delete();
    }

    public function addView($request)
    {
        $item = CourseItem::query()
            ->find($request->item_id);
        $item->increment('views');
    }

    public function getTopCourses(): Collection|array
    {
        return CourseEvaluation::query()
            ->join('courses', 'courses.id', '=', 'course_evaluations.course_id')
            ->where('courses.status', 'published')
            ->select($this->topCoursesData())
            ->groupBy($this->topCoursesGroup())
            ->orderByDesc('course_rate')
            ->limit(10)
            ->get();
    }

    private function topCoursesData(): array
    {
        return ['course_evaluations.course_id',
            'courses.course_name',
            'courses.course_description',
            'courses.price',
            'courses.is_free',
            'courses.teacher_id',
            'courses.created_at',
            'courses.updated_at',
            DB::raw('AVG(course_evaluations.rate) as course_rate')
        ];
    }

    private function topCoursesGroup(): array
    {
        return [
            'course_evaluations.course_id',
            'courses.course_name',
            'courses.course_description',
            'courses.price',
            'courses.is_free',
            'courses.teacher_id',
            'courses.created_at',
            'courses.updated_at'
        ];
    }
}
