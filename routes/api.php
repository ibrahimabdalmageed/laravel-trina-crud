<?php

use Illuminate\Support\Facades\Route;
use Trinavo\TrinaCrud\Http\Controllers\TrinaCrudController;
use Trinavo\TrinaCrud\Http\Controllers\TrinaCrudModelController;

Route::prefix('trina-crud')->group(function () {
    Route::get('/sync-models', [TrinaCrudController::class, 'syncModels']);
    Route::get('/get-schema', [TrinaCrudController::class, 'getSchema']);
    Route::get('/model/{model}', [TrinaCrudModelController::class, 'index']);
    Route::get('/model/{model}/{id}', [TrinaCrudModelController::class, 'show']);
    Route::post('/model/{model}', [TrinaCrudModelController::class, 'store']);
    Route::put('/model/{model}/{id}', [TrinaCrudModelController::class, 'update']);
    Route::delete('/model/{model}/{id}', [TrinaCrudModelController::class, 'destroy']);
});
