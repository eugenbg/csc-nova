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

Route::get('/', 'App\Http\Controllers\HomeController@home');
Route::post('/subscribe', 'App\Http\Controllers\SubscribeController@subscribe');
Route::post('/search', 'App\Http\Controllers\SearchController@search');
Route::get('/search', 'App\Http\Controllers\SearchController@search');
Route::get('/test', 'App\Http\Controllers\HomeController@ft');
Route::get('/choose/{id}', 'App\Http\Controllers\HomeController@choose');

Route::fallback(['as' => 'slug', 'uses' => 'App\Http\Controllers\Router@routeMatch']);
