<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewPopular extends Controller
{
    public function show(){
        return view('pages.NewPopular');
    }
}
