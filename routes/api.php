<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/signup', 'UserController@register');
Route::post('/login', 'UserController@login');
Route::get('/user', 'UserController@getCurrentUser');
Route::post('/update', 'UserController@update');
Route::get('/logout', 'UserController@logout');


Route::get('gen-pass', function(){
    return bcrypt('12345678');
});


Route::fallback(function(){
    return response()->json([
        'message' => 'Page Not Found. If error persists, contact info@tyreman.com'], 404);
});