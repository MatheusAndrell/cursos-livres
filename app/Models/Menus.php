<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menus extends Model
{
    protected $table = 'admin_menus';
    protected $fillable = ['name'];
}
