<?php

namespace App\Services;


use App\Exceptions\InternalException;
use App\Jobs\CloseOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSku;
use App\Models\User;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function store(User $user,UserAddress $userAddress,$remark, $items)
    {
        $order = DB::transaction(function () use ($user,$userAddress,$remark,$items){
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









}