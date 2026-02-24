<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeCfp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $req = DB::table('role_users')
            ->select('role_id', 'user_id')
            ->where('user_id', Auth::user()->id)
            ->where('hasRole', 1)
            ->get();

        foreach($req as $r){
            if($r->role_id == 8 || $r->role_id == 3){
                return $next($request);
            }else{
                abort(403);
            }
        }
    }
}
