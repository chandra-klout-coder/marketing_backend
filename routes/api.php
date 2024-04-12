<?php

use App\Models\Event;
use App\Models\Member;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Facade\FlareClient\Report;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\AttendeeController;
use App\Http\Controllers\API\FeedBackController;
use App\Http\Controllers\API\MemberController;
use App\Http\Controllers\API\NotificationController;

//Test
Route::get('/test', [AuthController::class, 'test']);

//Auth - Register
Route::post('/register', [AuthController::class, 'register']);

//Auth - Login
Route::post('/login', [AuthController::class, 'login']);

//Auth - Forget password 
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

//Auth - Reset password
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');

Route::get('/countries', [AuthController::class, 'countries']);
Route::get('/states', [AuthController::class, 'states']);
Route::get('/cities', [AuthController::class, 'cities']);
Route::get('/jobtitles', [AuthController::class, 'jobtitles']);
Route::get('/companies', [AuthController::class, 'companies']);
Route::get('/industries', [AuthController::class, 'industries']);

Route::get('/send-sms', [AuthController::class, 'sendsms']);

//Employee Size Details
Route::get('/employee-size', [AuthController::class, 'employeeSize']);

//Protecting Routes
Route::middleware('auth:sanctum')->group(function () {

  //Check Authentication
  Route::get('/checkingAuthenticated', function () {
    return response()->json(['message' => 'You are in Klout Marketing Club', 'status' => 200], 200);
  });

  //Get user details
  Route::get('/profile', [UserController::class, 'profile']);

  //Update Profile
  Route::post('/updateprofile', [UserController::class, 'updateprofile']);

  //Change Password
  Route::post('/changepassword', [UserController::class, 'changePassword']);

  //Members 
  Route::get('/members', [MemberController::class, 'index']);
  Route::get('/members/{id}', [MemberController::class, 'show']);
  Route::post('/members', [MemberController::class, 'store']);
  Route::put('/members/{id}', [MemberController::class, 'update']);
  Route::delete('/members/{id}', [MemberController::class, 'destroy']);

  //ICP Search Options
  Route::post('/icp-search', [AuthController::class, 'icp_search']);

  //Send Mail to attendee. - testing purpose
  Route::post('/send-mail-to-attendee/{attendee_id}', [AttendeeController::class, 'sendMailToAttendee']);
  
  //Send Individula SMS to Attendee. -testing purpose
  Route::post('/send-sms-to-attendee/{attendee_id}', [AttendeeController::class, 'sendSmsToAttendee']);

  //Event-attendees
  Route::post('/attendees/upload/{event_id}', [AttendeeController::class, 'upload']);
  Route::get('/attendees', [AttendeeController::class, 'index']);
  Route::get('/attendees_event/{event_id}', [AttendeeController::class, 'getAttendeeByEventID']);
  Route::get('/virtualbusinesscard/{attendee_id}', [AttendeeController::class, 'getVitualBusinessCard']);

  //SMS Notifications
  Route::get('/notifications-list', [NotificationController::class, 'notifications_list']);
  Route::post('/notifications', [NotificationController::class, 'store_notification']);

  //Reports -
  Route::get('/reports', [ReportController::class, 'reports']);
  Route::post('/event-report', [ReportController::class, 'generateCSV']);
  Route::get('/event-report-download/{id}', [ReportController::class, 'downloadCSV']);
  Route::delete('/reports/{id}', [ReportController::class, 'destroy']);

  //Logout 
  Route::post('logout', [AuthController::class, 'logout']);
});

//Route::get('/smsnotifications/{id}', [NotificationController::class, 'sms_show']);
Route::post('/emailnotifications', [NotificationController::class, 'mail_store']);

//Notification - Send Reminder mail at regular Interval
Route::get('/send-mail-reminder-regular-interval', [NotificationController::class, 'sendMailReminderRegularInterval']);

//Notification - Send Reminder SMS at regular Interval
Route::get('/send-sms-reminder-regular-interval', [NotificationController::class, 'sendSmsReminderRegularInterval']);
