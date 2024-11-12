<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\PrivateLessonController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/subscribe/success', function (Request $request) {
    $course_id = $request->query('course_id');
    $student_id = $request->query('student_id');
    return view('subscribe', ['course_id' => $course_id, 'student_id' => $student_id]);
})->name('subscribe.success');

Route::get('/process-subscription/{course_id}/{student_id}', [CourseController::class, 'subscribeToCourse'])->name('process.subscription');

Route::get('/confirm_lesson/{lesson_id}/', [PrivateLessonController::class, 'confirmLessonPayment'])->name('confirm.lesson');

