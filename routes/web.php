<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/',[\App\Http\Controllers\ComuniController::class,'home']);

Route::get('/regione-{regione}/comune-di-{comune}',[\App\Http\Controllers\ComuniController::class,'comune']);

Route::get('/regione-{regione}/provincia-di-{provincia}',[\App\Http\Controllers\ComuniController::class,'provincia']);
Route::get('/regione-{regione}/citta-metropolitana-di-{provincia}',[\App\Http\Controllers\ComuniController::class,'provincia']);
Route::get('/regione-{regione}/provincia-di-{provincia}',[\App\Http\Controllers\ComuniController::class,'provincia']);

Route::get('/regione-{regione}',[\App\Http\Controllers\ComuniController::class,'regione']);



