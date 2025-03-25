<?php

use Illuminate\Support\Facades\Route;
use Trinavo\TrinaCrud\Http\Controllers\TrinaCrudController;
use Trinavo\TrinaCrud\Http\Controllers\TrinaCrudModelController;
use Trinavo\TrinaCrud\Http\Controllers\OpenApiController;



// OpenAPI documentation
Route::prefix(config('trina-crud.route_prefix'))
    ->name('trina-crud.')
    ->middleware(config('trina-crud.middleware', []))
    ->group(function () {
        Route::get('/openapi.json', [OpenApiController::class, 'json'])->name('openapi.json');
        Route::get('/openapi.yaml', [OpenApiController::class, 'yaml'])->name('openapi.yaml');
    });


// Regular API routes
Route::prefix(config('trina-crud.route_prefix'))
    ->middleware(config('trina-crud.middleware', []))
    ->group(function () {
        Route::get('/get-schema', [TrinaCrudController::class, 'getSchema']);
        Route::get('/{model}/get-schema', [TrinaCrudController::class, 'getSchema']);
        Route::get('/{model}', [TrinaCrudModelController::class, 'index']);
        Route::get('/{model}/{id}', [TrinaCrudModelController::class, 'show']);
        Route::post('/{model}', [TrinaCrudModelController::class, 'store']);
        Route::put('/{model}/{id}', [TrinaCrudModelController::class, 'update']);
        Route::delete('/{model}/{id}', [TrinaCrudModelController::class, 'destroy']);
    });
