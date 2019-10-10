<?php

namespace App\Http\Controllers;


use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function payByAlipay(Request $request,Order $order)
    {
        $this->authorize('own',$order);
        if ($order->paid_at || $order->closed){
            throw new InvalidRequestException('订单状态不正确');
        }
        return app('alipay')->web([
            'out_trade_no' => $order->no,
            'total_amount' => $order->total_amount,
            'subject' => '支付Shop订单'.$order->no,
        ]);
    }
    // 前端回调页面   校验提交的参数是否合法
    public function alipayReturn()
    {
        try {
            app('alipay')->verify();
        } catch (\Exception $e) {
            return view('pages.error', ['msg' => '数据不正确']);
        }

        return view('pages.success', ['msg' => '付款成功']);
    }
    // 服务器端回调
    public function alipayNotify()
    {
        $data = app('alipay')->verify();
        // 如果订单状态不是成功或者结束，则不走后续的逻辑
        if (!in_array($data->trade_status,['TRADE_SUCCESS','TRADE_FINISHED'])){
            return app('alipay')->success();
        }
        // \Log::debug('Alipay notify', $data->all());

        $order = Order::where('no', $data->out_trade_no)->first();
        if (!$order) {
            return 'fail';
        }
        // 如果这笔订单的状态已经是已支付
        if ($order->paid_at) {
            return app('alipay')->success();
        }
        $orderData = [
            'paid_at' => Carbon::now(),
            'payment_method' => 'alipay',
            'payment_no' => $data->trade_no, // 支付宝订单号
        ];
        if ($order->closed){
            $orderData['closed'] = false;
        }
        $order->update($orderData);
        $this->afterPaid($order);
        return app('alipay')->success();
    }

    protected function afterPaid($order)
    {
        event(new OrderPaid($order));
    }
}