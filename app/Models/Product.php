<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;


    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }


    public function brand()
    {
        return $this->belongsTo(Brand::class,'brand_id');
    }

    /**
     * get related things (same category or brand)
     */
    public function getRelatedProducts($limit = 8)
    {
        return Product::where(function ($query) {
            $query->where('category_id', $this->category_id)
                  ->orWhere('brand_id', $this->brand_id);
        })
        ->where('id', '<>', $this->id)
        ->inRandomOrder()
        ->limit($limit)
        ->get();
    }
}
