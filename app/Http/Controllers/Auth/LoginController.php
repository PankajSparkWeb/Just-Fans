<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\AuthServiceProvider;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Session;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->redirectTo = route('feed');
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request)
    {
        // Handling 2FA stuff
              // Handling 2FA stuff
              $force2FA = false;
              if (getSetting('security.enable_2fa')) {
                  if (Auth::user()->enable_2fa && !in_array(AuthServiceProvider::generate2FaDeviceSignature(), AuthServiceProvider::getUserDevices(Auth::user()->id))) {
                      AuthServiceProvider::generate2FACode();
                      AuthServiceProvider::addNewUserDevice(Auth::user()->id);
                      $force2FA = true;
                  }
              }
              Session::put('force2fa', $force2FA);
      
              return response()->json(['success' => true, 'message' => 'Logged in successfully.']);
          }
      
          // New function to check blocked user
          protected function checkBlockedUser($user)
          {
              // Check if the user is blocked
              if ($user->status == 'blocked') {
                  Auth::logout();
                  session()->flash('error', 'Restricted User.'); 
                  return redirect()->back();
              }
          }
          
          
      

          public function login(Request $request)
          {
              $credentials = $request->only('email', 'password');
          
              if (Auth::attempt($credentials)) {
                  // Check user status
                  $this->checkBlockedUser(auth()->user());
          
                  // User is not blocked, proceed with login
                  return redirect()->intended('feed');
              }
          
              return redirect()->back()->with('error', 'Invalid login credentials.')
                  ->withInput($request->only('email', 'password')); // Include both email and password in the input data.
          }
          
    /**
     * Redirect the user to the Facebook authentication page.
     */
    public function redirectToProvider(Request $request)
    {
        return Socialite::driver($request->route('provider'))->redirect();
    }

    /**
     * Obtain the user information from Facebook.
     */
    public function handleProviderCallback(Request $request)
    {
        $provider = $request->route('provider');

        try {
            $user = Socialite::driver($provider)->user();
        } catch (RequestException $e) {
            throw new \ErrorException($e->getMessage());
        }

        // Creating the user & Logging in the user
        $userCheck = User::where('auth_provider_id', $user->id)->first();
        if($userCheck){
            $authUser = $userCheck;
        }
        else{
            try {
                $authUser = AuthServiceProvider::createUser([
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'auth_provider' => $provider,
                    'auth_provider_id' => $user->id
                ]);
            }
            catch (\Exception $exception) {
                // Redirect to homepage with error
                return redirect(route('feed'))->with('error', $exception->getMessage());
            }

        }

        Auth::login($authUser, true);
        $redirectTo = route('feed');
        if (Session::has('lastProfileUrl')) {
            $redirectTo = Session::get('feed');
        }
        return redirect($redirectTo);

    }

}