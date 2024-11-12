<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function getLocations(): JsonResponse
    {
        $result = City::query()->with('locations')->get(['id', 'city_name']);
        return response()->json([
            'status' => true,
            'message' => trans('messages.locations'),
            'data' => $result
        ]);
    }
}
