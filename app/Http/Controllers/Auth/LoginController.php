<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
    public function redirectTo(){
        $req = DB::table('role_users')
            ->select('role_id', 'user_id')
            ->where('hasRole', '=', 1)
            ->where('user_id', '=', Auth::user()->id)
            ->get();

        foreach($req as $r){
            if($r->role_id == 1){
                return 'homeAdmin';
            }elseif($r->role_id == 3 || $r->role_id == 8){
                return 'home';
            }elseif($r->role_id == 6){   
                return 'home-etp';
            }elseif($r->role_id == 4){
                return 'homeEmp';
            }elseif($r->role_id == 5){
                return 'homeForm';
            }elseif($r->role_id == 7){
                return 'homeFormInterne';
            }elseif($r->role_id == 10){
                return route('homeParticulier');      
            }else{
                return "login";
            }
        }
    }
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
