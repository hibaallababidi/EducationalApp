<?php

namespace App\Http\Services;

use App\Models\Course;
use App\Models\CourseEvaluation;
use App\Models\CourseItem;
use App\Models\Follow;
use App\Models\Like;
use App\Models\Post;
use App\Models\PrivateLesson;
use App\Models\SocialLink;
use App\Models\StudentSubscription;
use App\Models\Teacher;
use App\Models\TeacherSpecialization;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeacherServices
{

    public function getMySpecializations(): Collection|array
    {
        return TeacherSpecialization::query()
            ->join('specializations as s',
                'teacher_specializations.specialization_id', '=', 's.id')
            ->where('teacher_specializations.teacher_id', Auth::id())
            ->get([
                's.id as specialization_id',
                's.specialization'
            ]);
    }

    public function savePrivateLesson($request)
    {
        $privateLesson = PrivateLesson::query()->create([
            'student_id' => $request->student_id,
            'teacher_id' => Auth::id(),
            'lesson_date' => $request->lesson_date,
            'price' => $request->price,
        ]);
        if ($request->has('meet_link'))
            $privateLesson->update([
                'meet_link' => $request->meet_link
            ]);
        return $privateLesson;
    }

    public function updatePrivateLesson($lesson, $request)
    {
        if ($request->has('lesson_date'))
            $lesson->update(['lesson_date' => $request->lesson_date,]);
        if ($request->has('price'))
            $lesson->update(['price' => $request->price,]);
        if ($request->has('meet_link'))
            $lesson->update(['meet_link' => $request->meet_link,]);
    }

    public function deleteLesson($request)
    {
        PrivateLesson::query()
            ->find($request->lesson_id)
            ->delete();
    }

    public function getMyLessons(): Collection|array
    {
        return PrivateLesson::query()
            ->join('students', 'private_lessons.student_id', '=', 'students.id')
            ->where('teacher_id', Auth::id())
            ->get($this->lessonDataToGet());
    }

    public function getLessonDetails($lesson_id): Collection|array
    {
        return PrivateLesson::query()
            ->join('students', 'private_lessons.student_id', '=', 'students.id')
            ->where('private_lessons.id', $lesson_id)
            ->get($this->lessonDataToGet());
    }

    private function lessonDataToGet(): array
    {
        return [
            'private_lessons.id as lesson_id',
            'private_lessons.lesson_date',
            'private_lessons.price',
            'students.id as student_id',
            'students.first_name as student_f_name',
            'students.last_name as student_l_name',
            'private_lessons.rate as lesson_rate',
            'private_lessons.meet_link',
            'private_lessons.is_confirmed'
        ];
    }

    public function saveCourse($request): Model|Builder
    {
        $course = Course::query()->create([
            'course_name' => $request->course_name,
            'course_description' => $request->course_description,
            'is_free' => $request->is_free,
            'teacher_id' => Auth::id()
        ]);
        if ($request->has('price'))
            $course->update(['price' => $request->price]);
        return $course;
    }

    public function saveCourseItem($request)
    {
        $orderItem = CourseItem::query()->where('course_id', $request->course_id)
            ->orderByDesc('id')->first();
        $item = CourseItem::query()->create([
            'item_name' => $request->item_name,
            'item_description' => $request->item_description,
            'course_id' => $request->course_id,
        ]);
        if (isset($orderItem)) {
            $order = ($orderItem->item_order) + 1;
            $item->update(['item_order' => $order]);
        } else
            $item->update(['item_order' => 1]);
        $item->addMedia($request->file)->toMediaCollection('Courses');

    }

    public function submitCourse($request)
    {
        $course = Course::query()->find($request->course_id);
        $course->update(['status' => 'waiting']);
    }

    public function getCourseItems($request): Collection|array
    {
        return CourseItem::query()->with('media')
            ->where('course_id', $request->course_id)
            ->orderBy('item_order')
            ->get($this->courseItemsData());
    }

    public function getCourseTeacher($request)
    {
        return Course::query()
            ->join('teachers', 'courses.teacher_id', '=', 'teachers.id')
            ->where('courses.id', $request->course_id)
            ->get([
                'teachers.id as teacher_id',
                'teachers.first_name',
                'teachers.last_name',
            ])->first();
    }

    private function courseItemsData(): array
    {
        return [
            'id',
            'item_name',
            'item_description',
            'views',
            'item_order',
            'created_at',
            'updated_at'
        ];
    }

    public function getIsSubscribed($request): bool
    {
        return StudentSubscription::query()
            ->where('student_id', Auth::id())
            ->where('course_id', $request->course_id)
            ->exists();
    }

    public function getCourseEvaluation($request)
    {
        return CourseEvaluation::query()
            ->select('course_id', DB::raw('AVG(rate) as course_rate'))
            ->groupBy('course_id')
            ->having('course_id', $request->course_id)
            ->get()
            ->first();
    }

    public function getSubscribersCount($request)
    {
        return StudentSubscription::query()
            ->where('course_id', $request->course_id)
            ->count();
    }

    public function getPosts()//: Collection|array
    {
        $follow = $this->getFollowPosts();
        $followPosts = $follow['posts'];
        $ids = $follow['ids'];
        $specPosts = $this->getSpecializationPosts($ids);

        if ($followPosts->isEmpty()) {
            $posts = $specPosts;
        } else {
            $followPosts = collect($followPosts);
            $specPosts = collect($specPosts);
            $posts = $followPosts->merge($specPosts);
        }
        $posts = $this->addTeacherPhoto($posts);
        $posts = $this->addIsFollowed($posts, 'teacher');
        return $this->addPostLikes($posts);
    }

    private function getFollowPosts()
    {
        $posts = Post::query()
            ->with('media')
            ->join('follows', 'posts.teacher_id', '=', 'follows.teacher_id')
            ->join('teachers', 'posts.teacher_id', '=', 'teachers.id')
            ->where('follower_type', '=', 'teacher')
            ->where('follower_id', Auth::id())
            ->orderByDesc('posts.updated_at')
//            ->selectRaw('posts.*, 1 as is_followed')
            ->get($this->postsData());
        $ids = $posts->pluck('id');
        return [
            'posts' => $posts,
            'ids' => $ids
        ];
    }

    private function getSpecializationPosts($ids): Collection|array
    {
        $specializations = TeacherSpecialization::query()
            ->where('teacher_id', Auth::id())
            ->pluck('specialization_id');
        return Post::query()
            ->with('media')
            ->join('teacher_specializations', 'posts.teacher_id', '=',
                'teacher_specializations.teacher_id')
            ->join('teachers', 'posts.teacher_id', '=', 'teachers.id')
//            ->whereNot('posts.teacher_id', Auth::id())
            ->whereIn('teacher_specializations.specialization_id', $specializations)
            ->whereNotIn('posts.id', $ids)
            ->orderByDesc('posts.updated_at')
//            ->selectRaw('posts.*, 0 as is_followed')
            ->get($this->postsData());

    }

    public function postsData(): array
    {
        return [
            'posts.id',
            'text',
            'posts.created_at',
            'teachers.id as teacher_id',
            'teachers.first_name',
            'teachers.last_name',
//            'posts.is_followed'
        ];
    }

    public function addTeacherPhoto($posts)
    {
        $teacherIds = $posts->pluck('teacher_id')->unique();

        $teachers = Teacher::query()->whereIn('id', $teacherIds)
            ->with('media')
            ->get()
            ->keyBy('id');

        foreach ($posts as $post) {
            $teacher = $teachers->get($post->teacher_id);
            $photo = $teacher->getMedia('ProfilePicture')->first();
            $post['t_photo'] = $photo?->original_url ?? null;
        }

        return $posts;
    }


    public function addPostLikes($posts)
    {
        $postIds = $posts->pluck('id');

        $likes = Like::query()->whereIn('post_id', $postIds)
            ->select('post_id', DB::raw('count(*) as count'))
            ->groupBy('post_id')
            ->get()
            ->keyBy('post_id');

        foreach ($posts as $post) {
            $post['likes_count'] = $likes->get($post->id)->count ?? 0;
        }

        return $posts;
    }

    public function addIsFollowed($posts, $follower_type)
    {
        foreach ($posts as $post) {
            $isFollowed = Follow::query()
                ->where('follower_type', '=', $follower_type)
                ->where('follower_id', Auth::id())
                ->where('teacher_id', $post->teacher_id)
                ->exists();
            $post['is_followed'] = $isFollowed;
        }
        return $posts;
    }
}
