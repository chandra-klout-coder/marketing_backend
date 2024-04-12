<?php

namespace App\Http\Controllers\API;

use App\Models\Name;
// use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Models\City;
use App\Models\State;
use Ramsey\Uuid\Uuid;
use App\Models\Country;
use App\Models\Company;
use App\Models\UserOtp;
use App\Models\JobTitle;
use App\Models\Industry;
use App\Models\SkillsData;
use App\Models\Subscriber;
use App\Models\CompanyData;
use App\Models\IndustryData;
use App\Models\JobTitleData;
use App\Models\EmployeeSize;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Services\SmsServices;
use App\Services\EmailService;

use App\Models\PasswordReset;
use App\Mail\ResetPasswordMail;
use App\Models\ContactMessage;
use App\Models\WebsiteSetting;
use App\Models\SponsorshipPackages;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Member;
use App\Models\UnassignedData;
use Maatwebsite\Excel\Concerns\ToArray;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $emailService;
    private $smsService;

    public function __construct(EmailService $emailService, SmsServices $smsService)
    {
        $this->emailService = $emailService;
        $this->smsService = $smsService;
    }


    public function test()
    {
        return response()->json([
            'status' => 200,
            'message' => 'KKlout Marketing Application API Working.'
        ]);
    }


    //Send SMS
    public function sendsms()
    {
        $send_message = $this->smsService->sendSMS('+918709289369', 'You are invited to the event!');

        if ($send_message) {
            return response()->json(['message' => 'OTP Send Successfully.'], 200);
        }
    }

    public function employeeSize()
    {
        $employees = EmployeeSize::all();

        if ($employees) {
            return response()->json([
                'status' => 200,
                'message' => 'All Employees',
                'data' => $employees
            ]);
        } else {
            return response()->json([
                'status' => 422,
                'message' => 'Data not Found'
            ]);
        }
    }

    public function sendmail()
    {
        // $originalId = 123;

        // $encodedId = encodeId($originalId);
        // $decodedId = decodeId($encodedId);

        // return response()->json([
        //     'original_id' => $originalId,
        //     'encoded_id' => $encodedId,
        //     'decoded_id' => $decodedId,
        // ]);

        $data = ['message' => 'This is the email message.'];

        // Mail::to('chandra.bhushan@digimantra.com')->send(new MyTestEmail($data));

        return response()->json(['message' => 'Email sent successfully']);
    }

    //List of Members
    public function members()
    {
        $userId = Auth::id();

        $members = Member::where('user_id', $userId)->get();

        if ($members) {
            return response()->json([
                'status' => 200,
                'message' => 'All Member List',
                'data' => $members
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Member not Found'
            ]);
        }
    }

    //Get Member Details
    public function show($id)
    {
        //get details of member 
        $member = Member::find($id);

        if (!empty($member)) {

            return response()->json([
                'status' => 200,
                'message' => 'Member Detail',
                'data' => $member
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Member Detail Not Found',
            ]);
        }
    }

    //Mobile App
    public function get_industries()
    {
        $industries = Industry::all();

        if ($industries) {
            return response()->json([
                'status' => 200,
                'message' => 'All Industries',
                'data' => $industries
            ]);
        } else {
            return response()->json([
                'status' => 422,
                'message' => 'Data not Found'
            ]);
        }
    }

    public function get_job_titles()
    {
        $JobTitleData = JobTitle::all();

        if ($JobTitleData) {
            return response()->json([
                'status' => 200,
                'message' => 'All Job Titles',
                'data' => $JobTitleData
            ]);
        } else {
            return response()->json([
                'status' => 422,
                'message' => 'Data not Found'
            ]);
        }
    }

    public function get_companies()
    {
        $CompanyData = Company::all();

        if ($CompanyData) {
            return response()->json([
                'status' => 200,
                'message' => 'All Companies',
                'data' => $CompanyData
            ]);
        } else {
            return response()->json([
                'status' => 422,
                'message' => 'Data not Found'
            ]);
        }
    }
    public function others_unasssigned_data(Request $request)
    {
        $user_id = $request->input('user_id');
        $other_id = $request->input('other_id');
        $type = $request->input('type');
        $value = $request->input('value');

        if (isset($type) && !empty($type) && !empty($other_id)) {

            $data = new UnassignedData();

            //$city->uuid = Uuid::uuid4()->toString();
            $data->user_id = !empty($request->user_id) ? $request->user_id : "0";
            $data->other_id = $request->other_id;
            $data->type = $request->type;
            $data->value = !empty($request->value) ? $request->value : "";
            $success = $data->save();

            if ($success) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Data Saved Successfully.'
                ]);
            } else {
                return response()->json([
                    'status' => 422,
                    'message' => 'Something Went Wrong.Please try again later.'
                ]);
            }
        } else {
            return response()->json([
                'status' => 422,
                'message' => 'Incorrect Data'
            ]);
        }
    }

    public function cities()
    {
        $cities = City::all();

        if ($cities) {
            return response()->json([
                'status' => 200,
                'message' => 'All Cities',
                'data' => $cities
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Data not Found'
            ]);
        }
    }

    public function states()
    {
        $states = State::all();

        if ($states) {
            return response()->json([
                'status' => 200,
                'message' => 'All States',
                'data' => $states
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Data not Found'
            ]);
        }
    }
    public function countries()
    {
        $countries = Country::all();

        if ($countries) {
            return response()->json([
                'status' => 200,
                'message' => 'All Countries',
                'data' => $countries
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Data not Found'
            ]);
        }
    }

    public function icp_search(Request $request)
    {
        $countries = $request->input('countries', []);
        $states = $request->input('states', []);
        $cities = $request->input('cities', []);

        $industries = $request->input('industries', []);
        $jobtitles = $request->input('jobtitles', []);
        $companies = $request->input('companies', []);

        $query = Attendee::query();

        if (!empty($countries)) {
            $query->whereIn('country', $countries);
        }

        if (!empty($states)) {
            $query->whereIn('state', $states);
        }

        if (!empty($cities)) {
            $query->whereIn('city', $cities);
        }

        if (!empty($jobtitles)) {
            $query->whereIn('jobtitle', $jobtitles);
        }

        if (!empty($industries)) {
            $query->whereIn('industry', $industries);
        }

        if (!empty($companies)) {
            $query->whereIn('company', $companies);
        }

        $filteredResults = $query->get();

        if (isset($filteredResults) && !empty($filteredResults)) {

            return response()->json([
                'status' => 200,
                'message' => 'Result Data',
                'data' => $filteredResults
            ]);
        } else {
            return response()->json([
                'status' => 422,
                'message' => 'Data not Found'
            ]);
        }
    }

    public function jobtitles()
    {
        $jobtitles = JobTitle::all();

        if ($jobtitles) {
            return response()->json([
                'status' => 200,
                'message' => 'All Job Titles',
                'data' => $jobtitles
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Data not Found'
            ]);
        }
    }

    public function registerMemberOtp(Request $request)
    {
        if ($request->step === '1') {

            $validator = Validator::make($request->all(), [
                'first_name' => 'required | string | max:200',
                'last_name' => 'string | max:200',
                'email' => 'required | email | max: 255 | unique:users',
                'mobile_number' => 'required | min:10 | max:10 | unique:users',
                'company' => 'required',
                'step' => 'required',
            ]);

            if ($validator->fails()) {

                $errors = $validator->errors();

                return response()->json([
                    'status' => 422,
                    'message' => 'Validation Error',
                    'error' => $errors,
                ]);
            }

            // Send OTP 
            // $mobile_otp = rand(100000, 999999);
            // $email_otp = rand(100000, 999999);

            // For Demo purpose 
            $email_otp = '123456';
            $mobile_otp = '123456';

            $email = $request->email;
            $mobile_number = $request->mobile_number;

            UserOtp::where('email', $email)->delete();

            UserOtp::create([
                'email' => $email,
                'email_otp' => $email_otp,
                'mobile' =>  $mobile_number,
                'mobile_otp'  => $mobile_otp
            ]);

            $this->smsService->sendSMS('+91' . $mobile_number, 'Your OTP is : ' . $mobile_otp);

            $email_message = 'Your OTP is : ' . $email_otp;

            $this->emailService->sendRegistrationEmail($email, 'KLout Marketing : OTP Verification', $email_message);

            return response()->json([
                'status' => 200,
                'message' => 'OTP Send to Mobile Number and Email.'
            ]);
        } elseif ($request->step === '2') {

            $email = $request->email;
            $mobile = $request->mobile_number;

            $mobile_verify = UserOtp::where('mobile', $mobile)->first();
            $email_verify = UserOtp::where('email', $email)->first();

            if (!empty($mobile_verify) && !empty($mobile_verify)) {

                if (($mobile_verify->mobile_otp !== trim($request->mobile_otp))) {
                    return response()->json([
                        'status' => 400,
                        'error' => 'mobile_otp',
                        'message' => 'Mobile OTP is Invalid.',
                    ]);
                }

                if (($email_verify->email_otp !== trim($request->email_otp))) {
                    return response()->json([
                        'status' => 400,
                        'error' => 'email_otp',
                        'message' => 'Email OTP is Invalid.',
                    ]);
                } else if (!empty($mobile_verify) && !empty($email_verify)) {

                    $user = User::create([
                        'uuid' => Uuid::uuid4()->toString(),
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => strtolower($request->email),
                        'password' => Hash::make($request->password),
                        'mobile_number' => $request->mobile_number,
                        'company' => $request->company
                    ]);

                    $registration_success_message = "Congratulations ! Your registration is Completed on Klout Club.";

                    $this->smsService->sendSMS('+91' . $mobile, $registration_success_message);

                    $this->emailService->sendRegistrationEmail($email, 'Klout : Registration Successfully', $registration_success_message);

                    $delete_otp_record = UserOtp::where('email', $email)->delete();

                    if ($delete_otp_record) {
                        return response()->json([
                            'status' => 200,
                            'message' => 'OTP Verified Successfully'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 401,
                        'message' => 'Something Went Wrong.Please try again.'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'Invalid OTP.Please try again.'
                ]);
            }
        }

        return response()->json([
            'status' => 200,
            'message' => 'Invalid paramters and try again.'
        ]);
    }

    //User-Registration 
    public function register(Request $request)
    {
        if ($request->step === '1') {

            $validator = Validator::make($request->all(), [
                'first_name' => 'required | string | max:200',
                'last_name' => 'string | max:200',
                'email' => 'required | email | max: 255 | unique:users',
                'password' => 'required | min:8',
                'mobile_number' => 'required | min:10 | max:10 | unique:users',
                'company' => 'required',
                'designation' => 'required',
                'pincode' => 'required',
                'address' => 'required',
                'step' => 'required',
                'tnc' => 'required'
            ]);

            if ($validator->fails()) {

                $errors = $validator->errors();

                return response()->json([
                    'status' => 422,
                    'message' => 'Validation Error',
                    'error' => $errors,
                ]);
            }

            // Send OTP 
            $mobile_otp = rand(100000, 999999);
            $email_otp = rand(100000, 999999);

            $email = $request->email;
            $mobile_number = $request->mobile_number;

            UserOtp::where('email', $email)
                ->orWhere('mobile', $mobile_number)
                ->delete();

            UserOtp::create([
                'email' => $email,
                'email_otp' => $email_otp,
                'mobile' =>  $mobile_number,
                'mobile_otp'  => $mobile_otp
            ]);

            $mobile_otp_send = $this->send_local_text_sms(
                $mobile_number,
                $mobile_otp
            );

            // Twillio 
            // $this->smsService->sendSMS('+91' . $mobile_number, 'Your OTP is : ' . $mobile_otp);

            $email_message = 'Your OTP is : ' . $email_otp;

            $this->emailService->sendRegistrationEmail($email, 'Klout: OTP Verification', $email_message);

            if ($mobile_otp_send) {
                return response()->json([
                    'status' => 200,
                    'message' => 'Send OTP to Mobile Number and Email.'
                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Something Went Wrong. Please try again later.'
                ]);
            }
        } elseif ($request->step === '2') {

            $email = $request->email;
            $mobile = $request->mobile_number;

            $mobile_verify = UserOtp::where('mobile', $mobile)->first();
            $email_verify = UserOtp::where('email', $email)->first();

            if (!empty($mobile_verify) && !empty($mobile_verify)) {

                if (($mobile_verify->mobile_otp !== trim($request->mobile_otp))) {
                    return response()->json([
                        'status' => 400,
                        'error' => 'mobile_otp',
                        'message' => 'Mobile OTP is Invalid.',
                    ]);
                }

                if (($email_verify->email_otp !== trim($request->email_otp))) {
                    return response()->json([
                        'status' => 400,
                        'error' => 'email_otp',
                        'message' => 'Email OTP is Invalid.',
                    ]);
                } else if (!empty($mobile_verify) && !empty($email_verify)) {

                    $user = User::create([
                        'uuid' => Uuid::uuid4()->toString(),
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => strtolower($request->email),
                        'password' => Hash::make($request->password),
                        'mobile_number' => $request->mobile_number,
                        'company' => $request->company,
                        'company_name' => !empty($request->company_name) ? $request->company_name : "",
                        'designation_name' => !empty($request->designation_name) ? $request->designation_name : "",
                        'designation' => $request->designation,
                        'pincode' => $request->pincode,
                        'address' => $request->address,
                        'tnc' => (!empty($request->tnc) && $request->tnc === "on")  ? "1" : "0",
                        'notifications' => (!empty($request->notifications) && $request->notifications === "on")  ? "1" : "0"
                    ]);

                    if ($user) {

                        $registration_success_message = "Congratulations! Your registration is Completed on Klout Club.";

                        $this->emailService->sendRegistrationEmail($email, 'Klout : Registration Successfully', $registration_success_message);

                        $delete_otp_record = UserOtp::where('email', $email)->delete();

                        if ($delete_otp_record) {
                            return response()->json([
                                'status' => 200,
                                'message' => 'OTP Verified and User Register Successfully.'
                            ]);
                        } else {
                            return response()->json([
                                'status' => 400,
                                'message' => 'Something Went Wrong. Please try again later.'
                            ]);
                        }
                    }
                } else {
                    return response()->json([
                        'status' => 401,
                        'message' => 'Something Went Wrong.Please try again.'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'Invalid OTP.Please try again.'
                ]);
            }
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'Invalid parameters and try again.'
            ]);
        }
    }


    //Send SMS via Local Text API - Single SMS
    public function send_local_text_sms($mobile_number, $otp)
    {
        $apiKey = urlencode(Config('app.textlocal_api_key'));

        $numbers = array($mobile_number);

        $sender = urlencode(Config('app.textlocal_sender'));

        $content = $otp . " is your OTP for verifying your profile with KloutClub by Insightner Marketing Services";

        $message = rawurlencode($content);

        $numbers = implode(',', $numbers);

        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);

        $ch = curl_init('https://api.textlocal.in/send/');

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        curl_close($ch);

        $responseData = json_decode($response, true);

        if ($responseData['status'] === "success") {
            return true;
        } else {
            return false;
        }
    }

    //User-Login
    public function login(Request $request)
    {
        $validator = validator::make($request->all(), [
            'email' => 'required | max:200',
            'password' => 'required'
        ]);

        if ($validator->fails()) {

            $errors = $validator->errors();

            return response()->json([
                'status' => 422,
                'message' => 'Validation Error',
                'error' => $errors,
            ]);
        } else {

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {

                return response()->json([
                    'status' => 401,
                    'message' => 'Invalid Credentials'
                ]);
            } else {

                $token = $user->createToken($user->email . '_Token')->plainTextToken;

                return response()->json([
                    'status' => 200,
                    'message' => 'Logged In Successfully',
                    'email' => 'Welcome to Kloud Club - ' . ucfirst($user->first_name),
                    'access_token_type' => 'Bearer',
                    'access_token' => $token,
                ]);
            }
        }
    }

    //Auth - Logout 
    public function logout()
    {
        $user = Auth::user();

        if ($user) {

            $user->tokens()->delete();

            return response()->json([
                'status' => 200,
                'message' => 'You have successfully logged out.'
            ]);
        }

        return response()->json(['message' => 'User not authenticated.'], 401);
    }

    //Auth - Forgot Password Link 
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {

            $errors = $validator->errors();

            return response()->json([
                'status' => 422,
                'message' => 'Email is not registered.',
                'error' => $errors->first(),
            ]);
        } else {

            $email = $request->input('email');

            $user = User::where('email', $email)->first();

            if ($user) {

                $token = Str::random(64);

                $exist = PasswordReset::where('email', $email)->get();

                if (!empty($exist)) {
                    PasswordReset::where('email', $email)->delete();
                }

                PasswordReset::create([
                    'email' => $email,
                    'token' => $token,
                ]);

                // Send the password reset link via email
                $resetLink  = Config('app.front_end_url') . '/reset-password?email=' . $email . '&token=' . $token;

                $this->smsService->sendSMS('+91' . $user->mobile_number, 'Dear User, 
                Thank you for your request to change your password.Please click link to enter your new password :
             ' . $resetLink . '
             If you have not requested to change password, then ignore this message.
             
             Kind regards,
             Klout Club');

                Mail::to($email)->send(new ResetPasswordMail($resetLink));

                return response()->json(
                    [
                        'status' => '200',
                        'message' => 'Reset password link sent successfully.'
                    ]
                );
            } else {
                return response()->json(
                    [
                        'status' => '400',
                        'message' => 'Invlaid Email! Data not Found.'
                    ]
                );
            }
        }
    }

    //Reset Password 
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {

            $errors = $validator->errors();

            return response()->json([
                'status' => 422,
                'message' => 'Invalid Data.',
                'error' => $errors
            ]);
        } else {

            $email = $request->input('email');
            $token = $request->input('token');
            $password = $request->input('password');
            $confirm_password = $request->input('confirm_password');

            // Check if the token is valid for the given email
            $passwordReset = PasswordReset::where('email', $email)->first();

            if (!$passwordReset || $passwordReset->token !== $request->input('token')) {
                return response()->json([
                    'status' => 422,
                    'error' => 'Link Expired.Please try again.'
                ]);
            }

            if (trim($request->input('password')) !== trim($request->input('confirm_password'))) {
                return response()->json([
                    'status' => 404,
                    'error' => 'New password or Confirm Password are not matching.'
                ]);
            }

            // Find the user and update the password
            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json(
                    [
                        'status' => 404,
                        'error' => 'User not found.Please try reset password link again.'
                    ]
                );
            }

            $user->password = Hash::make($password);
            $user->save();

            // Delete the password reset entry from the table
            PasswordReset::where('email', $email)->delete();

            //send mail and sms
            $changed_password_success_message = "Congratulations ! Password Changed Successfully.";

            $this->emailService->sendChangedPasswordEmail($email, 'Klout : Password Changed', $changed_password_success_message);

            return response()->json(['status' => 200, 'message' => 'Password Reset Successfully']);
        }
    }

    //JobTitles 
    public function jobTitle()
    {
        $jobTitles = JobTitle::orderBy('name', 'asc')->get()->toArray();

        if ($jobTitles) {
            return response()->json([
                'status' => 200,
                'message' => 'All Job-Titles',
                'data' => $jobTitles
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Data not Found',
                'data' => []
            ]);
        }
    }

    //Companies
    public function companies()
    {
        $companies = Company::orderBy('name', 'asc')->get()->toArray();

        if ($companies) {
            return response()->json([
                'status' => 200,
                'message' => 'All Companies',
                'data' => $companies
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Data not Found',
                'data' => []
            ]);
        }
    }

    //Sponsorship Packages
    public function sponsorshipPackages()
    {
        $sponsorshipPackages = SponsorshipPackages::orderBy('name', 'asc')->get()->toArray();

        if ($sponsorshipPackages) {
            return response()->json([
                'status' => 200,
                'message' => 'All Sponsorship Packages',
                'data' => $sponsorshipPackages
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Data not Found',
                'data' => []
            ]);
        }
    }

    //Industries
    public function industries()
    {
        $industries = Industry::orderBy('name', 'asc')->get()->toArray();

        if ($industries) {
            return response()->json([
                'status' => 200,
                'message' => 'All Industries',
                'data' => $industries
            ]);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Data not Found',
                'data' => []
            ]);
        }
    }

    // Subscribe 
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscribers,email',
        ]);

        if ($validator->fails()) {

            $error = $validator->errors();

            return response()->json([
                'status' => 422,
                'message' => 'User already subscribed.',
                'error' => ucwords($error->first())
            ]);
        } else {

            $user = Subscriber::where('email', $request->email)->first();

            if ($user) {
                response()->json([
                    'status' => 201,
                    'message' => 'User already subscribed.'
                ]);
            } else {
                Subscriber::create([
                    'email' => $request->input('semail'),
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Subscribed successfully.'
                ]);
            }
        }
    }

    // Unsubscribe
    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscribers,email',
        ]);

        $subscriber = Subscriber::where('email', $request->input('email'))->get();

        if ($subscriber) {

            $subscriber = Subscriber::where('email', $request->input('email'))->first();
            $subscriber->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Unsubscribed Successfully'
            ]);
        } else {
            return response()->json([
                'status' => 422,
                'message' => 'Email Not found.'
            ]);
        }
    }

    // Contact-Us
    public function contact_us(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required',
            'subject' => 'required',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {

            $error = $validator->errors();

            return response()->json([
                'status' => 422,
                'message' => 'Invalid Data.',
                'error' => $error
            ]);
        } else {

            ContactMessage::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'subject' => $request->input('subject'),
                'message' => $request->input('message'),
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Message submitted successfully'
            ]);
        }
    }

    // Website Settings
    public function website_settings(Request $request, $key)
    {
        // Update a specific website setting by key
        $request->validate([
            'value' => 'required',
        ]);

        $setting = WebsiteSetting::where('key', $key)->first();

        if ($setting) {
            $setting->update(['value' => $request->input('value')]);
            return response()->json(['message' => 'Setting updated successfully']);
        }

        return response()->json(['error' => 'Setting not found'], 404);
    }

    public function show_website_settings($key)
    {
        // Retrieve a specific website setting by key
        $setting = WebsiteSetting::where('key', $key)->first();

        return $setting
            ? response()->json($setting)
            : response()->json(['error' => 'Setting not found'], 404);
    }
    public function all_website_settings()
    {
        // Retrieve a specific website setting by key
        $settings = WebsiteSetting::all();

        return response()->json($settings);
    }
}
