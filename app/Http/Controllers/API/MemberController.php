<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Ramsey\Uuid\Uuid;

use App\Models\Member;
use App\Models\UserOtp;
use App\Services\SmsServices;
use App\Services\EmailService;

use Illuminate\Support\Facades\Auth;


class MemberController extends Controller
{
    private $emailService;
    private $smsService;

    public function __construct(EmailService $emailService, SmsServices $smsService)
    {
        $this->emailService = $emailService;
        $this->smsService = $smsService;
    }
    //List of Members
    public function index()
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

    //Store Member
    public function store(Request $request)
    {
        $userId = Auth::id();

        if ($request->step === '1') {

            $validator = Validator::make($request->all(), [
                'first_name' => 'required | string | max:200',
                'last_name' => 'string | max:200',
                'email' => 'required | email | max: 255 | unique:members',
                'mobile_number' => 'required | min:10 | max:10 | unique:members',
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

                    $user = Member::create([
                        'uuid' => Uuid::uuid4()->toString(),
                        'user_id' => $userId,
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => strtolower($request->email),
                        'mobile_number' => $request->mobile_number,
                        'company' => $request->company
                    ]);

                    $registration_success_message = "Congratulations !  Klout Membership registration is completed.";

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

    //update
    public function update(Request $request, $id)
    {
        $userId = Auth::id();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required | string | max:200',
            'last_name' => 'string | max:200',
            'email' => 'required | email | max: 255',
            'mobile_number' => 'required | min:10 | max:10',
            'company' => 'required',
            // 'step' => 'required',
        ]);

        if ($validator->fails()) {

            $errors = $validator->errors();

            return response()->json([
                'status' => 422,
                'message' => 'Validation Error',
                'error' => $errors,
            ]);
        }

        $member = Member::find($id);

        if ($member) {

            $member->user_id = $userId;
            $member->first_name = strtolower(strip_tags($request->first_name));
            $member->last_name = strtolower(strip_tags($request->last_name));
            $member->email = strtolower($request->email);
            $member->mobile_number = $request->mobile_number;
            $member->company = strtolower($request->company);

            $success = $member->update();

            if ($success) {

                return response()->json([
                    'status' => 200,
                    'message' => 'Member Updated Successfully'
                ]);
            } else {

                return response()->json([
                    'status' => 401,
                    'message' => 'Something Went Wrong. Please try again later.'
                ]);
            }
        } else {

            return response()->json([
                'status' => 404,
                'message' => 'Data not Found.'
            ]);
        }
    }

    //delete
    public function destroy(Request $request, $id)
    {
        //Delete event
        $member = Member::find($id);

        if ($member) {

            $deleted = $member->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Member Deleted Successfully.'
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Data not Found.'
            ]);
        }
    }
}
