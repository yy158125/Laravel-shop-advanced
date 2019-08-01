<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EmailVerificationController extends Controller
{
    public function verify(Request $request)
    {
        $email = $request->input('email');
        $token = $request->input('token');
        // 如果有一个为空说明不是一个合法的验证链接，直接抛出异常。
        if (!$email || !$token) {
            throw new \Exception('验证链接不正确');
        }
        if ($token != Cache::get('email_verified_'.$email)){
            throw new \Exception('验证链接不正确或已过期');
        }
        if (!$user = User::where('email', $email)->first()) {
            throw new \Exception('用户不存在');
        }
        Cache::forget('email_verified_'.$email);
        $user->update(['email_verified'=>true]);
        // 最后告知用户邮箱验证成功。
        return view('pages.success', ['msg' => '邮箱验证成功']);
    }
    public function send(Request $request){
        $user = $request->user();

        // 判断用户是否已经激活
        if ($user->email_verified) {
            throw new \Exception('你已经验证过邮箱了');
        }
        $res = $user->notify(new EmailVerificationNotification());
        dd($res);
        return view('pages.success', ['msg' => '邮件发送成功']);
    }
}