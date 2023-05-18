<?php

namespace App\Models\Stripe;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StripePlan extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['plan_id', 'product', 'name', 'description', 'amount', 'currency','expire','recurring', 'interval', 'course', 'bundle'];

    public function subcribeCourses()
    {
        return $this->hasMany(SubscribeCourse::class,'stripe_id','id');
    }
    public function subcribeBundle()
    {
        return $this->hasMany(SubscribeBundle::class,'stripe_id','id');
    }
}
