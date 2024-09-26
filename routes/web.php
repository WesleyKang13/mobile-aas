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

Route::middleware(['userauth'])->group(function(){
    Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'dashboard']);
    Route::get('/attendance', [App\Http\Controllers\AttendanceController::class, 'index']);
    Route::get('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout']);

    // get user location
    Route::get('/user/{id}/course/{course_id}/location', [App\Http\Controllers\AttendanceController::class, 'location']);
    Route::get('/attendance/{id}/close', [App\Http\Controllers\AttendanceController::class, 'close']);

});


