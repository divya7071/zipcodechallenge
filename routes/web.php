<?php

use App\Events\ActivityFetchCompleted;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;




Auth::routes();
Route::get('/importZips', [App\Http\Controllers\AthleteController::class, 'importZips']) ->name('importZips');
Route::get('/my-data', [App\Http\Controllers\AthleteController::class, 'index']) ->name('athlete.mydata');
Route::get('/login/strava', [App\Http\Controllers\AthleteAuthController::class, 'login'])->name('athlete.strava.login');
Route::get('/auth/strava/callback', [App\Http\Controllers\AthleteAuthController::class, 'callback'])->name('athlete.strava.connect');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('contact-us', [App\Http\Controllers\HomeController::class, 'contact'])->name('contact');
Route::get('privacy-policy', [App\Http\Controllers\HomeController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('terms-and-conditions', [App\Http\Controllers\HomeController::class, 'termsAndConditions'])->name('terms-and-conditions');
Route::get('legal', [App\Http\Controllers\HomeController::class, 'legal'])->name('legal');
Route::post('contact-submit', [App\Http\Controllers\HomeController::class, 'contact_submit'])->name('contact-submit');
Route::get('how-it-works', [App\Http\Controllers\HomeController::class, 'howItWork'])->name('how-it-works');
Route::post('invitation', [App\Http\Controllers\ContactController::class, 'store'])->name('invitation');
Route::get('locate', [App\Http\Controllers\ContactController::class, 'locate'])->name('locate');
// Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'dashboard'])->name('dashboard');

Route::middleware(['auth:athlete'])->prefix('account') ->name('account.')->group(function () {

    Route::get('/overview', [App\Http\Controllers\AthleteController::class, 'overview'])->name('overview');
    Route::get('/dashboard', [App\Http\Controllers\AthleteController::class, 'overview'])->name('dashboard');
    Route::get('/athletes/{athlete}', [App\Http\Controllers\AthleteController::class, 'detail'])->name('athletes.show');
    Route::get('/remove-account', [App\Http\Controllers\AthleteController::class, 'removeAccount'])->name('remove-account');
    Route::post('/delete-account', [App\Http\Controllers\AthleteController::class, 'deleteAccount'])->name('delete-account');
    Route::post('/pause-account', [App\Http\Controllers\AthleteController::class, 'pauseAccount'])->name('pause-account');
    Route::post('/athlete/logout', [App\Http\Controllers\AthleteAuthController::class, 'logout'])->name('athlete.logout');
    Route::get('/activities', [App\Http\Controllers\AthleteActivityController::class, 'index'])->name('activity.index');
    Route::get('/activities/polylines', [App\Http\Controllers\AthleteActivityController::class, 'getActivityPolylines'])->name('activity.polylines');
    Route::post('/activity/map', [App\Http\Controllers\AthleteActivityController::class, 'mapView'])->name('activity.map');
    Route::post('/syncActivity', [App\Http\Controllers\AthleteActivityController::class, 'syncActivity'])->name('activity.sync');
    Route::get('/activity/sync-status', [App\Http\Controllers\AthleteActivityController::class, 'syncStatus'])->name('activity.sync-status');
    Route::get('/activities/{activity}', [App\Http\Controllers\AthleteActivityController::class, 'show'])->name('activity.show');
    Route::post('/activities/{activity}/passed-zips', [App\Http\Controllers\AthleteActivityController::class, 'savePassedZips'])->name('activity.savePassedZips');
    Route::post('/activities/save-map', [App\Http\Controllers\AthleteActivityController::class,'storeMap'])->name('activities.save-map');
    Route::get('/load-activities', [App\Http\Controllers\AthleteActivityController::class, 'loadActivityMore'])->name('activities.load');
    Route::get('/segment-details/{id}', [App\Http\Controllers\AthleteActivityController::class, 'segmantDetails']);
    Route::get('/activity-media/{id}', [App\Http\Controllers\AthleteActivityController::class, 'media']);
    Route::get('/activity-log', [App\Http\Controllers\AthleteActivityController::class, 'activity_log']);
    Route::get('/activity-log-more', [App\Http\Controllers\AthleteActivityController::class, 'activity_log_more'])->name('activities.log-more');
    Route::post('/zipcodes/map-bbox', [App\Http\Controllers\AthleteActivityController::class, 'mapBbox']);
    Route::get('/my-achievements', [App\Http\Controllers\SegmentController::class, 'index'])->name('segment.index');
    Route::get('/leaderboard', [App\Http\Controllers\SegmentController::class, 'leaderboard'])->name('leaderboard');
    Route::get('/zipcode/{id}', [App\Http\Controllers\SegmentController::class, 'detail'])->name('segments.leaderboard');
    Route::get('/zip/{zipcode}/leaderboard', [App\Http\Controllers\SegmentController::class, 'zip_leaderboard'])->name('zip.leaderboard');
    Route::get('/explore-map', [App\Http\Controllers\SegmentController::class, 'exploreMap'])->name('explore-map');
    Route::get('/zips-in-view',[App\Http\Controllers\SegmentController::class,'zipsInView'])->name('zips-in-view');
    Route::get('/todo-zips',[App\Http\Controllers\SegmentController::class,'pendingZips'])->name('todo-zips');
   Route::get('/completed-zips',[App\Http\Controllers\SegmentController::class,'passedZips'])->name('completed-zips');
    Route::get('/passed-zips',[App\Http\Controllers\SegmentController::class,'index'])->name('passed-zips');
    Route::post('/getSingleZipview',[App\Http\Controllers\SegmentController::class,'getSingleZipview'])->name('getSingleZipview');
    Route::get('/zips-in-all-view', [App\Http\Controllers\SegmentController::class, 'getAllZipsInView'])->name('zips.in.view');
    Route::get('/load-map-passed-zips', [App\Http\Controllers\SegmentController::class, 'loadMapPassedZips'])->name('load-map-passed-zips');

});


Route::prefix('admin')->name('admin.')->namespace('App\Http\Controllers\Admin')->group(function () {
    Auth::routes(['register' => false]);
     Route::middleware(['auth:admin'])->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\HomeController::class, 'index'])->name('dashboard');
                Route::resource('account', App\Http\Controllers\Admin\UserController::class);
                Route::resource('athlete', App\Http\Controllers\Admin\AthleteController::class);
                Route::resource('athlete-activity', App\Http\Controllers\Admin\AthleteActivityController::class);
                Route::get('/athlete-activity/{athlete}',[App\Http\Controllers\Admin\AthleteActivityController::class, 'index'])->name('athlete-activity.index');
                Route::get('/getActivities', [App\Http\Controllers\Admin\AthleteActivityController::class,'getActivities'])->name('getActivities');
                Route::resource('settings', App\Http\Controllers\Admin\SettingController::class);
                Route::post('/authorize-strava',[App\Http\Controllers\Admin\SettingController::class,'authorizeStrava'])->name('authorize');
                Route::get('/exchange_token', [App\Http\Controllers\Admin\SettingController::class, 'handleStravaCallback'])->name('handleCallback');
                Route::post('/strava/webhook/create', [App\Http\Controllers\Admin\SettingController::class, 'createStravaWebhook'])->name('strava.createWebhook');
                Route::delete('/strava/webhook/delete', [App\Http\Controllers\Admin\SettingController::class, 'deleteStravaWebhook'])->name('strava.deleteWebhook');
                Route::post('update-password', [App\Http\Controllers\Admin\UserController::class, 'updatePassword'])->name('update-password');
                 Route::match(['get', 'post'], '/webhook/strava', [App\Http\Controllers\Admin\StravaWebhookController::class, 'receive']);
                Route::get('/webhook/strava', function (Request $request) {
                        if ($request->hub_verify_token === 'strava_webhook') {
                            return response()->json([
                                'hub.challenge' => $request->hub_challenge,
                            ]);
                        }
                    
                        return response()->json([], 403);
                    });
        });
});

