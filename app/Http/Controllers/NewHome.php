<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewHome extends Controller
{
    public function ShowHome(){
        return view('pages.NewHome');
    }
}
