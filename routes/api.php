<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\DoctorAuthController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');




Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::match(['post', 'put'], '/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');





Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');




Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])
    ->middleware('auth:sanctum');






Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);
    Route::post('/admin/change-password', [AdminAuthController::class, 'changePassword']);
});




Route::middleware('auth:sanctum')->group(function () {
    Route::post('/profile/update', [UserProfileController::class, 'update']);
});




Route::middleware('auth:sanctum')->get('/profile', [UserProfileController::class, 'getProfile']);






Route::post('doctor/register', [DoctorAuthController::class, 'register']);
Route::post('doctor/verify', [DoctorAuthController::class, 'verify']);
Route::post('doctor/login', [DoctorAuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/doctor/change-password', [App\Http\Controllers\Api\DoctorAuthController::class, 'changePassword']);
});









Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('admin/doctors/pending', [DoctorController::class, 'pendingDoctors']);
    Route::post('admin/doctors/approve/{id}', [DoctorController::class, 'approveDoctor']);
    Route::post('admin/doctors/reject/{id}', [DoctorController::class, 'rejectDoctor']);
});
