<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;

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

Route::post('/register', [AuthController::class, 'register'])->middleware('auth:sanctum');
Route::post('/login', [AuthController::class, 'login']);

Route::post('user/index', [UserController::class, 'index'])->middleware('auth:sanctum');
Route::post('user/show/{id}', [UserController::class, 'show'])->middleware('auth:sanctum');
Route::post('user/update/{id}', [UserController::class, 'update'])->middleware('auth:sanctum');
Route::post('user/destroy/{id}', [UserController::class, 'destroy'])->middleware('auth:sanctum');

Route::post('student/index', [StudentController::class, 'index'])->middleware('auth:sanctum');
Route::post('student/show/{id}', [StudentController::class, 'show'])->middleware('auth:sanctum');
Route::post('student/update/{id}', [StudentController::class, 'update'])->middleware('auth:sanctum');
Route::post('student/destroy/{id}', [StudentController::class, 'destroy'])->middleware('auth:sanctum');