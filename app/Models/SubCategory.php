<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;
    // protected $table = 'sub_categories';

    // protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'name',
    ];

    public function getCategory()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function getBrand()
    {
        return $this->hasMany(Brand::class, 'subcategory_id');
    }
}
