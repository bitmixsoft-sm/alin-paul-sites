<?php

/**
 * Self-contained route file for the Email Outreach feature (client's request, 2026-10-20) -
 * required from routes/web.php with a single `require app_path('EmailOutreach/routes.php');`
 * line, so adding this feature to another site only ever needs that one line added there too.
 */

Route::get('/admin/email-outreach', '\App\EmailOutreach\Http\AdminEmailOutreachController@index')->name('admin_email_outreach_index');
Route::post('/admin/email-outreach/accounts', '\App\EmailOutreach\Http\AdminEmailOutreachController@storeAccount')->name('admin_email_outreach_store_account');
Route::get('/admin/email-outreach/accounts/{id}/edit', '\App\EmailOutreach\Http\AdminEmailOutreachController@editAccount')->name('admin_email_outreach_edit_account');
Route::post('/admin/email-outreach/accounts/{id}', '\App\EmailOutreach\Http\AdminEmailOutreachController@updateAccount')->name('admin_email_outreach_update_account');
Route::post('/admin/email-outreach/campaigns', '\App\EmailOutreach\Http\AdminEmailOutreachController@storeCampaign')->name('admin_email_outreach_store_campaign');
Route::get('/admin/email-outreach/campaigns/{id}', '\App\EmailOutreach\Http\AdminEmailOutreachController@showCampaign')->name('admin_email_outreach_show_campaign');
Route::post('/admin/email-outreach/campaigns/{id}/run-now', '\App\EmailOutreach\Http\AdminEmailOutreachController@runCampaignNow')->name('admin_email_outreach_run_now');
Route::post('/admin/email-outreach/campaigns/{id}/messages', '\App\EmailOutreach\Http\AdminEmailOutreachController@storeMessage')->name('admin_email_outreach_store_message');
Route::post('/admin/email-outreach/campaigns/{id}/recipients', '\App\EmailOutreach\Http\AdminEmailOutreachController@importRecipients')->name('admin_email_outreach_import_recipients');
Route::post('/admin/email-outreach/recipients/{id}/take-over', '\App\EmailOutreach\Http\AdminEmailOutreachController@takeOver')->name('admin_email_outreach_take_over');
Route::post('/admin/email-outreach/recipients/{id}/continue', '\App\EmailOutreach\Http\AdminEmailOutreachController@continueAutomation')->name('admin_email_outreach_continue');
Route::post('/admin/email-outreach/recipients/{id}/reply', '\App\EmailOutreach\Http\AdminEmailOutreachController@manualReply')->name('admin_email_outreach_manual_reply');
Route::post('/admin/email-outreach/recipients/{id}/delete', '\App\EmailOutreach\Http\AdminEmailOutreachController@deleteRecipient')->name('admin_email_outreach_delete_recipient');

// Public - loaded by the recipient's email client, not an admin. No auth middleware.
Route::get('/email-outreach/track/{token}.gif', '\App\EmailOutreach\Http\EmailOutreachTrackingController@trackOpen')->name('email_outreach_track');
