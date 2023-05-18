<?php

namespace Database\Seeders;

use App\Models\Config;
use App\Models\MenuItems;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class V544Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [0, 'en', 'labels', 'backend.menu-manager.header_text', 'Menu Structure'],
            [0, 'en', 'labels', 'backend.menu-manager.instruction_text', 'Place each item in the order you prefer. Click on the arrow to the right of the item to display more configuration options.'],
            [0, 'en', 'labels', 'backend.menu-manager.label.label', 'Label'],
            [0, 'en', 'labels', 'backend.menu-manager.label.url', 'Url'],
            [0, 'en', 'labels', 'backend.menu-manager.button.add_to_menu', 'Add to Menu'],
            [0, 'en', 'labels', 'backend.menu-manager.button.update_sort_order', 'Update Sort Order'],
            [0, 'en', 'labels', 'backend.menu-manager.button.view_details', 'View Details'],
            [0, 'en', 'labels', 'backend.menu-manager.pages_card_header_text', 'Pages'],
            [0, 'en', 'labels', 'backend.menu-manager.custom_links_card_header_text', 'Custom Links'],
            [0, 'en', 'labels', 'backend.backup.app_key', 'App Key'],
        ];
        foreach ($data as $item) {
            $dataExist = DB::table('ltm_translations')->where('locale', $item[1])->where('group', $item[2])->where('key', $item[3])->first();
            if (!isset($dataExist)) {
                DB::table('ltm_translations')->insert([
                    'status' => $item[0],
                    'locale' => $item[1],
                    'group' => $item[2],
                    'key' => $item[3],
                    'value' => $item[4],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        $config_keys = [
            'filesystems.disks.dropbox.token' => 'filesystems.disks.dropbox.authorization_token',
            'filesystems.disks.dropbox.app_secret' => 'filesystems.disks.dropbox.secret',
            'filesystems.disks.dropbox.key' => 'filesystems.disks.dropbox.key'
        ];
        foreach ($config_keys as $exist_config_key => $new_config_key) {
            Config::updateOrCreate([
                'key' => $exist_config_key
            ], [
                'key' => $new_config_key
            ]);
        }

        MenuItems::where('link', 'forums')->update(['link' => 'forum']);
        Config::where('key', 'flutter.active')->update(['value' => 0]);
    }
}
