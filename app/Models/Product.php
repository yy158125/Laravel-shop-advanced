<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    const TYPE_SECKILL = 'seckill';

    public static $typeMap = [
        self::TYPE_NORMAL  => '普通商品',
        self::TYPE_CROWDFUNDING => '众筹商品',
        self::TYPE_SECKILL => '秒杀商品',
    ];
    protected $fillable = [
        'title', 'description', 'image', 'on_sale','type', 'rating', 'sold_count', 'review_count', 'price','long_title'
    ];

    protected $casts = [
        'on_sale' => 'boolean'
    ];

    public function getImageUrlAttribute(){

        if(Str::startsWith($this->attributes['image'],['http://','https://'])){
            return $this->attributes['image'];
        }
        return Storage::disk('public')->url($this->attributes['image']);
    }

    // 与商品SKU关联
    public function skus(){
        return $this->hasMany(ProductSku::class);
    }
    // 与分类关联
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    // 与属性关联
    public function properties(){
        return $this->hasMany(ProductProperty::class);
    }
    //
    public function crowdfunding()
    {
        return $this->hasOne(CrowdfundingProduct::class);
    }
    // 秒杀
    public function seckill()
    {
        return $this->hasOne(SeckillProduct::class);
    }
    public function getGroupedPropertiesAttribute()
    {
        return $this->properties
            ->groupBy('name')
            ->map(function ($properties){
                return $properties->pluck('value')->all();
            });
    }
    public function toESArray()
    {
        $arr = array_only($this->toArray(),[
            'id',
            'title',
            'type',
            'category_id',
            'long_title',
            'on_sale',
            'rating',
            'sold_count',
            'review_count',
            'price',
        ]);
        $arr['category'] = $this->category ? explode(' - ', $this->category->full_name) : '';
        // 类目的 path 字段
        $arr['category_path'] = $this->category ? $this->category->path : '';
        // strip_tags 函数可以将 html 标签去除
        $arr['description'] = strip_tags($this->description);
        $arr['skus'] = $this->skus->map(function (ProductSku $sku){
            return array_only($sku->toArray(),['title', 'description', 'price']);
        });
        // 只取出需要的商品属性字段
        $arr['properties'] = $this->properties->map(function (ProductProperty $property) {
            return array_only($property->toArray(), ['name', 'value']);
        });
        return $arr;
    }

}
