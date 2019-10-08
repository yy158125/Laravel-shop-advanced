<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function getCategoryTree($parentId = null,$allCategories = null)
    {
        if (is_null($allCategories)){
            $allCategories = Category::all();
        }
        return $allCategories
            ->where('parent_id',$parentId)
            ->map(function (Category $category) use ($allCategories){
                $data = ['id' => $category->id,'name' => $category->name];
                if (!$category->is_directory){
                    return $data;
                }
                // 否则递归调用本方法，将返回值放入 children 字段中
                $data['children'] = $this->getCategoryTree($category->id,$allCategories);
                return $data;
            });
    }
}
