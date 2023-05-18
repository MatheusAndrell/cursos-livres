<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function obtenhaBusiness()
    {
        return view('pages.obtenha-business');
    }

    public function saibaMais()
    {
        return view('pages.saiba-mais');
    }
}
