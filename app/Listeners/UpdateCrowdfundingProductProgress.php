<?php

namespace App\Listeners;

use App\Events\Orderpaid;
use App\Models\Order;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Yansongda\Supports\Log;

class UpdateCrowdfundingProductProgress
{

    /**
     * Handle the event.
     *
     * @param  Orderpaid  $event
     * @return void
     */
    public function handle(Orderpaid $event)
    {
        $order = $event->getOrder();
        if ($order->type !== Order::TYPE_CROWDFUNDING){
            return;
        }
        $crowdfunding = $order->items[0]->product->crowdfunding;
        $data = Order::where('type',Order::TYPE_CROWDFUNDING)
            // 并且是已支付的
            ->whereNotNull('paid_at')
            ->whereHas('items',function ($query) use ($crowdfunding){
                $query->where('product_id',$crowdfunding->product_id);
            })
            ->first([
                // 取出订单总金额
                DB::raw('sum(total_amount) as total_amount'),
                // 取出去重的支持用户数
                DB::raw('count(distinct(user_id)) as user_count'),
            ]);
        Log::info($data);
        $crowdfunding->update([
            'total_amount' => $data->total_amount,
            'user_count' => $data->user_count
        ]);
    }
}
