<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {

    // Public
    Route::post('/auth/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::get('/health', fn () => response()->json(['status' => 'ok', 'app' => 'TalentMatch']));

    // Authenticated
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('/auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::get('/auth/me', [\App\Http\Controllers\Api\AuthController::class, 'me']);

        // Dashboard
        Route::get('/dashboard/summary', [\App\Http\Controllers\Api\DashboardController::class, 'summary']);

        // Job Positions
        Route::apiResource('JobPositions', \App\Http\Controllers\Api\JobPositionController::class);
        Route::post('/job-positions/import-from-source', [\App\Http\Controllers\Api\JobPositionController::class, 'importFromSource']);

        // Candidates
        Route::apiResource('candidates', \App\Http\Controllers\Api\CandidateController::class);
        Route::post('/candidates/{candidate}/resume', [\App\Http\Controllers\Api\CandidateController::class, 'uploadResume']);
        Route::post('/candidates/{candidate}/enrich-linkedin', [\App\Http\Controllers\Api\CandidateController::class, 'enrichLinkedin']);
        Route::put('/candidates/{candidate}/profile', [\App\Http\Controllers\Api\CandidateController::class, 'updateProfile']);

        // Skills dictionary
        Route::apiResource('skills', \App\Http\Controllers\Api\SkillController::class);

        // Match
        Route::post('/match/run', [\App\Http\Controllers\Api\MatchController::class, 'run']);
        Route::get('/match/results', [\App\Http\Controllers\Api\MatchController::class, 'index']);
        Route::get('/match/results/{matchResult}', [\App\Http\Controllers\Api\MatchController::class, 'show']);
        Route::patch('/match/results/{matchResult}/status', [\App\Http\Controllers\Api\MatchController::class, 'updateStatus']);

        // Audit logs
        Route::get('/audit-logs', [\App\Http\Controllers\Api\AuditLogController::class, 'index']);
    });
});