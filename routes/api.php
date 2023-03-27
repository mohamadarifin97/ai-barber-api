<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QueueController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/get-queue', [QueueController::class, 'getQueue']);

Route::controller(AuthController::class)->group(function(){
    Route::post('/admin/register', 'register');
    Route::post('/admin/login', 'login');
    Route::post('/admin/logout', 'logout');
    // Route::get('/admin/get-token', [AuthController::class, 'getToken']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/get-queue-list', [QueueController::class, 'getQueueList']);
    Route::post('/admin/queue-complete', [QueueController::class, 'queueComplete']);
    Route::post('/admin/update-status', [QueueController::class, 'updateStatus']);
    Route::post('/admin/update-store-status', [QueueController::class, 'updateStoreStatus']);
});