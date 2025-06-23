<?php

use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/

Route::prefix('v1')->group(function () {


    Route::resource('/carreras', CarreraController::class)->only([
        'index',
        'show'
    ]);

    Route::resource('/asignaturas', AsignaturaController::class)->only([
        'index',
        'show',
    ]);

    Route::resource('/estudiante', EstudianteController::class)->only([
        'index',
        'show',
        'store',
        'update',
        'destroy'
    ]);

    Route::post('sanctum/token', [LoginController::class, 'getToken']);
});
