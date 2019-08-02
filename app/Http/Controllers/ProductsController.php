<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request){
        // 创建一个查询构造器
        $builder = Product::where('on_sale',true);
        // 判断是否有提交 search 参数，如果有就赋值给 $search 变量
        // search 参数用来模糊搜索商品
        if($search = $request->input('search','')){
            $like = '%'.$search.'%';
            $builder->where(function ($query) use ($like){
                $query->where('title','like',$like)
                    ->orWhere('description','like',$like)
                    ->orWhereHas('skus',function ($query) use ($like){
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }
        // 是否有提交 order 参数，如果有就赋值给 $order 变量
        // order 参数用来控制商品的排序规则
        if($order = $request->input('order','')){
            if(preg_match('/^(.+)_(asc|desc)$/',$order,$sm)){
                if(in_array($sm[1],['price', 'sold_count', 'rating'])){
                    $builder->orderBy($sm[1], $sm[2]);
                }
            }
        }

        $products = $builder->paginate(12);

        return view('products.index',[
            'products' => $products,
            'filters'  => [
                'search' => $search,
                'order' => $order
            ]
        ]);
    }

    public function show(Request $request,Product $product){


        if(!$product->on_sale){
            throw new InvalidRequestException('商品未上架');
        }
        $favored = false;
        if($user = $request->user()){
            $favored = $user->favoriteProducts()->find($product->id);
        }
        return view('products.show',['product' => $product,'favored' => $favored]);
    }

    /**
     * 收藏
     * @param Request $request
     * @param Product $product
     * @return array
     */
    public function favorite(Request $request,Product $product){
        $user = $request->user();
        if($user->favoriteProducts()->find($product->id)){
            return [];
        }

        $user->favoriteProducts()->attach($product->id);
        return [];
    }

    /**
     * 取消收藏
     * @param Request $request
     * @param Product $product
     * @return array
     */
    public function disFavorite(Request $request,Product $product){
        $user = $request->user();
        $user->favoriteProducts()->detach($product->id);
        return [];
    }
    // 收藏商品
    public function favorites(Request $request){
        $products = $request->user()
            ->favoriteProducts()
            ->paginate(16);
        return view('products.favorites',['products' => $products]);
    }
}
