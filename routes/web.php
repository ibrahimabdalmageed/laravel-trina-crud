<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

Route::middleware(config('trina-crud.admin_middleware'))->prefix(config('trina-crud.admin_route_prefix'))->group(function () {
    Route::get('/permissions', function () {
        return view('trina-crud::permissions');
    })->name('trina-crud.permissions');
});
