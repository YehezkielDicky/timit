<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $auth = $request->session()->get('auth_user');
        $role = $auth['role'] ?? null;

        if (!$auth || ($roles && !in_array($role, $roles, true))) {
            return redirect()->route('admin.login')->withErrors(['msg' => 'Akses ditolak.']);
        }
        return $next($request);
    }
}
