<?php

use Illuminate\Http\Request;

Route::group([
  'prefix' => 'auth'
], function () {
  Route::post('signin', 'AuthController@signin');
  Route::post('signup', 'AuthController@signup');
  //Route::get('signup/activate/{token}', 'AuthController@signupActivate');

  Route::group([
    'middleware' => 'auth:api',
  ], function () {
    Route::get('logout', 'AuthController@logout');
    Route::get('user', 'AuthController@user');
  });
});