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

//Upload a PDF file for Analysis
Route::post('/upload', 'UploadController');
Route::post('/massupload', 'MassController');
Route::post('/confirm', 'WorkController@store');

//Download the PDF file
Route::get('/download/{name}',function($name){
    return Response::download(public_path('pdf/').$name);
});

//Get the EhB departments
Route::get('/departments', 'DepartmentsController');

//Search final works and get ratings
Route::get('/search', 'WorkController@search');
// Authenticatie nodig omdat de user_id wordt gebruikt om een entry te maken in de ratings table.
Route::resource('ratings', 'RatingController')->middleware('auth:api');
Route::get('ratingsAvg/{id}', 'RatingController@showAverage');

// Code from Laravel Passport Tuturial: https://medium.com/modulr/create-api-authentication-with-passport-of-laravel-5-6-1dc2d400a7f
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', 'AuthController@login');
    Route::group([
        'middleware' => 'auth:api'], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});

Route::get('/cdn/{image}', function($image) {
    //dd(public_path('./pdf/' . $image));

    return response()->file(public_path('./pdf/' . $image));
});
