<?php

namespace App\Http\Services;

use Ichtrojan\Otp\Otp;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class EmailVerification
{
    private $otp;

    public function __construct()
    {
        $this->otp = new Otp();
    }

    public function getOtp($request)
    {
        return $this->otp->validate($request->email, $request->code);
    }

    public function getToken($user)
    {
        $token = JWTAuth::fromUser($user);
        $data = $user;
        $data['token'] = $token;
        return $data;
    }
}
