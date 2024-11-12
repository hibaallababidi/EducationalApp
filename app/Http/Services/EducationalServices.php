<?php

namespace App\Http\Services;

use App\Models\City;
use App\Models\Educational;
use App\Models\Location;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class EducationalServices
{
    private TeacherServices $teacherServices;


    public function __construct()
    {
        $this->teacherServices = new TeacherServices();
    }

    /** @noinspection PhpUndefinedFieldInspection */
    public function getHome()
    {
        $user = Auth::user();
        $posts = Post::query()
            ->with('media')
            ->join('teachers', 'posts.teacher_id', '=', 'teachers.id')
            ->where('teachers.location_id', $user->location_id)
            ->orderByDesc('posts.updated_at')
            ->get($this->teacherServices->postsData());
        $posts = $this->teacherServices->addTeacherPhoto($posts);
        $posts = $this->teacherServices->addIsFollowed($posts, 'educational');
        return $this->teacherServices->addPostLikes($posts);
    }

    public function getMyProfile()
    {
        $profile = Educational::query()->find(Auth::id());
        $profile = $this->addMyPhoto($profile);
        $profile['user_type'] = 'educational';
        return $this->addMyLocation($profile);
    }

    private function addMyPhoto($profile)
    {
        $photo = $profile->getMedia('ProfilePicture')->first();
        if ($photo != null)
            $profile['photo'] = $photo->original_url;
        else
            $profile['photo'] = null;
        return $profile;
    }

    private function addMyLocation($profile)
    {
        if ($profile->location_id != null) {
            $location = Location::query()->find($profile->location_id);
            $profile['location'] = $location->location_name;
            $city = City::query()->find($location->city_id);
            $profile['city'] = $city->city_name;
        }
        return $profile;
    }
}
