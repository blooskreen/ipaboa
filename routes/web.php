<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ShowcaseController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::get('/showcase', [ShowcaseController::class, 'index'])->name('showcase.index');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/gallery/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/gallery/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::post('/courses/{course}/complete', [CourseController::class, 'complete'])->name('courses.complete');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::delete('/profile/photo', [ProfileController::class, 'removePhoto'])->name('profile.photo.remove');
    Route::delete('/profile/banner', [ProfileController::class, 'removeBanner'])->name('profile.banner.remove');

    Route::post('/showcase', [ShowcaseController::class, 'store'])->name('showcase.store');
    Route::delete('/showcase/{post}', [ShowcaseController::class, 'destroy'])->name('showcase.destroy');
    Route::post('/showcase/{post}/comment', [ShowcaseController::class, 'comment'])->name('showcase.comment');
    Route::delete('/showcase/comment/{comment}', [ShowcaseController::class, 'destroyComment'])->name('showcase.comment.destroy');
    Route::post('/showcase/{post}/like', [ShowcaseController::class, 'like'])->name('showcase.like');
    Route::post('/showcase/{post}/vote', [ShowcaseController::class, 'vote'])->name('showcase.vote');

    Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::post('/quizzes/{quiz}/start', [QuizController::class, 'start'])->name('quizzes.start');
    Route::get('/quizzes/attempt/{attempt}', [QuizController::class, 'take'])->name('quizzes.take');
    Route::post('/quizzes/attempt/{attempt}/answer', [QuizController::class, 'answer'])->name('quizzes.answer');
    Route::post('/quizzes/attempt/{attempt}/submit', [QuizController::class, 'submit'])->name('quizzes.submit');
    Route::get('/quizzes/attempt/{attempt}/result', [QuizController::class, 'result'])->name('quizzes.result');

    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])
        ->name('certificates.download');
});
