<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ProductController extends CommonProductsController
{
    use HasResourceActions;

    public function getProductType()
    {
        // TODO: Implement getProductType() method.
        return Product::TYPE_NORMAL;
    }


    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }


    public function update($id)
    {
        return $this->form()->update($id);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */

    protected function customGrid(Grid $grid)
    {
        // TODO: Implement customGrid() method.
        $grid->model()->with(['category']);
        $grid->id('ID')->sortable();
        $grid->title('商品名称');
        $grid->column('category.name', '类目');
        $grid->on_sale('已上架')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->rating('评分');
        $grid->sold_count('销量');
        $grid->review_count('评论数');
        $grid->price('价格');
    }

    protected function customForm(Form $form)
    {
        // TODO: Implement customForm() method.
    }


}
