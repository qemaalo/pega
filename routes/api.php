<?php

use App\Http\Controllers\FrappeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Ruta para obtener todas las tareas
Route::get('/compromops', [App\Http\Controllers\FrappeController::class, 'getTasks']);

// Ruta para actualizar fechas de una tarea específica
Route::post('/compromops/{id}/update-dates', [App\Http\Controllers\FrappeController::class, 'updateDates']);