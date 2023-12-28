<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function getSubCategory()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function getCreatedAtAttribute(string $string)
    {
        return date("d-m-Y", strtotime($string));
    }
    public function getUpdatedAtAttribute(string $string)
    {
        return date("d-m-Y", strtotime($string));
    }
}
