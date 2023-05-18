<?php

namespace Database\Seeders;

use App\Models\MenuItems;
use App\Models\Menus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $nav_menu = Menus::firstOrCreate([
            'name' => 'nav-menu'
        ]);
        $menus = [
            [
                'url' => route('blogs.index'),
                'name' => 'Blog'
            ],
            [
                'url' => route('courses.all'),
                'name' => 'Courses'
            ],
            [
                'url' => route('bundles.all'),
                'name' => 'Bundles'
            ],
            [
                'url' => asset('forum'),
                'name' => 'Forums'
            ],
            [
                'url' => asset('contact'),
                'name' => 'Contact'
            ],
            [
                'url' => asset('about-us'),
                'name' => 'About Us'
            ]
        ];
        foreach ($menus as $key => $item){
            $menuItem = new MenuItems();
            $menuItem->label = $item['name'];
            $menuItem->link = \Illuminate\Support\Arr::last(explode('/', $item['url']));
            $menuItem->parent = 0;
            $menuItem->sort = $key;
            $menuItem->menu = $nav_menu->id;
            $menuItem->depth = 0;
            $menuItem->save();
            $menuItem->parent = $menuItem->id;
            $menuItem->save();
        }
    }
}
