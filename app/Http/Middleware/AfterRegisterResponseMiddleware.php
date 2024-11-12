<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\EducationalAuthController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class AfterRegisterResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\AuthResponse)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        print Carbon::now();
        return $next($request);
    }

    public function terminate(){//$request, $response) {
//        (new EducationalAuthController())->saveEducational($request);
        print Carbon::now();
    }
}
