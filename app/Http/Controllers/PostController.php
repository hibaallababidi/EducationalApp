<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teacher\DisplayDetailsPostRequest;
use App\Http\Requests\Teacher\PostRequest;
use App\Http\Requests\Teacher\UpdatePostRequest;
use App\Http\Services\Responses\StudentResponse;
use App\Http\Services\Responses\TeacherResponse;
use App\Http\Services\StudentServices;
use App\Http\Services\TeacherServices;
use App\Models\Location;
use App\Models\Post;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    private TeacherResponse $teacher_response;
    private TeacherServices $services;
    private StudentServices $studentServices;
    private StudentResponse $studentResponse;


    public function __construct()
    {
        $this->teacher_response = new TeacherResponse();
        $this->services = new TeacherServices();
        $this->studentServices = new StudentServices();
        $this->studentResponse = new StudentResponse();
    }

    public function post(PostRequest $request)
    {

        $post = Post::create([
            'teacher_id' => Auth::id(),
            'text' => $request->text,
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $post->addMedia($file)->toMediaCollection('file');
            }
        }

        return $this->teacher_response->PostResponse();
    }

    public function displayMyPost()
    {
        $displayPost = Post::query()->with('media')
            ->where('posts.teacher_id', Auth::id())
            ->get([
                'id',
                'text',
                'created_at',
            ]);
        $displaylinks = Teacher::query()
            ->join('social_links', 'social_links.teacher_id', '=', 'teachers.id')
            ->where('teachers.id', Auth::id())
            ->get([
                'social_links.type',
                'social_links.link',
            ]);
        $location = Location::query()->join('cities', 'cities.id', '=', 'locations.city_id')
            ->join('teachers', 'teachers.location_id', '=', 'locations.id')
            ->where('teachers.id', Auth::id())
            ->get([
                'cities.city_name',
                'locations.location_name',
            ]);
        return response()->json([
            'status' => true,
            'message' => trans('messages.displayMyPost'),
            'data' => [
                'myLocation' => $location,
                'social_links' => $displaylinks,
                'posts' => $displayPost
            ]
        ], 200);

    }

    public function display_details_Post(DisplayDetailsPostRequest $request)
    {

        $display_details_my_Post = Post::query()->with('media')
            ->where('posts.teacher_id', Auth::id())
            ->where('posts.id', $request->post_id)
            ->get([
                'id',
                'teacher_id',
                'created_at',
            ]);
        return response()->json([
            'status' => true,
            'message' => trans('messages.displayPost'),
            'data' => $display_details_my_Post
        ], 400);

    }

    public function delete_post(DisplayDetailsPostRequest $request)
    {
        $delete_post = Post::where('teacher_id', Auth::id())
            ->where('id', $request->post_id)
            ->first();

        if ($delete_post) {
            $delete_post->clearMediaCollection();
            $delete_post->delete();
            return $this->teacher_response->deletePostResponse();
        } else {
            return response()->json(['message' => 'Post not found.'], 404);
        }


    }

    public function update_post(UpdatePostRequest $request)
    {
        $post = Post::where('teacher_id', Auth::id())
            ->where('id', $request->post_id)
            ->first();
        if ($post) {
            if ($request->has('text')) {
                $post->update(['text' => $request->text]);
            }
            return $this->teacher_response->updatePostResponse();
        } else {
            return response()->json(['message' => 'Post not found.'], 404);
        }

    }

    public function displayPostsTeacher(): JsonResponse
    {
        $posts = $this->services->getPosts();
        return $this->teacher_response->displayPostsTeacherResponse($posts);
    }

    public function displayHomePageStudent(): JsonResponse
    {
        $home = $this->studentServices->getHome();
        return $this->studentResponse->displayHomePageResponse($home);
    }
}
