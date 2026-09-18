<?php

use App\Http\Controllers\Api\V1\CandidacyController;
use App\Http\Controllers\Api\V1\ElectionController;
use App\Http\Controllers\Api\V1\ElectoralUnitController;
use App\Http\Controllers\Api\V1\OfficeController;
use App\Http\Controllers\Api\V1\PartyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/elections', [ElectionController::class, 'index']);
    Route::get('/offices', [OfficeController::class, 'index']);
    Route::get('/parties', [PartyController::class, 'index']);
    Route::get('/electoral-units', [ElectoralUnitController::class, 'index']);
    Route::get('/candidacies', [CandidacyController::class, 'index']);
    Route::get('/candidacies/{id}', [CandidacyController::class, 'show']);
});
