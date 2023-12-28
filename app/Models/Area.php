<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'name',
    ];

    public function getLocation()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
