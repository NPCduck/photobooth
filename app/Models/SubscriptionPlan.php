<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'is_active',
        'stripe_plan_id',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
