<?php

use App\Http\Controllers\GanttController;
use App\Http\Controllers\PlanoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return view('welcome');
});

Route::get('/empty-json', function () {
    return response()->json([]);
});

Route::get('/test',  [TestController::class, 'index']);
Route::get('/test1',  [TestController::class, 'tabla1']);
Route::get('/test2',  [TestController::class, 'tabla2']);
Route::resource('/planos',  PlanoController::class);

// Rutas para la carta Gantt
Route::resource('/compromops', GanttController::class);
Route::post('/compromops/{id}', [GanttController::class, 'ajaxUpdate'])->name('compromops.ajax.update');

// Agrega esta ruta específica para las actualizaciones AJAX
Route::post('/compromops/{id}/ajax-update', [GanttController::class, 'ajaxUpdate'])
    ->name('compromops.ajax.update');
