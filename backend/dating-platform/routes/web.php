<?php
use Illuminate\Http\Request;


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


Route::get('/', 'IndexController@index')->name('index');

Auth::routes();

Route::get('/profile', 'ProfileController@index')->name('profile')->middleware('auth');

Route::post('/fast-register', 'AutoRegisterController@fastregister')->name('fast_register');

Route::get('/profile/{id}', 'ProfileController@show')->name('user_profile');

Route::get('/profile/{id}/albums', 'AlbumController@index')->name('albums');

Route::get('/profile/{id}/photos', 'AlbumController@photo_page')->name('photos');

Route::post('/friends/search', 'FriendsController@search')->name('friends_search')->middleware('auth');

Route::get('/profile-settings', 'ProfileController@edit')->name('profile_settings')->middleware('auth');

Route::get('/profile-status/{id}', 'ProfileController@status')->name('profile_status')->middleware('auth');

Route::get('/add-friend/{id}', 'FriendsController@create')->name('add_friend')->middleware('auth');

Route::get('/acc-friend/{id}', 'FriendsController@store')->name('acc_friend')->middleware('auth');

Route::get('/delete-friend/{id}', 'FriendsController@delete')->name('del_friend')->middleware('auth');

Route::get('/delete-request/{id}', 'FriendsController@delete_request')->name('del_friend')->middleware('auth');

Route::post('/profile-moto', 'ProfileController@moto')->name('profile_moto')->middleware('auth');

Route::get('/delete-profile-image/{id}', 'ProfileController@delete_profile_image')->name('delete_profile_image')->middleware('auth');

Route::post('/user-search', 'ProfileController@search')->name('user_search')->middleware('auth');

Route::post('/upload-image', 'ProfileController@image')->name('upload-image')->middleware('auth');

Route::post('/save-profile-info', 'ProfileController@update')->name('save-profile-info')->middleware('auth');

Route::post('/new-album', 'AlbumController@create')->name('new-album')->middleware('auth');

Route::post('/add-album-photo', 'AlbumController@store')->name('add-album-photo')->middleware('auth');

Route::post('/get-album', 'AlbumController@show')->name('get-album')->middleware('auth');

Route::post('/delete-album-photo', 'AlbumController@delete_photo')->name('delete-album-photo')->middleware('auth');

Route::post('/send-message', 'ChatController@send')->name('send-message');

Route::post('/load-messages', 'ChatController@load')->name('load-messages');

Route::post('/seen', 'ChatController@seen')->name('seen');

Route::post('/get-messages', 'ChatController@get')->name('send-message');

Route::post('/chat/ai-toggle', 'ChatController@toggleAi')->name('chat_ai_toggle')->middleware('auth');

Route::post('/chat/ai-video-session/start', 'ChatController@startAiVideoSession')->name('chat_ai_video_session_start')->middleware('auth');
Route::post('/chat/ai-video-session/end', 'ChatController@endAiVideoSession')->name('chat_ai_video_session_end')->middleware('auth');

Route::post('/pusher/auth', 'ChatController@auth')->name('pusher_auth');

Route::get('/logout', 'LogoutController@logout')->name('logout');

Route::get('/find-friends', 'FindFriendsController@index')->name('find_friends');

Route::get('/friends/{id}', 'FriendsController@show')->name('friends')->middleware('auth');

Route::post('/find-friends', 'FindFriendsController@search')->name('find_friends_search');

Route::post('/load-upload', 'ImageController@load_upload')->name('load_update')->middleware('auth');

Route::post('/attach-to-album', 'AlbumController@update')->name('attach_album')->middleware('auth');

Route::post('/delete-album', 'AlbumController@delete_album')->name('delete_album')->middleware('auth');

Route::get('/newsfeed', 'PostController@index')->name('newsfeed')->middleware('auth');

Route::post('/load-posts', 'PostController@show')->name('load_posts')->middleware('auth');

Route::post('/newsfeed', 'PostController@store')->name('text_post')->middleware('auth');

Route::post('/like', 'PostController@like')->name('like_post')->middleware('auth');

Route::post('/notification/seen', 'PostController@update')->name('seen_notification')->middleware('auth');

Route::get('/delete-post/{id}', 'PostController@delete_post')->name('delete_post')->middleware('auth');

Route::get('/packages', 'PackagesController@index')->name('packages')->middleware('auth');

Route::post('/onlineuser', 'ChatController@online')->name('useronline')->middleware('auth');

Route::get('/videochat', 'VideoController@index')->name('videochat')->middleware('auth');

Route::post('/videochat/call', 'VideoController@call')->name('video_call')->middleware('auth');

Route::post('/videochat/answer', 'VideoController@answer')->name('video_answer')->middleware('auth');

Route::post('/videochat/refuse', 'VideoController@refuse')->name('video_refuse')->middleware('auth');

Route::post('/videochat/credits', 'VideoController@credits')->name('videochat')->middleware('auth');

Route::post('/chat/load-right', 'ChatController@load_right')->name('chat_load_right')->middleware('auth');
Route::post('/chat/load-top', 'ChatController@load_top')->name('chat_load_top')->middleware('auth');

Route::post('/users/get-online', 'ProfileController@getOnline')->name('get_online_users');

Route::post('/get-guest-session', 'ChatController@getSessionGuest')->name('getSessionGuest');

Route::get('/onlineusers', 'FindFriendsController@onlineusers')->name('onlineusers');

Route::post('/get-find-friends-user/{id}', 'FindFriendsController@getFindFriendsUser')->name('getFindFriendsUser');
//ADMIN ROUTES

Route::get('/admin', 'AdminDashController@index')->name('admin_dash')->middleware('auth');
Route::get('/admin/editors', 'EditorsController@index')->name('admin_editors')->middleware('auth');
Route::get('/admin/users', 'WebUsersController@index')->name('admin_users')->middleware('auth');
Route::get('/admin/editors/{id}', 'EditorsController@show')->name('admin_editors')->middleware('auth');
Route::get('/admin/users/{id}', 'WebUsersController@show')->name('admin_user')->middleware('auth');
Route::post('/admin/search', 'EditorsController@search')->name('admin_search')->middleware('auth');
Route::post('/admin/users/update', 'WebUsersController@store')->name('admin_user_update')->middleware('auth');
Route::post('/admin/editors/update', 'EditorsController@store')->name('admin_editor_update')->middleware('auth');

Route::get('/admin/user-activity/action', 'UsersActivityController@search')->middleware('auth')->name('user-activity-search');
Route::get('/admin/users-activity', 'UsersActivityController@index')->name('admin_users_activity')->middleware('auth');
Route::post('/users/add-activity', 'UsersActivityController@store')->name('add_user_activity');

Route::get('/admin/packages', 'AdminPackagesController@index')->name('admin_packages')->middleware('auth');
Route::get('/admin/packages/{id}', 'AdminPackagesController@edit')->name('admin_packages_edit')->middleware('auth');
Route::post('/admin/packages/{id}', 'AdminPackagesController@update')->name('admin_packages_edit')->middleware('auth');
Route::get('/admin/delete/packages/{id}', 'AdminPackagesController@destroy')->name('admin_packages_delete')->middleware('auth');
Route::get('/translate', 'TranslateController@index')->name('translate')->middleware('auth');
Route::post('/translate/add', 'TranslateController@store')->name('translateadd')->middleware('auth');
Route::post('/translate/edit', 'TranslateController@edit')->name('translateedit')->middleware('auth');
Route::post('/translate/update', 'TranslateController@update')->name('translateupdate')->middleware('auth');
Route::get('/language/{id}', 'TranslateController@change_lang')->name('translatechange');
Route::get('/admin/cms', 'AdminCmsController@index')->name('admin_cms')->middleware('auth');
Route::get('/admin/cms/add', 'AdminCmsController@create')->name('admin_cms_add')->middleware('auth');
Route::post('/admin/cms/add', 'AdminCmsController@store')->name('admin_cms_add_store')->middleware('auth');
Route::get('/admin/cms/delete/{id}', 'AdminCmsController@destroy')->name('admin_cms_delete')->middleware('auth');
Route::get('/admin/cms/{id}', 'AdminCmsController@edit')->name('admin_cms_edit')->middleware('auth');
Route::get('/pages/{id}', 'AdminCmsController@show')->name('admin_cms_show');
Route::get('/admin/clients', 'AdminClientController@index')->name('admin_client_show')->middleware('auth');
Route::post('/admin/clients/remove-by-emails', 'AdminClientController@removeByEmails')->name('admin_client_remove_by_emails')->middleware('auth');
Route::get('/admin/clients/add', 'AdminClientController@create')->name('admin_client_add')->middleware('auth');
Route::get('/admin/clients/add/multiple', 'AdminClientController@create_multiple')->name('admin_client_add_multiple')->middleware('auth');
Route::post('/admin/clients/add', 'AdminClientController@add')->name('admin_client_add_post')->middleware('auth');
Route::post('/admin/clients/add/multiple', 'AdminClientController@add_multiple')->name('admin_client_add_post_multiple')->middleware('auth');
Route::get('/admin/client/edit/{id}', 'AdminClientController@show')->name('admin_client_edit')->middleware('auth');
Route::get('/admin/delete/client/{id}', 'AdminClientController@destroy')->name('admin_client_delete')->middleware('auth');
Route::post('/admin/client/edit/{id}', 'AdminClientController@edit')->name('admin_client_edit_post')->middleware('auth');
Route::get('/admin/settings', 'SettingsController@index')->name('admin_settings')->middleware('auth');
Route::post('/admin/settings/save', 'SettingsController@store')->name('admin_settings_post')->middleware('auth');

Route::get('/admin/newsletter/{id}', 'AdminNewsletterController@index')->name('admin_newsletter')->middleware('auth');
Route::post('/admin/newsletter/{id}', 'AdminNewsletterController@index')->name('admin_newsletter_ajax')->middleware('auth');
Route::post('/admin/send/newsletter', 'AdminNewsletterController@create')->name('admin_newsletter_send')->middleware('auth');


Route::get('/admin/orders', 'OrdersController@index')->name('orders')->middleware('auth');

Route::get('/admin/user/delete/{id}', 'WebUsersController@delete')->name('admin_user_delete')->middleware('auth');

Route::get('/admin/ai-profiles', 'AdminAIProfileController@index')->name('admin_ai_profiles_index')->middleware(['auth', 'can:manage,App\\AIProfile']);
Route::post('/admin/ai-profiles', 'AdminAIProfileController@store')->name('admin_ai_profiles_store')->middleware(['auth', 'can:manage,App\\AIProfile']);
Route::get('/admin/ai-profiles/{aiProfile}', 'AdminAIProfileController@show')->name('admin_ai_profiles_show')->middleware(['auth', 'can:manage,App\\AIProfile']);
Route::put('/admin/ai-profiles/{aiProfile}', 'AdminAIProfileController@update')->name('admin_ai_profiles_update')->middleware(['auth', 'can:manage,App\\AIProfile']);
Route::delete('/admin/ai-profiles/{aiProfile}', 'AdminAIProfileController@destroy')->name('admin_ai_profiles_destroy')->middleware(['auth', 'can:manage,App\\AIProfile']);
Route::post('/admin/ai-profiles/{aiProfile}/distill-style', 'AdminAIProfileController@distillStyle')->name('admin_ai_profiles_distill_style')->middleware(['auth', 'can:manage,App\\AIProfile']);

Route::get('/admin/ai-settings', 'AdminAISettingController@index')->name('admin_ai_settings_index')->middleware(['auth', 'can:manage,App\\AIProfile']);
Route::post('/admin/ai-settings', 'AdminAISettingController@update')->name('admin_ai_settings_update')->middleware(['auth', 'can:manage,App\\AIProfile']);

Route::get('/admin/themes', 'AdminThemeController@index')->name('admin_themes_index')->middleware('auth');
Route::post('/admin/themes', 'AdminThemeController@update')->name('admin_themes_update')->middleware('auth');

//Payments
Route::any('/payments', 'PaymentsController@newpayment')->name('new_payment')->middleware('auth');
Route::get('/payments/accepted', 'PaymentsController@accepted')->name('accepted_payment');
Route::get('/payments/denied', 'PaymentsController@accepted')->name('accepted_payment');
Route::post('/payments/accepted', 'PaymentsController@accepted')->name('accepted_payment');
Route::post('/payments/denied', 'PaymentsController@declined')->name('declined_payment');
Route::get('/payments/unsubscribe', 'PaymentsController@unsubscribe')->name('payment_unsubscribe');
Route::any('/payments/confirm-payment', 'PaymentsController@confirmPayment')->name('confirm_payment');
Route::any('/payments/free', 'PaymentsController@freePackage')->name('free_package')->middleware('auth');
Route::any('/payments/create-subscription', 'PaymentsController@create_subscription')->name('create-subscription');
Route::any('/payments/finalize-subscription-payment', 'PaymentsController@finalizeSubscriptionPayment')->name('finalize-subscription-payment');
Route::any('/payments/create-custom-pack', 'PaymentsController@createCustomPack')->name('create-custom-pack');
Route::post('/payments/voucher', 'PaymentsController@voucher')->name('voucher');

//Chat Online

Route::post('/chat/status', 'ChatController@status')->name('chat_status');

//block

Route::get('/block/{id}', 'BlockController@block')->name('block')->middleware('auth');
Route::get('/unblock/{id}', 'BlockController@unblock')->name('block')->middleware('auth');
Route::get('/blocked', 'BlockController@index')->name('block')->middleware('auth');

Route::get('/autoregister/verify/{email}/{token}', 'AutoRegisterController@verify')->name('autoregister_verify');
Route::get('/autoregister/{req_token}/{email}/{target}/{code}', 'AutoRegisterController@index')->name('autoregister_tracking');
Route::get('/autoregister/{req_token}/{email}/{target}', 'AutoRegisterController@index')->name('autoregister');

Route::post('/get-discount', 'DiscountController@create')->name('get_discount')->middleware('auth');

Route::post('/get-livefeed', 'LiveFeedController@index')->name('get_livefeed');

Route::post('/friend-request-live', 'FriendsController@live_request')->name('friends_request_live')->middleware('auth');

Route::get('/ai/chat-room', 'AIChatController@room')->name('ai_chat_room')->middleware('auth');
Route::post('/ai/chat', 'AIChatController@send')->name('ai_chat_send')->middleware(['auth', 'free.messages.limit']);
Route::get('/ai/chat/history', 'AIChatController@history')->name('ai_chat_history')->middleware('auth');
Route::post('/ai/video-status', 'AIChatController@videoStatus')->name('ai_chat_video_status')->middleware('auth');
Route::post('/ai/video-session/start', 'AIChatController@startVideoSession')->name('ai_video_session_start')->middleware('auth');
Route::post('/ai/video-session/end', 'AIChatController@endVideoSession')->name('ai_video_session_end')->middleware('auth');

//Discounts
Route::get('/admin/discounts', 'DiscountController@index')->name('admin_discount')->middleware('auth');
Route::post('/admin/discounts/add', 'DiscountController@store')->name('admin_discount_add')->middleware('auth');
Route::get('/admin/discounts/delete/{id}', 'DiscountController@destroy')->name('admin_discount_delete')->middleware('auth');

//CHATBOT
Route::get('/chatbot/train', 'ChatBotController@train')->name('chatbot_train');
Route::post('/chatbot/response', 'ChatBotController@response')->name('chatbot_response');
Route::post('/chatbot/auto-message', 'ChatBotController@auto_message')->name('chatbot_automessage');

//UNSUBSCRIBE NEWSLETTER
Route::get('/unsubscribe/{email}', 'UnsubscribeController@index')->name('unsubscribe_newsletter');

//EMAIL TRACKING
Route::get('/admin/emailtracking', 'EmailTrackingController@index')->name('email_tracking');
Route::get('/admin/emailtracking/{id}', 'EmailTrackingController@show')->name('email_tracking_show');
Route::get('/emailtracking/track/{code}', 'EmailTrackingController@track')->name('email_tracking_track');

//REFERRALS
Route::get('/referrals/claim/{id}', 'ReferralsController@edit');
Route::post('/referrals/create', 'ReferralsController@create')->name('referrals')->middleware('auth');
Route::get('/referrals/{username}/{token}', 'ReferralsController@store');

//LANDING PAGE
Route::get('/landing', 'LandingController@index')->middleware('guest');
Route::get('/game', 'LandingController@game')->middleware('guest');

//COMPLETE REGISTRATION
Route::post('/complete/autoregister', 'AutoRegisterController@complete')->name('complete_autoregister');
Route::post('/complete/autoregister-fake/', 'AutoRegisterController@complete_fake')->name('complete_autoregister_fake');
Route::post('/register-fake', 'LandingController@get_account')->name('fakeregister_get_account');
Route::get('/autoregister-fake/{id}', 'AutoRegisterController@fakeregister')->name('fakeregister');

//CHANGE USER LANGUAGE
Route::post('/change-lang/{id}', 'TranslateController@change_user_lang')->name('change_user_lang');

Route::post('packages/order-attempts/store', 'PackagesController@orderAttemptsStore')->name('packages.order.attemts.store');
Route::get('/admin/order-attempts', 'OrdersController@orderAttempts')->name('order.attempt')->middleware('auth');
Route::get('/admin/order-attempts/delete/{id}', 'OrdersController@deleteOrderAttempts')->name('order.attempt.delete')->middleware('auth');
Route::post('/admin/order-attempts/delete', 'OrdersController@deleteOlderOrderAttempts')->name('order.attempt.delete.older')->middleware('auth');

//ROULETTE
Route::get('/roulette', 'RouletteController@index')->name('roulette');
Route::get('/roulette/random_value', 'RouletteController@random_value')->name('roulette_random_value');
Route::get('/admin/roulette/values', 'RouletteController@values')->name('admin_roulette_values')->middleware('auth');
Route::post('/admin/roulette/values/add', 'RouletteController@add_value')->name('admin_roulette_add_value')->middleware('auth');
Route::get('/admin/roulette/values/delete/{id}', 'RouletteController@delete_value')->name('admin_roulette_delete_value')->middleware('auth');

//CLEAR CACHE
Route::get('/admin/clear-cache', function(){
    if(auth()->user()->isAdmin()){
        \Artisan::call('cache:clear');
        \Artisan::call('config:clear');
    }
})->middleware('auth');

Route::any('/webhooks/{provider}/{path}', 'PaymentsController@webhook')->name('payment.webhooks');
