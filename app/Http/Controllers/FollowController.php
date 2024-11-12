<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teacher\DeleteFollowRequest;
use App\Http\Requests\Teacher\FollowRequest;
use App\Http\Services\Responses\TeacherResponse;
use App\Models\Follow;
use App\Models\Notification;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FollowController extends Controller
{


    private TeacherResponse $teacher_response;


    public function __construct()
    {
        $this->teacher_response = new TeacherResponse();

    }

    public function followAndUnfollow(FollowRequest $request)
    {
        if ($request->status == 1) {
            Follow::create([
                'follower_type' => $request->follower_type,
                'follower_id' => Auth::id(),
                'teacher_id' => $request->teacher_id,
            ]);
            $user = Auth::user();
            $title = 'Follow';
            $body = $user->first_name . ' followed you';
            Notification::create([
                'type' => 'teacher',
                'user_id' => $request->teacher_id,
                'title' => $title,
                'body' => $body,
                'actor_id' => $user->id
            ]);
            return $this->teacher_response->followResponse();
        } else {
            $delete_follow = Follow::where('teacher_id', $request->teacher_id)
                ->where('follower_id', Auth::id())
                ->where('follower_type', $request->follower_type)
                ->first();

            if ($delete_follow) {
                $delete_follow->delete();
                return $this->teacher_response->deleteFollowResponse();
            } else {
                return response()->json(['error' => 'Follow record not found.'], 404);
            }
        }
    }

//    public function follow(FollowRequest $request)
//    {
//
//        if ($request->follower_type == 'teacher') {
//            $followerId = Auth::id();
//            $teacherId = $request->teacher_id;
//            if ($followerId == $teacherId) {
//                return $this->teacher_response->follow_Response();
//
//            }
//        }
//        $follow = Follow::create([
//            'follower_type' => $request->follower_type,
//            'follower_id' => Auth::id(),
//            'teacher_id' => $request->teacher_id,
//        ]);
//        return $this->teacher_response->followResponse();
//
//
//    }
//
//
//    public function un_follow(DeleteFollowRequest $request)
//    {
//        $delete_follow = DB::table('follows')
//            ->where('teacher_id', $request->teacher_id)->first();
//
//        if ($delete_follow) {
//            Follow::where('teacher_id', $request->teacher_id)
//                ->first()
//                ->delete();
//            return $this->teacher_response->deleteFollowResponse();
//
//        } else {
//            return $this->teacher_response->delete_FollowResponse();
//        }
//    }
}
