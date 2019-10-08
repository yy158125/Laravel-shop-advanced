<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['name','is_directory','level','path','parent_id'];

    protected $casts = [
        'is_directory' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot(); // TODO: Change the autogenerated stub
        // 监听 Category 的创建事件，用于初始化 path 和 level 字段值
        static::creating(function (Category $category){
            if(is_null($category->parent_id)){
                $category->parent_id = 0;
                $category->level = 0;
                $category->path = '-';
            }else{
                // 将层级设为父类目的层级 + 1
                $category->level = $category->parent->level + 1;
                $category->path = $category->parent->path . $category->parent_id . '-';
            }
        });
    }

    public function parent(){
        return $this->belongsTo(Category::class);
    }
    public function children(){
        return $this->hasMany(Category::class,'parent_id');
    }
    public function products(){
        return $this->hasMany(Product::class);
    }
    // 定一个一个访问器，获取所有祖先类目的 ID 值
    public function getPathIdsAttribute(){
        $path = trim($this->path,'-');
        return array_filter(explode('-',$path));
    }
    // 获取所有祖先类目并按层级排序
    public function getAncestorsAttribute()
    {
        return Category::query()
            ->whereIn('id',$this->path_ids)
            ->orderBy('level')
            ->get();
    }
    // 获取以 - 为分隔的所有祖先类目名称以及当前类目的名称
    public function getFullNameAttribute()
    {
        return $this->ancestors
            ->pluck('name')
            ->push($this->name)
            ->implode(' - ');
    }

}
