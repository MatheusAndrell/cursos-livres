<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [0,'en','labels','backend.tests.fields.score_field','Passing Score'],
            [0,'en','labels','backend.tests.fields.score_field_placeholder','Min. Score to be considered as Passed'],
            [0,'en','labels','frontend.course.your_score','Gained Percentage'],
            [0,'en','labels','frontend.course.your_result','Your Result'],
            [0,'en','labels','backend.stripe.plan.stripe_expire_note','Please enter expiry ex: 1=days, 1=month, 1=week, 1=year'],
            [0,'en','labels','backend.stripe.plan.fields.expire','Expire'],
            [0,'en','labels','backend.reports.total_subscribe','Total Subscribe'],
            [0,'en','labels','backend.reports.subscribe_earning','Subscribe Earning'],
            [0,'en','labels','backend.reports.subscribed_plan','Subscribed Plan'],
            [0,'en','labels','backend.reports.fields.subscribed_name','Subscribtion Name'],
            [0,'en','labels','frontend.subscription.courses','Courses'],
            [0,'en','labels','frontend.subscription.bundles','Bundles'],
            [0,'en','labels','backend.stripe.plan.fields.select_course','Select Courses'],
            [0,'en','labels','backend.stripe.plan.fields.select_bundles','Select Bundles']
        ];
        foreach ($data as $item){
            DB::table('ltm_translations')->insert([
                'status'=>$item[0],
                'locale'=>$item[1],
                'group'=>$item[2],
                'key'=>$item[3],
                'value'=>$item[4],
                'created_at'=>Carbon::now(),
                'updated_at'=>Carbon::now(),
            ]);
        }
    }
}
