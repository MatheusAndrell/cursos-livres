<?php

namespace App\Models\Stripe;

use App\Models\Course;
use Illuminate\Database\Eloquent\Model;
class SubscribeCourse extends Model
{
    protected $fillable = ['stripe_id', 'course_id','created_at'];

    public function course()
    {
        return $this->hasOne(Course::class,'id','course_id');
    }
}
