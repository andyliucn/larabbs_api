<?php

use Illuminate\Http\Request;

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

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace'=> 'App\Http\Controllers\Api'
], function ($api) {
    $api->group([
        'middleware'=> 'api.throttle',
        'limit'=> config('api.rate_limits.sign.limit'),          /*限制次数*/
        'expires'=> config('api.rate_limits.sign.expires'),      /*过期时间*/
    ], function ($api){
        // 短信验证码
        $api->post('verificationCodes', 'VerificationCodesController@store')
            ->name('api.verificationCodes.store');

        // 用户注册
        $api->post('users', 'UserController@store')
            ->name('api.users.store');

        // 图片验证码
        $api->post('captchas', 'CaptchasController@store')
            ->name('api.captchas.store');
    });
});