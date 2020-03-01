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

// user routrs remember to append auth to beginnings

Route::group([
    'prefix'=>'auth'
], function (){
    Route::post('signup', 'AuthController@signUp');
    Route::post('login', 'AuthController@login');
    Route::get('signup/activate/{token}', 'AuthController@signupactivate');
    Route::get('{id}/getposts', 'AuthController@findUser');
    Route::get('/getquestions', 'UserController@showAll');
    Route::get('{q_id}/getanswers', 'UserController@getQuestion');
    Route::get('{term}/search', 'UserController@search');
    Route::get('{category}/category', 'UserController@category');
    Route::get('{id}/userdetails', 'UserController@profile');

    Route::group([
        'middleware'=>'auth:api'
    ], function () {
        Route::post('/postquestion', 'UserController@create');
        Route::post('/postanswer', 'UserController@createAnswer');
        Route::put('{id}/upvote', 'UserController@edit');
        Route::put('{id}/downvote', 'UserController@downVote');
    });
});




// test route
Route::get('test/{id}', 'AuthController@findUser');