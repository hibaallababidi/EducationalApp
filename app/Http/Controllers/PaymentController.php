<?php

namespace App\Http\Controllers;

use App\Http\Requests\Student\CoursePaymentRequest;
use App\Http\Requests\Student\LessonPaymentRequest;
use App\Models\Course;
use App\Models\PrivateLesson;
use Exception;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeClient;

class PaymentController extends Controller
{
    public function payCourse(CoursePaymentRequest $request)
    {
        $course = Course::query()->find($request->course_id);
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $stripe = new StripeClient(env('STRIPE_SECRET'));
        try {
            // Create a new Stripe product if not already created
            $product = $stripe->products->create([
                'name' => $course->course_name,
            ]);

            $price = $stripe->prices->create([
                'currency' => 'eur',
                'unit_amount' => $course->price * 100,
                'product' => $product->id,
            ]);

            // Generate the success URL with the JWT token and course_id
//            $successUrl = route('subscribe.success', [
//                'course_id' => $course->id,
//                'student_id' => Auth::id(),
//            ]);
            $successUrl = route('process.subscription', [
                'course_id' => $course->id,
                'student_id' => Auth::id()
            ]);


//        الـ price هو المبلغ اللي بدكن تسحبوه من اليورر
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $price->id,
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => 'http://127.0.0.1:8000',
//                'cancel_url' => env('client_url'),
            ]);
            if ($session['url']) {
                return response()->json([
                    'status' => true,
                    'message' => '',
                    'data' => $session['url']
                ]);
            }
        } catch (Exception $e) {
            // Handle any errors that occur during the payment process
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function payLesson(LessonPaymentRequest $request)
    {
        $lesson = PrivateLesson::query()->find($request->lesson_id);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $stripe = new StripeClient(env('STRIPE_SECRET'));
        try {
            // Create a new Stripe product if not already created
            $product = $stripe->products->create([
                'name' => 'Lesson ' . $lesson->id,
            ]);

            $price = $stripe->prices->create([
                'currency' => 'eur',
                'unit_amount' => $lesson->price * 100,
                'product' => $product->id,
            ]);


            $successUrl = route('confirm.lesson', [
                'lesson_id' => $lesson->id,
            ]);

//        الـ price هو المبلغ اللي بدكن تسحبوه من اليورر
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $price->id,
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => 'http://127.0.0.1:8000',
//                'cancel_url' => env('client_url'),
            ]);
            if ($session['url']) {
                return response()->json([
                    'status' => true,
                    'message' => '',
                    'data' => $session['url']
                ]);
            }
        } catch (Exception $e) {
            // Handle any errors that occur during the payment process
            return response()->json(['status' => false, 'message' => $e->getMessage(),'hh'], 500);
        }
    }
}

//                $booking = StudentSubscription::create([
//                    'course_id' => $course->id,
//                    'student_id' => Auth::id(),
//                ]);

//                $booking->transaction()->create([
//                    'booking_id' => $booking->id,
//                    'payment_transaction_id' => $session->id,
//                    'user_id' => Auth::id(),
//                ]);
