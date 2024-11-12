<?php

namespace App\Http\Services\Teacher;

use App\Http\Services\EmailVerification;
use App\Models\City;
use App\Models\Course;
use App\Models\Location;
use App\Models\PdfDocument;
use App\Models\Post;
use App\Models\SocialLink;
use App\Models\Teacher;
use App\Models\TeacherSpecialization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\PdfToText\Pdf;

class ProfileService
{
    private EmailVerification $emailVerification;

    public function __construct()
    {
        $this->emailVerification = new EmailVerification();
    }

    public function searchCVs($keywords)
    {
        $keywordsArray = explode(' ', $keywords);
        $query = PdfDocument::query();

        foreach ($keywordsArray as $keyword) {
            $query->orWhere('content', 'LIKE', '%' . $keyword . '%');
        }
        $documents = $query->get();
        // Map documents to include match count and teacher info
        $results = $documents->map(function ($document) use ($keywordsArray) {
            $matchCount = 0;
            foreach ($keywordsArray as $keyword) {
                $matchCount += substr_count(strtolower($document->content), strtolower($keyword));
            }
            $teacher = Teacher::query()
                ->where('id', $document->teacher_id)->first();
            $teacher->match_count = $matchCount;
            $cv = $teacher->getMedia('CV')->first();
            $teacher->cv = $cv->original_url;
            return $teacher;
        });

        return $results->sortByDesc('match_count')->values();
    }

    public function saveCompleteInfo($teacher, $request)
    {
        $teacher->update([
            'details' => $request->details,
            'location_id' => $request->location_id,
            'phone_number' => $request->phone_number,
        ]);
        $this->saveProfilePhoto($teacher, $request);
        $this->saveSpecializations($teacher, $request->specializations);
        $path = $this->saveCV($teacher, $request);
        $this->saveCVPDF($teacher->id, $path);
        $this->saveSocialLinks($teacher, $request);
        $teacher->save();
        return $teacher;
    }

    public function saveCVPDF($teacher_id, $path)
    {
        try {
            // Normalize the path separator to forward slash '/' (optional)
            $normalizedPath = str_replace('\\', '/', $path);

            // No need to specify the path to pdftotext if it's in the PATH
            $pdfText = (new Pdf('C:\\poppler\\Library\\bin\\pdftotext.exe'))
                ->setPdf($normalizedPath)
                ->text();

            PdfDocument::query()->create([
                'teacher_id' => $teacher_id,
                'content' => Str::limit($pdfText, 60000),
            ]);
            return $pdfText;
        } catch (\Exception $e) {
            return "Error extracting PDF text: " . $e->getMessage();
        }
    }

    public function getProfile($user, $type)
    {
        $photo = $user->getMedia('ProfilePicture')->first();
        if ($photo != null) {
            $user['photo'] = $photo->original_url;
        } else
            $user['photo'] = null;
        $cv = $user->getMedia('CV')->first();
        if ($cv != null)
            $user['cv'] = $cv->original_url;
        else
            $user['cv'] = null;
        $data = $this->emailVerification->getToken($user);
        $data['user_type'] = $type;
        return $data;
    }

    public function saveProfilePhoto($teacher, $request)
    {
        if ($request->has('photo'))
            $teacher->addMedia($request->photo)->toMediaCollection('ProfilePicture');
    }

    public function saveSpecializations($teacher, $specializations)
    {
        foreach ($specializations as $specialization) {
            TeacherSpecialization::query()->create([
                'teacher_id' => $teacher->id,
                'specialization_id' => $specialization
            ]);
        }
    }

    public function saveCV($teacher, $request)
    {
        $media = $teacher->addMedia($request->cv)->toMediaCollection('CV');
        return $media->getPath();
    }

    public function saveSocialLinks($teacher, $request)
    {
        if ($request->social_links != null) {
            $social_links = $request->social_links;
            for ($i = 0; $i < sizeof($social_links); $i++) {
                $this->storeSocialLink($social_links[$i], $teacher->id);
            }
        }
    }

    public function storeSocialLink($social_link, $teacher_id)
    {
        if ($social_link['type'] == 'telegram') {
            if ($social_link['link'][0] == '@')
                $social_link['link'] = Str::replaceFirst('@', 'https://t.me/', $social_link['link']);
        }
        SocialLink::query()->create([
            'teacher_id' => $teacher_id,
            'type' => $social_link['type'],
            'link' => $social_link['link']
        ]);
    }

    public function getTeachers(): Collection|array
    {
        $teachers = Teacher::query()
            ->whereNot('id', Auth::id())
            ->get([
                'id',
                'first_name',
                'last_name'
            ]);
        foreach ($teachers as $teacher) {
            $photo = $teacher->getMedia('ProfilePicture')->first();
            if ($photo != null) {
                $teacher['photo'] = $photo->original_url;
            } else
                $teacher['photo'] = null;
//            $teacher['photo'] = $teacher->getMedia('ProfilePicture')[0]->original_url;
        }
        return $teachers;
    }

    /*
     * $display_myCourses_approved = Teacher::query()
            ->join('courses', 'teachers.id', '=', 'courses.teacher_id')
            ->where('teachers.id', $request->teacher_id)
            ->where('courses.status', '=', 'published')
            ->get([

                'courses.id as course_id',
                'courses.course_name',
                'courses.course_description',
                'courses.is_free',
                'courses.price',
                'courses.created_at',
                'courses.updated_at',
            ]);
        $display_info=Teacher::query()->with('media')
            ->where('teachers.id', $request->teacher_id)->get();
        $teacherPosts =Post::with('media')
            ->where('posts.teacher_id', $request->teacher_id)
            ->get([
                'posts.text',
                'posts.created_at',
            ]);
     */

    public function getTeacherProfile($request)
    {
        $teacher = $this->getTeacherInfo($request->teacher_id);
        $courses = $this->getTeacherCourses($teacher);
        $posts = $this->getTeacherPosts($teacher);
        return [
            'info' => $teacher,
            'courses' => $courses,
            'posts' => $posts
        ];
    }

    private function getTeacherInfo($teacher_id)
    {
        $teacher = Teacher::query()
            ->with('media')
            ->where('teachers.id', $teacher_id)
            ->get($this->teacherProfileData())
            ->first();
        $teacher = $this->getTeacherLinks($teacher);
        return $this->getTeacherLocation($teacher);
    }

    private function getTeacherCourses($teacher): Collection|array
    {
        return Course::query()
            ->where('teacher_id', $teacher->id)
            ->where('courses.status', '=', 'published')
            ->orderByDesc('created_at')
            ->get([
                'courses.id as course_id',
                'courses.course_name',
                'courses.course_description',
                'courses.is_free',
                'courses.price',
                'courses.created_at',
                'courses.updated_at',
            ]);
    }

    private function getTeacherPosts($teacher)
    {
        return Post::with('media')
            ->where('posts.teacher_id', $teacher->id)
            ->orderByDesc('created_at')
            ->get([
                'posts.id',
                'posts.text',
                'posts.created_at',
                'posts.updated_at'
            ]);
    }

    private function teacherProfileData(): array
    {
        return [
            'id',
            'first_name',
            'last_name',
            'email',
            'phone_number',
            'details',
            'is_available',
            'location_id',
            'status'
        ];
    }

    private function getTeacherLocation($teacher)
    {
        if ($teacher->location_id != null) {
            $location = Location::query()->find($teacher->location_id);
            $city = City::query()->find($location->city_id);
            $teacher['location'] = $location->location_name;
            $teacher['city'] = $city->city_name;
        }
        return $teacher;
    }

    private function getTeacherLinks($teacher)
    {
        $teacher['links'] = SocialLink::query()
            ->where('teacher_id', $teacher->id)
            ->get([
                'id', 'type', 'link'
            ]);
        return $teacher;
    }

    public function saveTeacherEditProfile($teacher, $request)
    {
        $this->saveFirstName($teacher, $request);
        $this->saveLastName($teacher, $request);
        $this->saveDetails($teacher, $request);
        $this->saveLocationId($teacher, $request);
        $this->savePhoneNumber($teacher, $request);
        $this->editProfilePicture($teacher, $request);
        if ($request->has('specializations'))
            $this->editSpecializations($teacher, $request->specializations);
        $this->editCV($teacher, $request);
        $this->editSocialLinks($teacher, $request);
        $teacher->save();
    }

    private function saveFirstName($teacher, $request)
    {
        if ($request->has('first_name'))
            $teacher->update(['first_name' => $request->first_name]);
    }

    private function saveLastName($teacher, $request)
    {
        if ($request->has('last_name'))
            $teacher->update(['last_name' => $request->last_name]);
    }

    private function saveDetails($teacher, $request)
    {
        if ($request->has('details'))
            $teacher->update(['details' => $request->details]);
    }

    private function savePhoneNumber($teacher, $request)
    {
        if ($request->has('phone_number'))
            $teacher->update(['phone_number' => $request->phone_number,]);
    }

    private function saveLocationId($teacher, $request)
    {
        if ($request->has('location_id'))
            $teacher->update(['location_id' => $request->location_id,]);
    }

    private function editProfilePicture($teacher, $request)
    {
        if ($request->has('photo')) {
            $teacher->clearMediaCollection('ProfilePicture');
            $teacher->addMedia($request->photo)->toMediaCollection('ProfilePicture');
        }
    }

    private function editSpecializations($teacher, $specializations)
    {
        TeacherSpecialization::query()
            ->where('teacher_id', $teacher->id)
            ->delete();
        foreach ($specializations as $specialization) {
            TeacherSpecialization::query()->create([
                'teacher_id' => $teacher->id,
                'specialization_id' => $specialization
            ]);
        }
    }

    private function editCV($teacher, $request)
    {
//        print('hhh');
        if ($request->has('cv')) {
//            print('fffffff');
            $teacher->clearMediaCollection('CV');
            $teacher->addMedia($request->cv)->toMediaCollection('CV');
        }
    }

    private function editSocialLinks($teacher, $request)
    {
        if ($request->social_links != null) {
            $social_links = $request->social_links;
            for ($i = 0; $i < sizeof($social_links); $i++) {
                $this->updateSocialLink($social_links[$i], $teacher->id);
            }
        }
    }

    private function updateSocialLink($social_link, $teacher_id)
    {
        if ($social_link['type'] == 'telegram') {
            if ($social_link['link'][0] == '@')
                $social_link['link'] = Str::replaceFirst('@', 'https://t.me/', $social_link['link']);
        }
        $link = SocialLink::query()->where('teacher_id', $teacher_id)
            ->where('type', $social_link['type'])
            ->first();
        if (isset($link))
            $link->update(['link' => $social_link['link']]);
        else
            SocialLink::query()->create([
                'teacher_id' => $teacher_id,
                'type' => $social_link['type'],
                'link' => $social_link['link']
            ]);
    }
}

//        $results = $documents->map(function ($document) use ($keywordsArray) {
//            $document->match_count = 0;
//            foreach ($keywordsArray as $keyword) {
//                $document->match_count += substr_count(strtolower($document->content), strtolower($keyword));
//            }
//            return $document;
//        });
