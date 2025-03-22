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

Route::middleware(config('trina-crud.middleware'))->group(function () {
    Route::get('/trina-crud/permissions', function () {
        return view('trina-crud::permissions');
    })->name('trina-crud.permissions');
});
