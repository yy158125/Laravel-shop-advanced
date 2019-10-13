<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CategoryService;
use function foo\func;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
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
        // 如果有传入 category_id 字段，并且在数据库中有对应的类目
        if ($request->category_id && $category = Category::find($request->category_id)){
            if ($category->is_directory){
                $builder->whereHas('category',function ($query) use ($category){
                    $query->where('path','like',$category->path.$category->id.'-%');
                });
            }else{
                $builder->where('category_id',$category->id);
            }
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
            ],
            'category' => $category ?? null,
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
        // 评价
        $reviews = OrderItem::query()
            ->with(['order.user','productSku'])
            ->where('product_id',$product->id)
            ->whereNotNull('reviewed_at')
            ->orderBy('reviewed_at','desc')
            ->limit(10)
            ->get();

        return view('products.show',[
            'product' => $product,
            'favored' => $favored,
            'reviews' => $reviews
        ]);
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
