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


Route::get('/search', function () {
    return 'Zoekalgoritme komt hier';
});

Route::post('/upload', 'UploadController')->middleware('api', 'cors');

Route::get('/departments', 'DepartmentsController')->middleware('api','cors');


Route::resource('works', 'WorkController');
Route::get('works/by_title/{title}', 'WorkController@showByTitle')->middleware('api', 'cors');
Route::get('works/by_departement/{departement}', 'WorkController@showByDepartement');
Route::get('/pdftoimage', 'PdfToImageController');

Route::get('/filter', 'WorkController@filter');
Route::resource('ratings', 'RatingController');