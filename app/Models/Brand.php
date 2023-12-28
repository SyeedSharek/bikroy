<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getCat()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }


    public function getSubCat()
    {
        return $this->belongsTo(SubCategory::class, 'subcategory_id');
    }
}
