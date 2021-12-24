<?php

use App\Http\Controllers\api\v1\LoginController;
use App\Http\Controllers\api\v1\MailController;
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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

//Users
Route::prefix('/user')->group(function(){
    Route::post('/login', 'App\Http\Controllers\api\v1\LoginController@login');
    Route::post('/createuser', 'App\Http\Controllers\api\v1\LoginController@createuser');
    Route::post('/verify_mobile', [LoginController::class,'verifyMobile']);
    Route::post('/verify_email', [LoginController::class, 'verifyEmail']);
    Route::post('/resend_verification_email', [LoginController::class, 'resendEmail']);
    Route::post('/resend_verification_sms', [LoginController::class, 'resendSMS']);
});
