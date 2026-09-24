<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobPositionController;
use App\Http\Controllers\Api\JobSourceCredentialController;
use App\Http\Controllers\Api\JobVisionCredentialController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

// Temporarily disabled authentication for login - all visitors have admin access
// To re-enable auth, set DISABLE_LOGIN_AUTH=false
if (env('DISABLE_LOGIN_AUTH', true)) {
    Route::post('auth/login', [AuthController::class, 'login']);
} else {
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
}
Route::post('job-positions/import-from-source', [JobPositionController::class, 'importFromSource'])->middleware('auth:sanctum');

Route::post('job-positions/simple-collect', [JobPositionController::class, 'simpleCollect'])->middleware('auth:sanctum');

Route::post('job-positions/simple-status', [JobPositionController::class, 'simpleStatus'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::middleware('admin')->group(function () {
        Route::get('job-source-credentials/providers', [JobSourceCredentialController::class, 'providers']);
        Route::post('job-source-credentials', [JobSourceCredentialController::class, 'store']);
        Route::put('job-source-credentials/{credential}', [JobSourceCredentialController::class, 'update']);
        Route::delete('job-source-credentials/{credential}', [JobSourceCredentialController::class, 'destroy']);
        Route::post('job-source-credentials/{credential}/activate', [JobSourceCredentialController::class, 'activate']);
        Route::get('job-source-credentials', [JobSourceCredentialController::class, 'index']);
    });
});

// Simple JobVision API endpoints (read-only)
Route::get('job-positions', [JobPositionController::class, 'index']);
Route::get('job-positions/simple-collect', [JobPositionController::class, 'simpleCollect']);
Route::get('job-positions/simple-status', [JobPositionController::class, 'simpleStatus']);



Route::middleware('auth:sanctum')->prefix('search')->group(function () {
    Route::get('health', [SearchController::class, 'index']);
    Route::get('candidates', [SearchController::class, 'candidates']);
    Route::get('jobs', [SearchController::class, 'jobs']);
    Route::post('reindex', [SearchController::class, 'reindex']);
    Route::get('match', [SearchController::class, 'matchWithSearch']);
});

Route::get('health', function () {
    return response()->json(['status' => 'ok', 'app' => 'TalentMatch']);
});