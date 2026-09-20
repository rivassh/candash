<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobPositionController;
use App\Http\Controllers\Api\JobSourceCredentialController;
use App\Http\Controllers\Api\JobVisionCredentialController;
use Illuminate\Support\Facades\Route;

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('job-positions/import-from-source', [JobPositionController::class, 'importFromSource'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::get('job-source-credentials/providers', [JobSourceCredentialController::class, 'providers']);
    Route::post('job-source-credentials', [JobSourceCredentialController::class, 'store']);
    Route::put('job-source-credentials/{credential}', [JobSourceCredentialController::class, 'update']);
    Route::delete('job-source-credentials/{credential}', [JobSourceCredentialController::class, 'destroy']);
    Route::post('job-source-credentials/{credential}/activate', [JobSourceCredentialController::class, 'activate']);
    Route::get('job-source-credentials', [JobSourceCredentialController::class, 'index']);
});

Route::get('health', function () {
    return response()->json(['status' => 'ok', 'app' => 'TalentMatch']);
});