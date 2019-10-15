<?php

namespace App\Http\Requests;


use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSku;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\AuthenticationException;
use Yansongda\Supports\Log;

class SeckillOrderRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address.province'      => 'required',
            'address.city'          => 'required',
            'address.district'      => 'required',
            'address.address'       => 'required',
            'address.zip'           => 'required',
            'address.contact_name'  => 'required',
            'address.contact_phone' => 'required',
            'sku_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $stock = Redis::get('seckill_sku_'.$value);
                    if(is_null($stock)){
                        return $fail('该商品不存在');
                    }
                    if ($stock < 1) {
                        return $fail('该商品已售完');
                    }
                    if(!$user = Auth::user()){
                        throw new AuthenticationException('请先登录');
                    }
                    if(!$user->email_verified){
                        throw new InvalidRequestException('请先验证邮箱');
                    }
                    Log::info($stock);
                    $order = Order::query()
                        ->where('user_id',$this->user()->id)
                        ->whereHas('items',function ($query) use ($value){
                            $query->where('product_sku_id',$value);
                        })
                        ->where(function ($query){
                            // 已支付的订单
                            $query->whereNotNull('paid_at')
                                // 或者未关闭的订单
                                ->orWhere('closed', false);
                        })
                        ->first();
                    if ($order){
                        if ($order->paid_at){
                            return $fail('你已经抢购了该商品');
                        }
                        return $fail('你已经下单了该商品，请到订单页面支付');
                    }
                }
            ]
        ];
    }
}
