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
    'namespace'=> 'App\Http\Controllers\Api',
    'middleware'=> 'serializer:array'
], function ($api) {
    /**
     * API 接口限制 节流等
     */
    $api->group([
        'middleware'=> 'api.throttle',
        'limit'=> config('api.rate_limits.sign.limit'),          /*限制次数*/
        'expires'=> config('api.rate_limits.sign.expires'),      /*过期时间*/
    ], function ($api){
        // 短信验证码
        $api->post('verificationCodes', 'VerificationCodesController@store')
            ->name('api.verificationCodes.store');

        // 图片验证码
        $api->post('captchas', 'CaptchasController@store')
            ->name('api.captchas.store');

        // 用户注册
        $api->post('users', 'UserController@store')
            ->name('api.users.store');

        // 登录
        $api->post('authorizations', 'AuthorizationsController@store')
            ->name('api.authorizations.store');
        // 刷新token
        $api->put('authorizations/current', 'AuthorizationsController@update')
            ->name('api.authorizations.update');
        // 删除token
        $api->delete('authroizations/current', 'AuthorizationsController@destroy')
            ->name('api.authorizations.destroy');

        $api->get('categories', 'CategoriesController@index')
            ->name('api.categories.index');

        /**
         * 需要 token 验证的接口
         */
        $api->group(['middleware'=> 'api.auth'], function ($api){
            // 当前登录用户信息
            $api->get('user', 'UserController@me')
                ->name('api.user.show');
            // 编辑登录用户信息
            $api->patch('user', 'UserController@update')
                ->name('api.user.update');
            // 图片资源
            $api->post('images', 'ImagesController@store')
                ->name('api.images.store');

            // 发布话题
            $api->post('topics', 'TopicsController@store')
                ->name('api.topics.store');
        });
    });
});