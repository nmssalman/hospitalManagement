<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Twilio\Rest\Client;
use App\Http\Controllers\api\v1\MailController;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        $login = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string'
        ]);

        if (!Auth::attempt($login))
            return response(['code' => '0', 'message' => 'Invalid Login Credentials']);
        return response(['code' => '1', 'message' => 'Authentication Successful']);
    }

    public function createuser(Request $request)
    {
        $mailController = new MailController();
        $login = $request->validate([
            'name' => 'required|string',
            'mobile' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string',
            'address' => 'required|string',
            'blood_group' => 'required|string',
            'city' => 'required|string'
        ]);
        $userExist = User::where('mobile', '=', $request->get("mobile"));
        if ($userExist->count() == 0) {
            $SMScode = random_int(100000, 999999);
            $response = $this->sendMessage($SMScode, $request->get('mobile'));
            $Emailcode = random_int(100000, 999999);

            if ($response == '1') {
                $response = $mailController->sendEmail($Emailcode, $request->get('email'));
                if ($response == '1') {
                    $user = User::create($login);
                    User::where('id', $user->id)->update(['mobile_verification_code' => $SMScode, 'email_verification_code' => $Emailcode]);
                    return response(['code' => '1', 'message' => 'User has been successfully created', 'sms_verification_code' => $SMScode, 'email_verification_code' => $Emailcode, 'data' => $user]);
                } else
                    return response(['code' => '0', 'message' => 'Unknown Error in Sending Email Verification']);
            } else
                return response(['code' => '0', 'message' => 'Unknown Error in Sending Mobile Verification']);
        } else
            return response(['code' => '0', 'message' => 'Phone number is registered!']);
    }

    public function sendMessage($message, $recipients)
    {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_NUMBER");

        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
            $recipients,
            ['from' => $twilio_number, 'body' => "Your verification code is $message"]
        );
        return '1';
    }

    public function verifyMobile(Request $request)
    {
        $mobile = $request->validate([
            'code' => 'required|string',
            'id' => 'required|string'
        ]);
        $user = User::where('mobile_verification_code', $request->get('code'))->first();
        if ($user->count() == 0)
            return response(['code' => '0', 'message' => 'Invalid Verification Code']);
        else {
            User::where('id', $request->get('id'))->update(['is_mobile_verified' => '1']);
            return response(['code' => '1', 'message' => 'Mobile Number Verified']);
        }
    }

    public function verifyEmail(Request $request)
    {
        $mobile = $request->validate([
            'code' => 'required|string',
            'id' => 'required|string'
        ]);
        $user = User::where('email_verification_code', $request->get('code'))->first();
        if ($user->count() == 0)
            return response(['code' => '0', 'message' => 'Invalid Verification Code']);
        else {
            User::where('id', $request->get('id'))->update(['is_email_verified' => '1']);
            return response(['code' => '1', 'message' => 'Email Address Verified']);
        }
    }

    public function resendEmail(Request $request)
    {
        $mailController = new MailController();
        $user = User::where('id', $request->get('id'))->first();
        if ($user->count() == 0)
            return response(['code' => '0', 'message' => 'Invalid User']);
        else {
            $response = $mailController->sendEmail($user->email_verification_code, $user->email);
            if ($response == '1')
                return response(['code' => '1', 'message' => 'Email has been sent', 'verification_code' => $user->email_verification_code]);
            else
                return response(['code' => '0', 'message' => 'Unknown Error while Sending Email']);
        }
    }

    public function resendSMS(Request $request){
        $user = User::where('id', $request->get('id'))->first();
        if ($user->count() == 0)
            return response(['code' => '0', 'message' => 'Invalid User']);
        else {
            $response = $this->sendMessage($user->mobile_verification_code, $user->mobile);
            if ($response == '1')
                return response(['code' => '1', 'message' => 'SMS has been sent', 'verification_code' => $user->mobile_verification_code]);
            else
                return response(['code' => '0', 'message' => 'Unknown Error while Sending SMS']);
        }
    }
}
