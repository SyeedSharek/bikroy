<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionType extends Model
{
    use HasFactory;

    protected $guarded = [];
    public function subscription()
    {
        return $this->hasMany(Subscription::class);
    }
}
