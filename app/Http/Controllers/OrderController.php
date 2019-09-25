<?php

namespace App\Http\Controllers;


use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\UserAddress;
use App\Services\OrderService;
use Illuminate\Http\Request;

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

}