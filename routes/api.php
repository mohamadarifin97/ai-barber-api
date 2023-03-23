<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
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
Route::get('/get-queue', [UserController::class, 'getQueue']);

Route::controller(AuthController::class)->group(function(){
    Route::post('/admin/register', 'register');
    Route::post('/admin/login', 'login');
    Route::post('/admin/logout', 'logout');
    // Route::get('/admin/get-token', [AuthController::class, 'getToken']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/get-queue', [AdminController::class, 'getQueue']);
    Route::post('/admin/next-queue', [AdminController::class, 'nextQueue']);
});