<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $auth = $request->session()->get('auth_user');
        $current = $auth['role'] ?? null;

        if (!$auth || !$current || (!empty($roles) && !in_array($current, $roles, true))) {
            return redirect()->route('admin.login')->withErrors(['msg' => 'Akses ditolak.']);
        }

        return $next($request);
    }
}
