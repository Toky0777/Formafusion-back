<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EmpOrParticulier
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
            // Employe ou EmployeEtp ou Particulier
            if ($r->role_id == 4 || $r->role_id == 9 || $r->role_id == 10) {
                return $next($request);
            } else {
                abort(403);
            }
        }
    }
}
