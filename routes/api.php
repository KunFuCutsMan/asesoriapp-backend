<?php

use App\Http\Controllers\AsesoriaController;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordController;
use App\Models\Estudiante;
use Illuminate\Support\Facades\Route;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/

Route::prefix('v1')->group(function () {


    Route::apiResource('/carreras', CarreraController::class)->only([
        'index',
        'show'
    ]);

    Route::apiResource('/asignaturas', AsignaturaController::class)->only([
        'index',
        'show',
    ]);

    Route::get('/estudiante/by-token/', [EstudianteController::class, 'showByToken'])
        ->middleware('auth:sanctum');
    Route::apiResource('/estudiante', EstudianteController::class)
        ->middlewareFor(
            ['index', 'show', 'update', 'destroy'],
            'auth:sanctum'
        );

    Route::apiResource('/asesoria', AsesoriaController::class)
        ->middleware('auth:sanctum');

    Route::post('sanctum/token', [LoginController::class, 'getToken']);

    Route::post('/password', [PasswordController::class, 'sendPasswordMessage']);
});
