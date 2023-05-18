<?php

namespace App\Models\Stripe;

use App\Models\Bundle;
use Illuminate\Database\Eloquent\Model;

class SubscribeBundle extends Model
{
    protected $fillable = ['stripe_id', 'bundle_id','created_at'];

    public function bundle()
    {
        return $this->hasOne(Bundle::class,'id','bundle_id');
    }
}
