<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Throwable;

class CouponCodeUnavailableException extends Exception
{
    public function __construct(string $message = "", int $code = 403)
    {
        parent::__construct($message, $code);
    }

    // 当这个异常被触发时，会调用 render 方法来输出给用户
    public function render(Request $request)
    {
        if ($request->expectsJson()){
            return response()->json(['msg' => $this->message],$this->code);
        }
        return redirect()->back()->withErrors(['coupon_code' => $this->message]);
    }
}
