<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\DoctorAuthController;
use App\Http\Controllers\DoctorProfileController;
use App\Http\Controllers\Api\DoctorScheduleController;
use App\Http\Controllers\Api\AdminManageController;
use App\Http\Middleware\CheckAdmin;
 //use App\Http\Controllers\Api\FavouriteController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DoctorBookingController;
use App\Http\Controllers\RatingRequestController;
use App\Http\Controllers\Api\PrescriptionController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['signed'])->name('verification.verify');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail']);
    Route::post('/profile/update', [UserProfileController::class, 'update']);
    Route::get('/profile', [UserProfileController::class, 'getProfile']);
});

// 🧑‍⚕️ Doctor Auth Routes
    Route::prefix('doctor')->group(function () {
    Route::post('/register', [DoctorAuthController::class, 'register']);
    Route::post('/verify', [DoctorAuthController::class, 'verify']);
    Route::post('/login', [DoctorAuthController::class, 'login']);
    Route::post('/forgot-password', [DoctorAuthController::class, 'forgotPassword']);
    Route::post('/verify-code', [DoctorAuthController::class, 'verifyCode']);
    Route::post('/reset-password', [DoctorAuthController::class, 'resetPassword']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/change-password', [DoctorAuthController::class, 'changePassword']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/doctor/profile', [DoctorProfileController::class, 'show']);
    Route::post('/doctor/profile/update', [DoctorProfileController::class, 'update']);
});




Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::post('/change-password', [AdminAuthController::class, 'changePassword']);

        
        Route::get('/doctors/pending', [DoctorController::class, 'pendingDoctors']);
        Route::post('/doctors/approve/{id}', [DoctorController::class, 'approveDoctor']);
        Route::post('/doctors/reject/{id}', [DoctorController::class, 'rejectDoctor']);
    });
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/doctor-schedules', [DoctorScheduleController::class, 'index']);
    Route::post('/doctor-schedules', [DoctorScheduleController::class, 'store']);
    Route::put('/doctor-schedules/{id}', [DoctorScheduleController::class, 'update']);
    Route::delete('/doctor-schedules/{id}', [DoctorScheduleController::class, 'destroy']);
});


Route::middleware(['auth:sanctum', CheckAdmin::class])->group(function () {
    Route::delete('/admin/delete/{id}', [AdminManageController::class, 'destroy']);
});



Route::get('/doctors/specialization/{specialization}', [DoctorAuthController::class, 'getDoctorsBySpecialization']);
Route::get('doctor/{id}/profile', [DoctorAuthController::class, 'getDoctorProfile']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/favourites', [FavouriteController::class, 'index']);
    Route::post('/favourites', [FavouriteController::class, 'store']);
    Route::delete('/favourites/{doctor_id}', [FavouriteController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/booking', [BookingController::class, 'book']);
    Route::get('/transactions', [BookingController::class, 'myTransactions']);
    Route::post('/booking/{id}/cancel', [BookingController::class, 'cancelBooking']);
});



Route::middleware('auth:api')->get('/my-patients', [DoctorBookingController::class, 'myPatients']);

Route::get('/admin/statistics', [\App\Http\Controllers\Api\Admin\StatisticsController::class, 'index']);
Route::get('/admin/charts', [\App\Http\Controllers\Api\Admin\ChartController::class, 'index']);



// routes/api.php
Route::get('/send-rating-requests', [RatingRequestController::class, 'sendRequests']);


// routes/api.php
Route::post('/submit-rating', [RatingRequestController::class, 'submitRating'])->middleware('auth:sanctum');


// للدكتور
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/doctor/prescriptions', [PrescriptionController::class, 'store']);
    Route::get('/doctor/prescriptions/count', [PrescriptionController::class, 'count']);
});

// للمستخدم
Route::middleware(['auth:sanctum'])->get('/user/prescriptions', [PrescriptionController::class, 'myPrescriptions']);














