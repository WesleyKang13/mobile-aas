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

Route::get('/forgot_password', [App\Http\Controllers\Auth\LoginController::class, 'forgotPassword']);
Route::get('/email', [App\Http\Controllers\Auth\LoginController::class, 'email']);
Route::get('/change_password/{id}', [App\Http\Controllers\Auth\LoginController::class, 'password']);
Route::post('/change_password/{id}', [App\Http\Controllers\Auth\LoginController::class, 'passwordConfirm']);

Route::middleware(['userauth'])->group(function(){
    Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'dashboard']);
    Route::get('/attendance', [App\Http\Controllers\AttendanceController::class, 'index']);
    Route::get('/attendance/{id}/close', [App\Http\Controllers\AttendanceController::class, 'close']);
    Route::get('/attendance/{course_id}/{date}', [App\Http\Controllers\AttendanceController::class, 'sheet']);
    Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout']);

    // get user location
    Route::get('/user/{id}/course/{course_id}/location', [App\Http\Controllers\AttendanceController::class, 'location']);
    

});


