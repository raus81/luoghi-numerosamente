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
Route::get('/regione-{regione}/libero-consorzio-comunale-di-{provincia}',[\App\Http\Controllers\ComuniController::class,'provincia']);

Route::get('/regione-{regione}',[\App\Http\Controllers\ComuniController::class,'regione']);

Route::get('/regione-{regione}/comune-di-{comune}/cognomi',[\App\Http\Controllers\ComuniController::class,'cognomi']);

Route::get('/regione-{regione}/comune-di-{comune}/popolazione',[\App\Http\Controllers\ComuniController::class,'popolazione']);
Route::get('/regione-{regione}/comune-di-{comune}/distanze',[\App\Http\Controllers\ComuniController::class,'distanze']);
Route::get('/regione-{regione}/comune-di-{comune}/parrocchie',[\App\Http\Controllers\ComuniController::class,'parrocchie']);


Route::get('sitemap.xml', [\App\Http\Controllers\ComuniController::class,'sitemap'])->name('sitemap');
Route::get('sitemap_stats.xml', [\App\Http\Controllers\ComuniController::class,'sitemapStats'])->name('sitemap');



