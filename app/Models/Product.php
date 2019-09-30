<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'title', 'description', 'image', 'on_sale',
        'rating', 'sold_count', 'review_count', 'price'
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
}
