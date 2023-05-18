<?php

namespace App\Console\Commands;

use App\Models\TeacherProfile;
use Illuminate\Console\Command;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class StoreOauthClientsTableData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store:oauth_clients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will generate api client_id and client_secret key.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $client = Passport::client()->forceFill([
            'user_id' => 1,
            'name' => "Test API Credentials",
            'secret' => env('API_SECRET_KEY'),
            'provider' => '',
            'redirect' => '',
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => 0,
        ]);
        $client->save();
    }
}
