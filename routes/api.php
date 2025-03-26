<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;

// مسارات تسجيل المستخدم
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');

// استعادة كلمة المرور
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
Route::match(['post', 'put'], '/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
  // تغيير إلى PUT

// التحقق من البريد الإلكتروني
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

// إعادة إرسال رابط التحقق
Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])
    ->middleware('auth:sanctum');

// مسارات تسجيل دخول وخروج الأدمن
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);
    Route::post('/admin/change-password', [AdminAuthController::class, 'changePassword']);
});
