<?php

use Illuminate\Support\Facades\Route;
use Trinavo\TrinaCrud\Http\Controllers\TrinaCrudController;
use Trinavo\TrinaCrud\Http\Controllers\TrinaCrudModelController;

// Admin routes
Route::prefix(config('trina-crud.route_prefix', 'trina-crud') . '/admin')
    ->middleware(config('trina-crud.admin_middleware', ['trina-crud.admin']))
    ->group(function () {
        Route::get('/sync-models', [TrinaCrudController::class, 'syncModels']);
    });

// Regular API routes
Route::prefix(config('trina-crud.route_prefix', 'trina-crud'))
    ->middleware(config('trina-crud.middleware', []))
    ->group(function () {
        Route::get('/get-schema', [TrinaCrudController::class, 'getSchema']);
        Route::get('/model/{model}', [TrinaCrudModelController::class, 'index']);
        Route::get('/model/{model}/{id}', [TrinaCrudModelController::class, 'show']);
        Route::post('/model/{model}', [TrinaCrudModelController::class, 'store']);
        Route::put('/model/{model}/{id}', [TrinaCrudModelController::class, 'update']);
        Route::delete('/model/{model}/{id}', [TrinaCrudModelController::class, 'destroy']);
    });
