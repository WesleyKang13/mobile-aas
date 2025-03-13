<?php

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
    return redirect('/dashboard');
});

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'index']);
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'authenticate']);
Route::get('/pin', [App\Http\Controllers\Auth\LoginController::class, 'pin']);
Route::post('/pin', [App\Http\Controllers\Auth\LoginController::class, 'pin']);
Route::get('/forgot_password', [App\Http\Controllers\Auth\LoginController::class, 'forgotPassword']);
Route::get('/email', [App\Http\Controllers\Auth\LoginController::class, 'email']);
Route::get('/reset_password', [App\Http\Controllers\Auth\LoginController::class, 'reset']);
Route::post('/reset_password', [App\Http\Controllers\Auth\LoginController::class, 'resetPassword']);

Route::middleware(['userauth'])->group(function(){

    Route::get('/change_password/{id}', [App\Http\Controllers\Auth\PasswordController::class, 'password']);
    Route::post('/change_password/{id}', [App\Http\Controllers\Auth\PasswordController::class, 'passwordConfirm']);

    Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'dashboard']);
    Route::get('/attendance', [App\Http\Controllers\AttendanceController::class, 'index']);
    Route::get('/attendance/{id}/close', [App\Http\Controllers\AttendanceController::class, 'close']);
    Route::get('/attendance/{course_id}/{date}', [App\Http\Controllers\AttendanceController::class, 'sheet']);
    Route::get('/attendance/{id}/{date}/advanced', [App\Http\Controllers\AttendanceController::class, 'advanced_view']);
    Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout']);
    Route::get('/attendance/manual/{course_id}/{date}', [App\Http\Controllers\AttendanceController::class, 'manual']);
    Route::post('/attendance/manual/{course_id}/{date}', [App\Http\Controllers\AttendanceController::class, 'manualEntry']);

    // get user location
    Route::get('/user/{id}/course/{course_id}/location', [App\Http\Controllers\AttendanceController::class, 'location']);

    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index']);
    Route::get('/notifications/count', [App\Http\Controllers\NotificationController::class, 'count']);
    Route::get('/notifications/compose/{user_id}/{status}', [App\Http\Controllers\NotificationController::class, 'create']);
    Route::post('/notifications/compose/{user_id}/{status}', [App\Http\Controllers\NotificationController::class, 'store']);
    Route::get('/notifications/status/{id}', [App\Http\Controllers\NotificationController::class, 'read']);
    Route::get('/notifications/readall',  [App\Http\Controllers\NotificationController::class, 'readAll']);
    Route::get('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'show']);
    Route::post('/notifications/{id}/reply', [App\Http\Controllers\NotificationController::class, 'reply']);
    Route::get('/notifications/{id}/send', [App\Http\Controllers\NotificationController::class, 'send']);
    Route::get('/notifications/{id}/download_attachment', [App\Http\Controllers\NotificationController::class, 'download']);
});


