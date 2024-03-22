<?php

use App\Http\Controllers\API\AssetsController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CreditTypeController;
use App\Http\Controllers\API\CrprospectController;
use App\Http\Controllers\API\DetailProfileController;
use App\Http\Controllers\API\Menu\MasterMenuController;
use App\Http\Controllers\API\Menu\MasterRoleController;
use App\Http\Controllers\API\TestController;
use App\Http\Controllers\Laporan\HistoryAccController;
use App\Http\Controllers\Laporan\PaymentDumpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Login Authenticate
Route::post('auth/login', [AuthController::class, 'login'])->name('login');
Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('payment', [PaymentDumpController::class, 'index']);
Route::post('historyAcc', [HistoryAccController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    //Route Group Master Menu
    Route::get('menu', [MasterMenuController::class, 'index']);
    Route::get('role', [MasterRoleController::class, 'index']);
    Route::post('menu', [MasterMenuController::class, 'store']);
    Route::post('role', [MasterRoleController::class, 'store']);

    //Detail Profile
    Route::get('me', [DetailProfileController::class, 'index']);

    //Route Group Credit Type
    Route::get('credit_type/{status}', [CreditTypeController::class, 'index']);
    Route::get('credit_type', [CreditTypeController::class, 'index']);

    //Route Group Cr Prospek (Kunjungan)
    Route::get('kunjungan', [CrprospectController::class, 'index']);
    Route::post('kunjungan/detail', [CrprospectController::class, 'detail']);
    Route::post('kunjungan', [CrprospectController::class, 'store']);
    Route::put('kunjungan/{id}', [CrprospectController::class, 'update']);
    Route::delete('kunjungan/{id}', [CrprospectController::class, 'destroy']);
    Route::post('image_upload_prospect', [CrprospectController::class, 'uploadImage']);
    Route::post('multi-upload-images', [CrprospectController::class, 'multiImage']);

    Route::get('test', [TestController::class, 'index']);
});

Route::post('assets', [AssetsController::class, 'storeAsset']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
