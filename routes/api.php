<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CreditTypeController;
use App\Http\Controllers\API\CrprospectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\MasterMenuController;


//Login Authenticate
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

//Route Group Master Menu
Route::middleware('auth:sanctum')->group(function () {
    Route::get('menu', [MasterMenuController::class, 'index']);
    Route::post('menu', [MasterMenuController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('credit_type/{status}', [CreditTypeController::class, 'index']);
    Route::get('credit_type', [CreditTypeController::class, 'index']);
});

//Route Group Cr Prospek (Kunjungan)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('kunjungan', [CrprospectController::class, 'index']);
    Route::get('kunjungan/detail/{id}', [CrprospectController::class, 'detail']);
    Route::post('kunjungan', [CrprospectController::class, 'store']);
    Route::put('kunjungan/{id}', [CrprospectController::class, 'update']);
    Route::delete('kunjungan/{id}', [CrprospectController::class, 'destroy']);
    Route::post('kunjunganImageUpload', [CrprospectController::class, 'uploadImage']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

