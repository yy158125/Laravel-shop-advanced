<?php

namespace App\Models;

use App\Exceptions\InternalException;
use Illuminate\Database\Eloquent\Model;

class ProductSku extends Model
{
    protected $fillable = ['title', 'description', 'price', 'stock'];

    public function product(){
        return $this->belongsTo(Product::class);
    }
    public function decreaseStock($amount)
    {
        if ($amount < 0){
            throw new InternalException('减库存不可小于0');
        }
        // $this->newQuery() 方法来获取数据库的查询构造器，
        return $this->newQuery()->where('id',$this->id)
            ->where('stock','>=',$amount)
            ->decrement('stock',$amount);
    }
    public function addStock($amount)
    {
        if ($amount < 0){
            throw new InternalException('加库存不可小于0');
        }
        return $this->increment('stock',$amount);
    }
}
