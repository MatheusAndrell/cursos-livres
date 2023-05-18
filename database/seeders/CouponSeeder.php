<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $coupons = [
            [
                'name' => "Extra 10% Off",
                'description' => "Valid on all products. Valid till 18th September 2021.",
                'code' => "SAVE10",
                'type' => 1,
                'amount' => 10,
                'min_price' => 100,
                'expires_at' => now()->addDays(5),
                'per_user_limit' => 1,
                'status' => 1,
            ],
            [
                'name' => "Extra $100 Off",
                'description' => "Valid on all products. Valid till 18th September 2021.",
                'code' => "FLAT100",
                'type' => 2,
                'amount' => 100,
                'min_price' => 105,
                'expires_at' => now()->addDays(5),
                'per_user_limit' => 1,
                'status' => 1,
            ]
        ];
        Coupon::query()->truncate();
        foreach ($coupons as $coupon)
            Coupon::query()->create($coupon);
    }
}
