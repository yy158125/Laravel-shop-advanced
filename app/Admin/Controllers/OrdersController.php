<?php

namespace App\Admin\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\HandleRefundRequest;
use App\Models\CrowdfundingProduct;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('订单列表')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show(Order $order, Content $content)
    {
        return $content
            ->header('查看订单')
            ->description('description')
            ->body(view('admin.orders.show', ['order' => $order]));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Order);

        $grid->id('Id');
        $grid->no('No');
        // 只展示已支付的订单，并且默认按支付时间倒序排序
        $grid->model()->whereNotNull('paid_at')->orderBy('paid_at', 'desc');
        $grid->no('订单流水号');
        // 展示关联关系的字段时，使用 column 方法
        $grid->column('user.name', '买家');
        $grid->total_amount('总金额')->sortable();
        $grid->paid_at('支付时间')->sortable();
        $grid->ship_status('物流')->display(function($value) {
            return Order::$shipStatusMap[$value];
        });
        $grid->refund_status('退款状态')->display(function($value) {
            return Order::$refundStatusMap[$value];
        });
        // 禁用创建按钮，后台不需要创建订单
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            // 禁用删除和编辑按钮
            $actions->disableDelete();
            $actions->disableEdit();
        });
        $grid->tools(function ($tools) {
            // 禁用批量删除按钮
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        return $grid;
    }




    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Order);

        $form->text('no', 'No');
        $form->number('user_id', 'User id');
        $form->textarea('address', 'Address');
        $form->textarea('remark', 'Remark');
        $form->decimal('total_amount', 'Total amount');
        $form->datetime('paid_at', 'Paid at')->default(date('Y-m-d H:i:s'));
        $form->text('payment_method', 'Payment method');
        $form->text('payment_no', 'Payment no');
        $form->text('refund_status', 'Refund status')->default('pending');
        $form->text('refund_no', 'Refund no');
        $form->switch('closed', 'Closed');
        $form->switch('reviewed', 'Reviewed');
        $form->text('ship_status', 'Ship status')->default('pending');
        $form->textarea('ship_data', 'Ship data');
        $form->textarea('extra', 'Extra');

        return $form;
    }

    public function ship(Order $order,Request $request)
    {
        if (!$order->paid_at){
            throw new InvalidRequestException('该订单未付款');
        }
        // 判断当前订单发货状态是否为未发货
        if ($order->ship_status !== Order::SHIP_STATUS_PENDING){
            throw new InvalidRequestException('该订单已发货');
        }
        if ($order->type === Order::TYPE_CROWDFUNDING && $order->$order->crowdfunding_status !== CrowdfundingProduct::STATUS_SUCCESS){
            throw new InvalidRequestException('众筹订单只能在众筹成功之后发货');
        }
        // Laravel 5.5 之后 validate 方法可以返回校验过的值
        $data = $this->validate($request,[
            'express_company' => ['required'],
            'express_no'      => ['required'],
        ],[],[
            'express_company' => '物流公司',
            'express_no'      => '物流单号',
        ]);
        $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            'ship_data'   => $data
        ]);
        return redirect()->back();
    }

    public function handleRefund(Order $order,HandleRefundRequest $request)
    {
        if ($order->refund_status !== Order::REFUND_STATUS_APPLIED){
            throw new InvalidRequestException('订单状态不正确');
        }
        if ($request->agree){
            // 同意退款的逻辑这里先留空
            // TODO
        }else{
            $extra = $order ?: [];
            $extra['refund_disagree_reason'] = $request->reason;
            $order->update([
                'refund_status' => Order::REFUND_STATUS_PENDING,
                'extra'         => $extra,
            ]);
        }

    }
}













