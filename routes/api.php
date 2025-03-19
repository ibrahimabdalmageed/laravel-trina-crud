<?php

use Illuminate\Support\Facades\Route;
use Trinavo\TrinaCrud\Http\Controllers\TrinaCrudController;
use Trinavo\TrinaCrud\Http\Controllers\TrinaCrudModelController;

Route::prefix('trina-crud')->group(function () {
    Route::get('/sync-models', [TrinaCrudController::class, 'syncModels']);
    Route::get('/get-schema', [TrinaCrudController::class, 'getSchema']);
    Route::get('/model/{model}', [TrinaCrudModelController::class, 'index']);
});
