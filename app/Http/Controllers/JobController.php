<?php

namespace App\Http\Controllers;

use App\Http\Requests\Educational\AddJobRequest;
use App\Http\Requests\Teacher\DetailsJobRequest;
use App\Http\Requests\Teacher\DisplayProfileEducationalRequest;
use App\Http\Services\Responses\AdminResponse;
use App\Http\Services\Responses\TeacherResponse;
use App\Models\Educational;
use App\Models\Job;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    private TeacherResponse $teacher_response;
    private AdminResponse $admin_response;

    public function __construct()
    {
        $this->teacher_response = new TeacherResponse();
        $this->admin_response = new AdminResponse();
    }

    public function display_all_jobs()
    {
        $display_all_jobs = Job::query()
            ->with('media')
            ->join('educationals', 'educationals.id', '=', 'jobs.educational_id')
            ->orderBy('jobs.updated_at', 'desc')
            ->get([
                'jobs.id',
                'jobs.educational_id',
                'jobs.job',
                'educationals.name as educational_name',
                'jobs.created_at',
                'jobs.updated_at',
            ]);

        $jobs_with_education = $display_all_jobs->map(function ($display_all_job) {
            $media = $display_all_job->media->map(function ($mediaItem) {
                return [
                    'id' => $mediaItem->id,
                    'model_type' => $mediaItem->model_type,
                    'model_id' => $mediaItem->model_id,
                    'uuid' => $mediaItem->uuid,
                    'collection_name' => $mediaItem->collection_name,
                    'name' => $mediaItem->name,
                    'file_name' => $mediaItem->file_name,
                    'mime_type' => $mediaItem->mime_type,
                    'disk' => $mediaItem->disk,
                    'conversions_disk' => $mediaItem->conversions_disk,
                    'size' => $mediaItem->size,
                    'manipulations' => $mediaItem->manipulations,
                    'custom_properties' => $mediaItem->custom_properties,
                    'generated_conversions' => $mediaItem->generated_conversions,
                    'responsive_images' => $mediaItem->responsive_images,
                    'order_column' => $mediaItem->order_column,
                    'created_at' => $mediaItem->created_at,
                    'updated_at' => $mediaItem->updated_at,
                    'original_url' => $mediaItem->original_url,
                    'preview_url' => $mediaItem->preview_url,
                ];
            });

            return [
                'id' => $display_all_job->id,
                'job' => $display_all_job->job,
                'educational_id' => $display_all_job->educational_id,
                'educational_name' => $display_all_job->educational_name,
                'photo' => null, // Assuming this is not relevant anymore
                'created_at' => $display_all_job->created_at,
                'updated_at' => $display_all_job->updated_at,
                'media' => $media,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => trans('messages.display_all_jobs'),
            'data' => $jobs_with_education,
        ], 200);
    }

    public function display_details_jobs(DetailsJobRequest $request)
    {
        $display_details_jobs = Job::query()->with('media')
            ->where('jobs.id', $request->job_id)
            ->get([
                'id',
                'educational_id',
                'job',
            ]);
        return response()->json([
            'status' => true,
            'message' => trans('messages.display_details_jobs'),
            'data' => $display_details_jobs
        ], 400);

    }

    public function display_profile_educational(DisplayProfileEducationalRequest $request)
    {
        $display_profile_educational = Educational::query()
            ->with('media')
            ->leftJoin('locations', 'locations.id', '=', 'educationals.location_id')
            ->leftJoin('cities', 'cities.id', '=', 'locations.city_id')
            ->where('educationals.id', $request->educational_id)
            ->select([
                'educationals.id',
                'educationals.name',
                'educationals.email',
                'educationals.phone_number',
                'educationals.details',
                'educationals.type',
                'locations.location_name',
                'cities.city_name',
            ])
            ->first();

        // Ensure default values for location_name and city_name if they are null
        if ($display_profile_educational) {
            $display_profile_educational->location_name = $display_profile_educational->location_name ?? 'null';
            $display_profile_educational->city_name = $display_profile_educational->city_name ?? 'null';
        }

//        $display_jobs_educational = Educational::query()
//            ->with('media')
//            ->leftJoin('jobs', 'jobs.educational_id', '=', 'educationals.id')
//            ->where('educationals.id', $request->educational_id)
//            ->get([
//                'jobs.id',
//                'jobs.job',
//            ]);
        $display_jobs_educational = Job::query()
            ->with('media')
//            ->leftJoin('jobs', 'jobs.educational_id', '=', 'educationals.id')
            ->where('educational_id', $request->educational_id)
            ->get([
                'id',
                'job',
            ]);

        return $this->admin_response->displayProfileEducationalResponse($display_profile_educational, $display_jobs_educational);
    }

//    public function display_profile_educational(DisplayProfileEducationalRequest $request)
//    {
//        // Load the educational profile with media, location, and city
//        $display_profile_educational = Educational::query()
//            ->with('media')
//            ->leftJoin('locations', 'locations.id', '=', 'educationals.location_id')
//            ->leftJoin('cities', 'cities.id', '=', 'locations.city_id')
//            ->where('educationals.id', $request->educational_id)
//            ->select([
//                'educationals.id',
//                'educationals.name',
//                'educationals.email',
//                'educationals.phone_number',
//                'educationals.details',
//                'educationals.type',
//                'locations.location_name',
//                'cities.city_name',
//            ])
//            ->first();
//
//        // Ensure default values for location_name and city_name if they are null
//        if ($display_profile_educational) {
//            $display_profile_educational->location_name = $display_profile_educational->location_name ?? 'null';
//            $display_profile_educational->city_name = $display_profile_educational->city_name ?? 'null';
//
//            // Load and format media data
//            $display_profile_educational->media = $display_profile_educational->media->map(function ($mediaItem) {
//                return [
//                    'id' => $mediaItem->id,
//                    'model_type' => $mediaItem->model_type,
//                    'model_id' => $mediaItem->model_id,
//                    'uuid' => $mediaItem->uuid,
//                    'collection_name' => $mediaItem->collection_name,
//                    'name' => $mediaItem->name,
//                    'file_name' => $mediaItem->file_name,
//                    'mime_type' => $mediaItem->mime_type,
//                    'disk' => $mediaItem->disk,
//                    'conversions_disk' => $mediaItem->conversions_disk,
//                    'size' => $mediaItem->size,
//                    'manipulations' => $mediaItem->manipulations,
//                    'custom_properties' => $mediaItem->custom_properties,
//                    'generated_conversions' => $mediaItem->generated_conversions,
//                    'responsive_images' => $mediaItem->responsive_images,
//                    'order_column' => $mediaItem->order_column,
//                    'created_at' => $mediaItem->created_at,
//                    'updated_at' => $mediaItem->updated_at,
//                    'original_url' => $mediaItem->original_url,
//                    'preview_url' => $mediaItem->preview_url,
//                ];
//            })->first(); // Remove the wrapping array
//        }
//
//        // Load jobs with media
//        $display_jobs_educational = Educational::query()
//            ->with('media')
//            ->leftJoin('jobs', 'jobs.educational_id', '=', 'educationals.id')
//            ->where('educationals.id', $request->educational_id)
//            ->get([
//                'jobs.id',
//                'jobs.job',
//            ]);
//
//        $display_jobs_educational = $display_jobs_educational->map(function ($job) {
//            $job->media = $job->media->map(function ($mediaItem) {
//                return [
//                    'id' => $mediaItem->id,
//                    'model_type' => $mediaItem->model_type,
//                    'model_id' => $mediaItem->model_id,
//                    'uuid' => $mediaItem->uuid,
//                    'collection_name' => $mediaItem->collection_name,
//                    'name' => $mediaItem->name,
//                    'file_name' => $mediaItem->file_name,
//                    'mime_type' => $mediaItem->mime_type,
//                    'disk' => $mediaItem->disk,
//                    'conversions_disk' => $mediaItem->conversions_disk,
//                    'size' => $mediaItem->size,
//                    'manipulations' => $mediaItem->manipulations,
//                    'custom_properties' => $mediaItem->custom_properties,
//                    'generated_conversions' => $mediaItem->generated_conversions,
//                    'responsive_images' => $mediaItem->responsive_images,
//                    'order_column' => $mediaItem->order_column,
//                    'created_at' => $mediaItem->created_at,
//                    'updated_at' => $mediaItem->updated_at,
//                    'original_url' => $mediaItem->original_url,
//                    'preview_url' => $mediaItem->preview_url,
//                ];
//            })->first(); // Remove the wrapping array
//            return $job;
//        });
//
//        return $this->admin_response->displayProfileEducationalResponse($display_profile_educational, $display_jobs_educational);
//    }

    public function addJob(AddJobRequest $request)
    {
        $job = Job::query()->create([
            'educational_id' => Auth::id(),
            'job' => $request->text,
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $job->addMedia($file)->toMediaCollection('Job');
            }
        }

        return response()->json([
            'status' => true,
            'message' => trans('messages.post_added'),
            'data' => []
        ], 201);
    }
}
