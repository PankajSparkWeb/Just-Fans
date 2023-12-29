<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    /**
     * Impersonate admin as user and act like him in the website
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function impersonate(Request $request){
        $userId = $request->route('id');
        try{
            $currentUserId = Auth::user()->id;
            Auth::loginUsingId($userId);

            Session::push('previousUserId', $currentUserId);
            if(!Session::get('impersonated')) {
                Session::push('impersonated', true);
            }
        } catch (\Exception $exception){
            return Redirect::route('voyager.users.index');
        }
        return Redirect::route('feed');
    }

    /**
     * Leave impersonation and return to admin user
     * @param Request $request
     * @return string
     */
    public function leaveImpersonation(Request $request) {
        $previousUserId = Session::get('previousUserId');
        try{
            Auth::loginUsingId($previousUserId);
            Session::remove('previousUserId');
            Session::remove('impersonated');
        } catch (\Exception $exception) {
            return Redirect::route('feed');
        }

        return Redirect::route('voyager.users.index');
    }
}
