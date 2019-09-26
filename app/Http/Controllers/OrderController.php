<?php

namespace App\Http\Controllers;


use App\Events\OrderReviewd;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\UserAddress;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(OrderRequest $request,OrderService $orderService)
    {
        $user = $request->user();
        $address = UserAddress::find($request->address_id);
        return $orderService->store($user,$address,$request->remark,$request->items);
    }
    public function index(Request $request)
    {
        $orders = Order::query()
            // 使用 with 方法预加载，避免N + 1问题
            ->with(['items.product', 'items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate();

        return view('orders.index', ['orders' => $orders]);
    }
    public function show(Request $request,Order $order)
    {
        $this->authorize('own',$order);
        return view('orders.show',[
            'order' => $order->load(['items.product','items.productSku'])
        ]);
    }

    public function received(Order $order,Request $request)
    {
        $this->authorize('own',$order);
        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED){
            throw new InvalidRequestException('发货状态不正确');
        }
        // 更新发货状态为已收到
        $order->update(['ship_status' => Order::SHIP_STATUS_RECEIVED]);
        return $order;
    }

    public function review(Order $order)
    {
        $this->authorize('own',$order);

        if (!$order->paid_at){
            throw new InvalidRequestException('该订单未支付，不可评价');
        }
        // 使用 load 方法加载关联数据，避免 N + 1 性能问题
        return view('orders.review',[
            'order' => $order->load(['items.productSku','items.product'])
        ]);
    }

    public function sendReview(Order $order,Request $request)
    {
        // 校验权限
        $this->authorize('own', $order);
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }

        if ($order->reviewed){
            throw new InvalidRequestException('该订单已评价，不可重复提交');
        }
        $reviews = $request->reviews;
        DB::transaction(function () use ($order,$reviews){
            // 遍历用户提交的数据
            foreach($reviews as $review){
                $orderItem = $order->items()->find($review['id']);
                // 保存评分和评价
                $orderItem->update([
                    'rating' => $review['rating'],
                    'review' => $review['review'],
                    'reviewed_at' => Carbon::now()
                ]);
            }
            $order->update(['reviewed' => true]);
            event(new OrderReviewd($order));
        });
        return redirect()->back();
    }

}











