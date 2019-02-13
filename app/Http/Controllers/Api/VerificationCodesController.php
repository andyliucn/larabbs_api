<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\VerificationCodeRequest;
use Doctrine\Common\Cache\Cache;
use Illuminate\Http\Request;
use Overtrue\EasySms\EasySms;

class VerificationCodesController extends Controller
{
    // 本地环境不发送短信[省钱]
    static $_env = 'local';

    public function __construct()
    {
        $this::$_env = config('app.env');
    }

    public function store(VerificationCodeRequest $request, EasySms $easySms)
    {
        $phone = $request->phone;

        if($this::$_env == 'local'){
            $code = '1234';
        } else {
            // 生成4位随机数, 左侧补0
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);

            try {
                $result = $easySms->send($phone, [
                    'template' => env('ALIYUN_TEMPLATE_CODE'),
                    'data' => [
                        'code' => $code
                    ],
                ]);
            } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
                $message = $exception->getException('aliyun')->getMessage();
                return $this->response->errorInternal($message ?: '短信发送异常');
            }
        }

        $key = 'verificationCode_' . str_random(15);
        $expiredAt = now()->addMinutes(5);

        // 缓存验证码 5 分钟过期
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);

        return $this->response->array([
            'phone' => $this::$_env=='local' ? \Cache::get($key) : '',
            'key'=> $key,
            'expired_at'=> $expiredAt->toDateTimeString()
        ])->setStatusCode(201);
    }
}
