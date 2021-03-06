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


Route::group([
    'namespace' => 'v1',
    'as' => 'v1.',
    'prefix' => 'v1'
] , function () {
    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('test' , function() {
        return 'salam';
    });
    Route::post('/register' , 'UserController@register')->name('register');
    Route::post('/login' , 'UserController@login')->name('login');
    Route::group(['middleware' => 'auth'] , function() {
        Route::post('/sendCode' , 'UserController@sendCode')->name('sendCode');
        Route::post('/checkCode' , 'UserController@checkCode')->name('checkCode');
    });
});
