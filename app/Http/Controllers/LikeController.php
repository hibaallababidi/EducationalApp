<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\LikeRequest;
use App\Http\Services\Responses\StudentResponse;
use App\Models\Like;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    private StudentResponse $response1;

    public function __construct()
    {
        $this->response1 = new StudentResponse();
    }

    public function likeAndDislike(LikeRequest $request)
    {
        if ($request->status == 1) {
            Like::create([
                'student_id' => Auth::id(),
                'post_id' => $request->post_id,
            ]);
            $user = Auth::user();
            $teacher = Teacher::query()
                ->join('posts', 'teachers.id', '=', 'posts.teacher_id')
                ->where('posts.id', '=', $request->post_id)
                ->get([
                    'teachers.id as teacher_id'
                ])
                ->first();
            $title = 'Like';
            $body = $user->first_name . ' liked your post';
            Notification::create([
                'type' => 'teacher',
                'user_id' => $teacher->teacher_id,
                'title' => $title,
                'body' => $body,
                'actor_id' => $user->id
            ]);
            return $this->response1->likeResponse();
        } elseif ($request->status == 0) {
            $disLike = Like::query()->where('student_id', Auth::id())->first();
            if ($disLike) {
                $disLike->delete();
                return $this->response1->dislikeResponse();
            }
        }
    }


}
