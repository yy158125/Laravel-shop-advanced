<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\OrderItem;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateProductSoldCount
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        $order = $event->getOrder();
        // 循环遍历订单的商品
        foreach($order->items as $item){
            $product = $item->product;
            $soldCount = OrderItem::where('product_id',$product->id)
                ->whereHas('order',function ($query){
                    $query->whereNotNull('paid_at');
                })->sum('amount');
            // 更新商品销量
            $product->update([
                'sold_count' => $soldCount
            ]);
        }
    }
}
