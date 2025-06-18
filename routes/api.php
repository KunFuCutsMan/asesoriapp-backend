<?php

use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\CarreraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/

Route::resource('v1/carreras', CarreraController::class)->only([
    'index',
    'show'
]);

Route::resource('v1/asignaturas', AsignaturaController::class)->only([
    'index',
    'show',
]);
