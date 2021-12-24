<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Mail\SendEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
class MailController extends Controller
{
    public function sendEmail($code, $email){
        $details = [
            'title' => 'Mail From Hospital Verification',
            'body' => $code
        ];

        Mail::to($email)->send(new SendEmails($details));
        return '1';
    }
}
