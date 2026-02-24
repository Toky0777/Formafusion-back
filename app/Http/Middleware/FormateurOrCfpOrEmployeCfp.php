<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class FormateurOrCfpOrEmployeCfp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $req = DB::table('role_users')
            ->select('role_id', 'user_id')
            ->where('user_id', Auth::user()->id)
            ->where('hasRole', 1)
            ->get();

        foreach ($req as $r) {
            // Cfp ou Formateur ou EmployeCfp
            if ($r->role_id == 8 || $r->role_id == 3 || $r->role_id == 5) {
                return $next($request);
            } else {
                abort(403);
            }
        }
    }
}
