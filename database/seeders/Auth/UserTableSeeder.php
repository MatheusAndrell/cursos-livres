<?php

namespace Database\Seeders\Auth;

use App\Models\Auth\User;
use Database\Seeders\Traits\DisableForeignKeys;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Class UserTableSeeder.
 */
class UserTableSeeder extends Seeder
{
    use DisableForeignKeys;

    /**
     * Run the database seed.
     *
     * @return void
     */
    public function run()
    {
        $this->disableForeignKeys();

        // Add the master administrator, user id of 1
        $data = [
            [
                'first_name'        => 'Admin',
                'last_name'         => 'Istrator',
                'email'             => 'admin@lms.com',
                'password'          => 'secret',
            ],
            [
                'first_name'        => 'Teacher',
                'last_name'         => 'User',
                'email'             => 'teacher@lms.com',
                'password'          => 'secret',
            ],
            [
                'first_name'        => 'Student',
                'last_name'         => 'User',
                'email'             => 'student@lms.com',
                'password'          => 'secret',
            ],
            [
                'first_name'        => 'Normal',
                'last_name'         => 'User',
                'email'             => 'user@lms.com',
                'password'          => 'secret',
            ]
        ];
        foreach ($data as $datum) {
            $datum['confirmation_code'] = md5(uniqid(mt_rand(), true));
            $datum['confirmed'] = true;
            $existData = User::where('email', $datum['email'])->first();
            if (isset($existData)){
                $existData->update($datum);
            }else{
                User::create($datum);
            }
        }
        $this->enableForeignKeys();
    }
}
