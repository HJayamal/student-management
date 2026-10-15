<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\APIController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and assigned
| to the "api" middleware group.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Get Student List API
Route::get('/student_list', [APIController::class, 'student_list']);

// Get Student List
Route::get('/student_list', [APIController::class, 'student_list']);

// Save Student
Route::post('/student/save', [APIController::class, 'store']);
