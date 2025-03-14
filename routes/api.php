<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function(){
    return response([
        'message' => 'API is working'
    ], 200);
});

Route::post('/debug', function () {
    return response()->json(['message' => 'POST request received']);
});

Route::post('/login', [AuthController::class, 'login']); // No auth required

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('user/index', [UserController::class, 'index']); 
    Route::get('user/show/{id}', [UserController::class, 'show']); 
    Route::put('user/update/{id}', [UserController::class, 'update']); 
    Route::delete('user/destroy/{id}', [UserController::class, 'destroy']); 

    Route::get('student/index', [StudentController::class, 'index']); 
    Route::get('student/show/{id}', [StudentController::class, 'show']); 
    Route::put('student/update/{id}', [StudentController::class, 'update']); 
    Route::delete('student/destroy/{id}', [StudentController::class, 'destroy']); 
    Route::get('student/filterbyclass', [StudentController::class, 'filterByClass']); 
    Route::get('student/search/{name}', [StudentController::class, 'searchByName']); 

    Route::post('/attendance/mark', [AttendanceController::class, 'markAttendance']);
    Route::get('/attendance/dates', [AttendanceController::class, 'getAttendanceDates']);
    Route::get('/attendance/class/{class_id}', [AttendanceController::class, 'getAttendanceByClass']);
    Route::get('/attendance/date', [AttendanceController::class, 'getAttendanceByDate']);
    Route::get('/attendance/status', [AttendanceController::class, 'getAttendanceByStatus']);
    Route::get('/attendance/filter', [AttendanceController::class, 'getFilteredAttendance']);
});