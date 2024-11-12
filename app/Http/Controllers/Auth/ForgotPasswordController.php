<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequests\ForgotPasswordRequest;
use App\Http\Services\Responses\AuthResponse;
use App\Models\Educational;
use App\Models\Student;
use App\Models\Teacher;
use App\Notifications\ForgotPasswordNotification;
use Illuminate\Http\JsonResponse;
use JetBrains\PhpStorm\Pure;

class ForgotPasswordController extends Controller
{
    private AuthResponse $response;

    #[Pure] public function __construct()
    {
        $this->response = new AuthResponse();
    }


    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        if (Student::query()->where('email', $request->email)->exists()) {
            $user = Student::query()->where('email', $request->email)->first();
            $userType = 'student';
        } else if (Educational::query()->where('email', $request->email)->exists()) {
            $user = Educational::query()->where('email', $request->email)->first();
            $userType = 'educational';
        } else if (Teacher::query()->where('email', $request->email)->exists()) {
            $user = Teacher::query()->where('email', $request->email)->first();
            $userType = 'teacher';
        } else {
            return $this->response->accountNotFoundResponse();
        }
        $user->notify(new ForgotPasswordNotification());
        return $this->response->forgotPasswordResponse($userType);
    }
}
