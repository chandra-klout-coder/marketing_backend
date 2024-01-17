<?php

use App\Models\Event;
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

Route::post('/icp-search', [AuthController::class, 'icp_search']);


















//It will be implemented once mobile app is ready
Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');

//Protecting Routes
Route::middleware('auth:sanctum')->group(function () {

  //Get user details
  Route::get('profile', [UserController::class, 'profile']);

  //Check Authentication
  Route::get('/checkingAuthenticated', function () {
    return response()->json(['message' => 'You are in', 'status' => 200], 200);
  });

  //Logout 
  Route::post('logout', [AuthController::class, 'logout']);

  //Update Profile
  Route::post('updateprofile', [UserController::class, 'updateprofile']);

  //Change Password
  Route::post('changepassword', [UserController::class, 'changePassword']);

  //Events
  Route::get('/events', [EventController::class, 'index']);
  Route::post('/events', [EventController::class, 'store']);
  // Route::get('/events/{id}', [EventController::class, 'show'])->name('events.show');
  Route::put('/events/{id}', [EventController::class, 'update']);
  Route::delete('/events/{id}', [EventController::class, 'destroy']);

  //Event-attendees
  Route::post('/attendees/upload/{event_id}', [AttendeeController::class, 'upload']);
  Route::get('/attendees', [AttendeeController::class, 'index']);
  Route::get('/attendees_event/{event_id}', [AttendeeController::class, 'getAttendeeByEventID']);
  Route::post('/attendees', [AttendeeController::class, 'store']);
  Route::get('/attendees/{id}', [AttendeeController::class, 'show']);
  Route::put('/attendees/{id}', [AttendeeController::class, 'update']);
  Route::delete('/attendees/{id}', [AttendeeController::class, 'destroy']);
  Route::get('/virtualbusinesscard/{attendee_id}', [AttendeeController::class, 'getVitualBusinessCard']);

  //Send Mail to attendee. - testing purpose
  Route::post('/send-mail-to-attendee/{attendee_id}', [AttendeeController::class, 'sendMailToAttendee']);
  //Send Individula SMS to Attendee. -testing purpose
  Route::post('/send-sms-to-attendee/{attendee_id}', [AttendeeController::class, 'sendSmsToAttendee']);

  //Feedback-Form
  Route::get('/feedbacks', [FeedBackController::class, 'index']);
  Route::post('/feedbacks', [FeedBackController::class, 'store']);
  Route::get('/feedbacks/{id}', [FeedBackController::class, 'show']);
  Route::delete('/feedbacks/{id}', [FeedBackController::class, 'destroy']);

  //Communications - testing
  Route::get('/message', [FeedBackController::class, 'message']);
  Route::get('/send-email', [AttendeeController::class, 'sendmail']);

  //Reports - Dashboard
  Route::get('/totalattendeesOrganizer', [ReportController::class, 'total_attendees_for_organizer']);
  Route::get('/totalevents', [ReportController::class, 'total_number_of_events']);
  Route::get('/upcomingevents', [ReportController::class, 'upcoming_events']);
  Route::get('/totalsponsors', [ReportController::class, 'total_sponsors']);

  //Reports - Event
  Route::get('/totalattendees/{event_id}', [ReportController::class, 'total_attendees']);
  Route::get('/totalsponsors/{event_id}', [ReportController::class, 'total_sponsors_event']);
  Route::get('/totalattendeetype/{event_id}', [ReportController::class, 'total_attendee_type_event']);
  Route::get('/attendeeProfileCompleted/{event_id}', [ReportController::class, 'attendee_profile_completed']);

  //SMS Notifications
  Route::get('/notifications-list', [NotificationController::class, 'notifications_list']);
  Route::post('/notifications', [NotificationController::class, 'store_notification']);

  //Reports -
  Route::get('/reports', [ReportController::class, 'reports']);
  Route::post('/event-report', [ReportController::class, 'generateCSV']);
  Route::get('/event-report-download/{id}', [ReportController::class, 'downloadCSV']);
  Route::delete('/reports/{id}', [ReportController::class, 'destroy']);
});

//Notification - Send Reminder 
Route::post('/send-reminder-on-start-date', [EventController::class, 'sendReminderOnStartDate']);

Route::post('/send-reminder-one-hour-before-start-time', [EventController::class, 'sendReminderOneHourBeforeStartTime']);

//Route::get('/smsnotifications/{id}', [NotificationController::class, 'sms_show']);
Route::post('/emailnotifications', [NotificationController::class, 'mail_store']);

//Notification - Send Reminder mail at regular Interval
Route::get('/send-mail-reminder-regular-interval', [NotificationController::class, 'sendMailReminderRegularInterval']);

//Notification - Send Reminder SMS at regular Interval
Route::get('/send-sms-reminder-regular-interval', [NotificationController::class, 'sendSmsReminderRegularInterval']);

Route::get('/send-sms', [AttendeeController::class, 'sendsms']);
