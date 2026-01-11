<?php

use App\Http\Controllers\Api\Admin\AuditLogController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Api\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Api\Admin\PatientController as AdminPatientController;
use App\Http\Controllers\Api\Admin\QueueController as AdminQueueController;
use App\Http\Controllers\Api\Admin\QueueSettingController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Admin\ScheduleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\QueueController;
use App\Http\Controllers\Api\QueueDisplayController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/queue-display', [QueueDisplayController::class, 'display']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/fcm-token', [AuthController::class, 'updateFcmToken']);

    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/departments/{department}', [DepartmentController::class, 'show']);
    Route::get('/departments/{id}/schedules', [DepartmentController::class, 'schedules']);

    Route::post('/queues/take', [QueueController::class, 'take']);
    Route::get('/queues/my', [QueueController::class, 'myQueues']);
    Route::get('/queues/active', [QueueController::class, 'active']);
    Route::get('/queues/status', [QueueController::class, 'status']);
    Route::post('/queues/{queue}/cancel', [QueueController::class, 'cancel']);

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('dashboard-stats', [DashboardController::class, 'stats']);
        
        Route::apiResource('departments', AdminDepartmentController::class);
        Route::apiResource('doctors', AdminDoctorController::class);

        Route::get('schedules', [ScheduleController::class, 'index']);
        Route::post('schedules', [ScheduleController::class, 'store']);
        Route::put('schedules/{schedule}', [ScheduleController::class, 'update']);
        Route::delete('schedules/{schedule}', [ScheduleController::class, 'destroy']);

        Route::get('queues', [AdminQueueController::class, 'index']);
        Route::post('queues/{queue}/call', [AdminQueueController::class, 'call']);
        Route::post('queues/{queue}/skip', [AdminQueueController::class, 'skip']);
        Route::post('queues/{queue}/done', [AdminQueueController::class, 'done']);
        Route::post('queues/{queue}/cancel', [AdminQueueController::class, 'cancelByAdmin']);
        Route::post('queues/{queue}/note', [AdminQueueController::class, 'addNote']);
        Route::post('queues/bulk-cancel', [AdminQueueController::class, 'bulkCancel']);
        Route::post('queues/reset-all', [AdminQueueController::class, 'resetAllQueues']);
        Route::get('reports/daily', [AdminQueueController::class, 'dailyReport']);

        Route::apiResource('patients', AdminPatientController::class)->only(['index', 'show', 'update', 'destroy']);
        
        Route::get('queue-settings', [QueueSettingController::class, 'index']);
        Route::post('queue-settings', [QueueSettingController::class, 'store']);
        Route::put('queue-settings/{id}', [QueueSettingController::class, 'update']);

        Route::get('audit-logs', [AuditLogController::class, 'index']);
        Route::get('audit-logs/{id}', [AuditLogController::class, 'show']);

        Route::get('reports/queue-data', [ReportController::class, 'queueData']);
        Route::get('reports/queue-pdf', [ReportController::class, 'queueReport']);
    });
});
