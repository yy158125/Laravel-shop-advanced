<?php

namespace App\Services;


use App\Exceptions\CouponCodeUnavailableException;
use App\Exceptions\InternalException;
use App\Jobs\CloseOrder;
use App\Models\CouponCode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSku;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yansongda\Supports\Log;

class OrderService
{
    public function store(User $user,UserAddress $userAddress,$remark, $items,CouponCode $coupon = null)
    {
        if ($coupon){
            $coupon->checkAvailable($user);
        }
        $order = DB::transaction(function () use ($user,$userAddress,$remark,$items,$coupon){
            $userAddress->update(['last_used_at' => Carbon::now()]);
            // 创建一个订单
            $order = new Order([
                'address' => [
                    'address' => $userAddress->full_address,
                    'zip'     => $userAddress->zip,
                    'contact_name' => $userAddress->contact_name,
                    'contact_phone' => $userAddress->contact_phone
                ],
                'remark' => $remark,
                'total_amount' => 0,
                'type' => Order::TYPE_NORMAL
            ]);
            $order->user()->associate($user);
            $order->save();

            $totalAmount = 0;
            // 遍历用户提交的 SKU
            foreach ($items as $data){
                $sku = ProductSku::find($data['sku_id']);
                // 创建一个 OrderItem 并直接与当前订单关联
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];
                if ($sku->decreaseStock($data['amount']) <= 0){
                    throw new InternalException('该商品库存不足');
                }
            }
            if ($coupon){
                $coupon->checkAvailable($user,$totalAmount);
                // 把订单金额修改为优惠后的金额
                $totalAmount = $coupon->getAdjustedPrice($totalAmount);
                // 将订单与优惠券关联
                $order->couponCode()->associate($coupon);
                if ($coupon->changeUsed() <= 0){
                    throw new CouponCodeUnavailableException('该优惠券已被兑完');
                }
            }
            // 更新订单总额
            $order->update(['total_amount' => $totalAmount]);
            // 将下单的商品从购物车中移除
            $skuIds = collect($items)->pluck('sku_id')->all();
            app(CartService::class)->remove($skuIds);

            return $order;
        });
        dispatch(new CloseOrder($order,config('app.order_ttl')));
        return $order;
    }

    // 众筹商品下单逻辑
    public function crowdfunding(User $user, UserAddress $address, ProductSku $sku, $amount)
    {
        $order = DB::transaction(function () use ($amount, $sku, $user, $address){
            $address->update(['last_used_at' => Carbon::now()]);
            $order = new Order([
                'address' => [
                    'address' => $address->full_address,
                    'zip'           => $address->zip,
                    'contact_name'  => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark'       => '',
                'total_amount' => $sku->price * $amount,
                'type' => Order::TYPE_CROWDFUNDING
            ]);
            $order->user()->associate($user);
            $order->save();
            $item = $order->items()->make([
                'amount' => $amount,
                'price' => $sku->price
            ]);
            $item->product()->associate($sku->product_id);
            $item->productSku()->associate($sku);
            $item->save();
            if ($sku->decreaseStock($amount) <= 0){
                throw new InvalidRequestException('该商品库存不足');
            }
            return $order;
        });
        // 众筹结束时间减去当前时间得到剩余秒数
        $crowdfundingTtl = $sku->product->crowdfunding->end_at->getTimestamp() - time();
        // 剩余秒数与默认订单关闭时间取较小值作为订单关闭时间
        dispatch(new CloseOrder($order,min(config('app.order_ttl'),$crowdfundingTtl)));

        return $order;
    }
    // 秒杀
    public function seckill(User $user,UserAddress $address,ProductSku $sku)
    {
        $order = DB::transaction(function () use ($user,$address,$sku){
            if ($sku->decreaseStock(1) <= 0){
                throw new InvalidRequestException('该商品库存不足');
            }
            // 更新此地址的最后使用时间
            $address->update(['last_used_at' => Carbon::now()]);
            // 创建一个订单
            $order = new Order([
                'address'      => [ // 将地址信息放入订单中
                    'address'       => $address->full_address,
                    'zip'           => $address->zip,
                    'contact_name'  => $address->contact_name,
                    'contact_phone' => $address->contact_phone,
                ],
                'remark'       => '',
                'total_amount' => $sku->price,
                'type'         => Order::TYPE_SECKILL,
            ]);
            // 订单关联到当前用户
            $order->user()->associate($user);
            $order->save();
            $item = $order->items()->make([
                'amount' => 1,
                'price' => $sku->price
            ]);
            $item->product()->associate($sku->product_id);
            $item->productSku()->associate($sku);
            $item->save();
            
            return $order;
        });
        dispatch(new CloseOrder($order,config('app.seckill_order_ttl')));
        return $order;
    }

    public function refundOrder(Order $order)
    {
        switch ($order->payment_method){
            case 'wechat':
                $refundNo = Order::getAvailableRefundNo();
                app('wechat_pay')->refund([
                    'out_trade_no' => $order->no,
                    'total_fee' => $order->total_amount * 100,
                    'refund_fee' => $order->total_amount * 100,
                    'out_refund_no' => $refundNo,
                    'notify_url' => ngrok_url('payment.wechat.refund_notify'),
                ]);
                $order->update([
                    'refund_no' => $refundNo,
                    'refund_status' => Order::REFUND_STATUS_PROCESSING
                ]);
                break;
            case 'alipay':
                $refundNo = Order::getAvailableRefundNo();
                Log::info($refundNo);
                // 调用支付宝支付实例的 refund 方法
                $ret = app('alipay')->refund([
                    'out_trade_no' => $order->no, // 之前的订单流水号
                    'refund_amount' => $order->total_amount, // 退款金额，单位元
                    'out_request_no' => $refundNo, // 退款订单号
                ]);

                if ($ret->sub_code){
                    $extra = $order->extra;
                    $extra['refund_failed_code'] = $ret->sub_code;
                    // 将订单的退款状态标记为退款失败
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_FAILED,
                        'extra' => $extra,
                    ]);
                } else{
                    // 将订单的退款状态标记为退款成功并保存退款订单号
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_SUCCESS,
                    ]);
                }
                break;

            default:
                throw new InternalException('未知订单支付方式：'.$order->payment_method);
                break;
        }
    }








}