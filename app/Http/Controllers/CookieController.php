<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CookieController extends Controller
{
    public function setCookie(){
        $response = response('Test Cookie');
        $response->withCookie('naveen','Pankaj',60);
        
        return $response;
     }
     
    public function getCookie(){
        return request()->cookie('name');
    }
    public function deleteCookie(){
        $response = response('Cookie Deleted');
        $response->withCookie(cookie()->forget('naveen'));
    
        return $response;
    }
}
