<?php

namespace App\Http\Controllers;


use App\Http\Requests\OrderRequest;
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
}