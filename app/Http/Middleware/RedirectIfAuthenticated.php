<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $req = DB::table('role_users')
                    ->select('role_id', 'user_id')
                    ->where('user_id', '=', Auth::user()->id)
                    ->where('isActive', '=', 1)
                    ->get();
                
                foreach($req as $r){
                    if($r->role_id == 3){
                        return redirect(RouteServiceProvider::HOME);
                    }elseif($r->role_id == 6){
                        return redirect(RouteServiceProvider::HOMEETP);
                    }elseif($r->role_id == 4){
                        return redirect(RouteServiceProvider::HOMEEMP);
                    }elseif($r->role_id == 5){
                        return redirect(RouteServiceProvider::HOMEFORM);
                    }elseif($r->role_id == 7){
                        return redirect(RouteServiceProvider::HOMEFORMINTERN);
                    }elseif($r->role_id == 1){
                        return redirect(RouteServiceProvider::HOMESADMIN);
                    }else{
                        abort(403);
                    }
                }
            }
        }

        return $next($request);
    }
}
