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

Route::resource('/compromops', GanttController::class);

Route::post('/compromops/{id}/ajax-update', [App\Http\Controllers\GanttController::class, 'ajaxUpdate'])->name('compromops.ajax-update');
Route::post('/compromops-history/{id}/confirm', [App\Http\Controllers\GanttController::class, 'confirmChange'])->name('compromops.history.confirm');
Route::post('/compromops-comment', [App\Http\Controllers\GanttController::class, 'addComment'])->name('compromops.comment.add');
Route::post('/compromops/{id}/comment', [App\Http\Controllers\GanttController::class, 'saveComment'])->name('compromops.comment');
