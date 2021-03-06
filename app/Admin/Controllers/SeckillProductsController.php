<?php
namespace App\Admin\Controllers;

use App\Models\Product;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Models\ProductSku;

class SeckillProductsController extends CommonProductsController
{
    public function getProductType()
    {
        return Product::TYPE_SECKILL;
    }
    protected function customGrid(Grid $grid)
    {
        $grid->id('ID')->sortable();
        $grid->title('商品名称');
        $grid->on_sale('已上架')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->price('价格');
        $grid->column('seckill.start_at', '开始时间');
        $grid->column('seckill.end_at', '结束时间');
        $grid->sold_count('销量');
    }

    protected function customForm(Form $form)
    {
        // TODO: Implement customForm() method.
        $form->datetime('seckill.start_at','秒杀开始时间')->rules('required|date');
        $form->datetime('seckill.end_at', '秒杀结束时间')->rules('required|date');
         // 当商品表单保存完毕时触发
         $form->saved(function(Form $form){

             $product = $form->model();
             // 商品重新加载秒杀字段
             $product->load(['seckill']);

            $diff = $product->seckill->end_at->getTimestamp() - time();
             collect($form->input('skus'))->each(function($sku) use($product,$diff,$form){

                if($product->on_sale && $diff > 0){
                    Log::info('seckill_sku_'.$sku['id'].': ');
                    Log::info($sku['stock']);
                    // 将剩余库存写入到 Redis 中，并设置该值过期时间为秒杀截止时间
                     Redis::setex('seckill_sku_'.$sku['id'],$diff,$sku['stock']);
                     Log::info(Redis::get('seckill_sku_'.$sku['id']));
                } else {
                    // 否则将该 SKU 的库存值从 Redis 中删除
                    Redis::del('seckill_sku_'.$sku['id']);
                }
             });

         });
    }
}