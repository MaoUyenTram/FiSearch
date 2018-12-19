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

Route::post('/upload', 'UploadController')->middleware('api', 'cors');
Route::post('/massupload', 'MassController')->middleware('api', 'cors');
Route::post('/confirm', 'WorkController@store')->middleware('api', 'cors');

Route::get('/departments', 'DepartmentsController')->middleware('api','cors');


Route::resource('works', 'WorkController');
Route::get('works/by_title/{title}', 'WorkController@showByTitle')->middleware('api', 'cors');
Route::get('works/by_departement/{departement}', 'WorkController@showByDepartement')->middleware('api','cors');
Route::get('/pdftoimage', 'PdfToImageController')->middleware('api','cors');

Route::get('/search', 'WorkController@search')->middleware('api','cors');
Route::resource('ratings', 'RatingController')->middleware('api','cors');

Route::get('/download/{name}',function($name){
    return Response::download(public_path('pdf/').$name);
});


// Code from Laravel Passport Tuturial: https://medium.com/modulr/create-api-authentication-with-passport-of-laravel-5-6-1dc2d400a7f
Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');

    Route::group([
        'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');
    });
});