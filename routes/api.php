<?php

use Illuminate\Http\Request;


/**
 * login and register routes
 */
Route::post('/login', 'AuthController@postLogin')->name('login');
Route::post('/register', 'AuthController@postRegister')->name('register');

//get all cities routes
Route::get('/cities' ,['as' => 'cities' ,'uses' => 'CityController@index']);

/**
 * posts routes
 */
Route::get('/posts'  ,['as' => 'posts' ,'uses' => 'PostController@getIndex']);
/**
 * orders route
 */
Route::get('/single-order/{id}' ,['as' => 'order' , 'uses' => 'MainController@getSingleOrder']);

/**
 * categories route
 */
Route::get('/categories'  ,['as' => 'categories' ,'uses' => 'CategoryController@getIndex']);

/**
 * blogs routes
 */
Route::get('/blogs' ,['as' => 'blogs' ,'uses' => 'MainController@getBlog']);
Route::get('/comments/{post_id}' ,['as' => 'comments' ,'uses' => 'MainController@getComment']);

/**
 * black list routes
 */
Route::get('/black-list' ,['as' => 'list' ,'uses' => 'MainController@getBlackList']);
Route::get('/search' ,['as' => 'search' ,'uses' => 'MainController@getSearchBlacklist']);


Route::group(['middleware' => ['jwt.verify','cors']], function() {
    //get user data
    Route::get('/profile' ,['as' => 'profile' ,'uses' => 'ProfileController@getIndex']);
    Route::post('/profile' ,['as' => 'profile' , 'uses' => 'ProfileController@postIndex']);

    //test token
    Route::post('/test-token' ,['as' => 'test' ,'uses' => 'ProfileController@postTestToken']);


    //authenticated posts routes
    Route::post('/add' ,['as' => 'posts' ,'uses' => 'PostController@postIndex']);
    Route::post('/edit' ,['as' => 'posts.edit' ,'uses' => 'PostController@postEdit']);
    Route::post('/wishlist' ,['as' => 'wishlist' , 'uses' => 'PostController@postWishlist']);
    Route::post('/add-order' ,['as' => 'add.order' ,'uses' => 'PostController@postMakeOrder']);
    Route::post('/comment' ,['as' => 'comment' ,'uses' => 'MainController@postAddComment']);

    //change passsword
    Route::post('/reset-password','AuthController@postResetPassword')->name('reset');
    Route::post('/change-password','AuthController@postChangePassword')->name('change-password');
});
