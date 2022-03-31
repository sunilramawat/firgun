<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('category_list','ApiController@category_list');
Route::get('subcategory_list','ApiController@subcategory_list');

Route::post('guestLogin','ApiController@guestLogin');
Route::post('register','ApiController@register');
Route::post('login','ApiController@login');
Route::post('verifyUser','ApiController@verifyUser');

//Route::post('socialLogin','ApiController@socialLogin');

Route::get('profile','ApiController@profile')->middleware('auth:api');
Route::post('update_profile','ApiController@profile')->middleware('auth:api');
Route::post('setting','ApiController@setting')->middleware('auth:api');
Route::get('favourite_list','ApiController@favourite_list')->middleware('auth:api');

Route::post('update_device','ApiController@update_device')->middleware('auth:api');

Route::get('check_username','ApiController@check_username')->middleware('auth:api');  
Route::post('changePassword','ApiController@changePassword')->middleware('auth:api');
Route::post('forgotPassword','ApiController@forgotPassword');
Route::post('resetPassword','ApiController@resetPassword');

Route::post('create_post','ApiController@createPost')->middleware('auth:api');
Route::post('repost','ApiController@repost')->middleware('auth:api');
Route::get('adds_list','ApiController@adds_list')->middleware('auth:api');
Route::get('deal_detail','ApiController@deal_detail')->middleware('auth:api');
Route::delete('delete_post','ApiController@deletePost')->middleware('auth:api');
Route::get('post_detail','ApiController@post_detail')->middleware('auth:api');

Route::post('comment','ApiController@commentPost')->middleware('auth:api');


Route::get('logout','ApiController@logout')->middleware('auth:api');
Route::get('stock_list','ApiController@stock_list');
Route::get('current_price','ApiController@current_price');
Route::get('tranding','ApiController@tranding');

Route::post('like','ApiController@like')->middleware('auth:api');
Route::post('follow','ApiController@follow')->middleware('auth:api');
Route::post('comment_like','ApiController@comment_like')->middleware('auth:api');
Route::post('vote','ApiController@vote')->middleware('auth:api');
Route::post('favourite','ApiController@favourite')->middleware('auth:api');
Route::get('watch_list','ApiController@watch_list')->middleware('auth:api');
Route::post('gainer','ApiController@gainer');
Route::post('tricker','ApiController@tricker');




Route::post('setpreferences','ApiController@setpreferences')->middleware('auth:api');
Route::get('setpreferences','ApiController@setpreferences')->middleware('auth:api');

Route::get('gallery','ApiController@gallery')->middleware('auth:api');

Route::delete('gallery','ApiController@gallery')->middleware('auth:api');

Route::post('make_default','ApiController@make_default')->middleware('auth:api');

Route::post('visibility','ApiController@visibility')->middleware('auth:api');

Route::get('match','ApiController@match')->middleware('auth:api');
Route::delete('match','ApiController@match')->middleware('auth:api');

Route::get('pending_match','ApiController@pending_match')->middleware('auth:api');

Route::post('report','ApiController@report')->middleware('auth:api');

Route::get('user_detail','ApiController@user_detail')->middleware('auth:api');

Route::delete('deleteAccount','ApiController@deleteAccount')->middleware('auth:api');

Route::get('notificationList','ApiController@notificationList')->middleware('auth:api');



Route::get('userNotify','ApiController@userNotify');
Route::get('recommend_list','ApiController@recommend_list')->middleware('auth:api');


Route::get('subscriptionsList','ApiController@subscriptionsList');
Route::post('pendingSubscriptionPlan','ApiController@pendingSubscriptionPlan')->middleware('auth:api');
Route::get('cronJobForaddList','ApiController@cronJobForaddList');
Route::get('cronJobForaddClose','ApiController@cronJobForaddClose');
Route::get('cronJobForSubscreption','ApiController@cronJobForSubscreption');

Route::post('androidSubscreption','ApiController@androidSubscreption')->middleware('auth:api');


// twilio
Route::post('chat_user', "ApiController@chat_user")->middleware('auth:api');
Route::get('chat_token','ApiController@chat_token')->middleware('auth:api');
Route::post('chat_post_event','ApiController@chat_post_event');
Route::post('chat_pre_event','ApiController@chat_pre_event');
Route::get('chat_update_uername','ApiController@chat_update_uername');
Route::post('addchatuser','ApiController@addchatuser');
Route::post('contact','ApiController@contact');

Route::get('check_pending','ApiController@check_pending');
Route::get('update_previous','ApiController@update_previous');

Route::get('notification_match_detail','ApiController@notification_match_detail')->middleware('auth:api');
// Question Answer
Route::get('question','ApiController@question')->middleware('auth:api');
Route::post('answer','ApiController@answer')->middleware('auth:api');
Route::delete('answer_delete','ApiController@answer_delete')->middleware('auth:api');

Route::post('chip','ApiController@chip')->middleware('auth:api');
Route::get('chip_list','ApiController@chip_list')->middleware('auth:api');
Route::get('chip_data_list','ApiController@chip_data_list')->middleware('auth:api');


/*
Route::middleware('auth')->group(function () {
    Route::get('profile', [App\Http\Controllers\ApiController::class, 'profile'])->name('profile');
    });*/