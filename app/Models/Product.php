<?php

namespace App\Models;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use Coderflex\Laravisit\Concerns\CanVisit;
use Coderflex\Laravisit\Concerns\HasVisits;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model implements CanVisit
{

    use HasFactory, HasVisits;

    protected $guarded = [];
    public function scopeSearch($query, $search)
    {
        return $query->where('status', true)
            ->where('is_sold', false)
            ->where(function ($query) use ($search) {
                $query->where('title', 'like', "%$search%")
                    ->orWhereHas('get_category', function ($query) use ($search) {
                        $query->where('category_name', 'like', "%$search%");
                    })
                    ->orWhereHas('get_subcategory', function ($query) use ($search) {
                        $query->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('get_brand', function ($query) use ($search) {
                        $query->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('get_location', function ($query) use ($search) {
                        $query->where('name', 'like', "%$search%");
                    })
                    ->orWhereHas('get_area', function ($query) use ($search) {
                        $query->where('name', 'like', "%$search%");
                    });
            });
    }
    public function get_category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function get_subcategory()
    {
        return $this->belongsTo(SubCategory::class, 'subcategory_id');
    }
    public function get_brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
    public function get_location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
    public function get_area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
    public function get_user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getImagesAttribute(string $images)
    {
        return json_decode($images);
    }
    public function getCreatedAtAttribute(string $time)
    {
        return Carbon::parse($time)->diffForHumans();
    }
}
