<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Bundle;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class SitemapController extends Controller
{

    public function index($file=null)
    {
        try{
            return Storage::disk('local')->get('sitemap-'.str_slug(config('app.name')).'/'.$file);
        }
        catch (\Exception $e){
            abort(404);
        }
    }


    public function getIndex(){
        $course = Course::select('id')->count();
        $bundle = Bundle::select('id')->count();
        $blog = Blog::select('id')->count();

        return view('admin.generate-sitemap');
    }




}
