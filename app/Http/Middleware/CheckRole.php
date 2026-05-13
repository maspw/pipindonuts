<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
{
    if (auth()->check() && in_array(auth()->user()->user_group, ['Kasir', 'Admin'])) {
        return $next($request);
    }
    return redirect()->route('kasir.login')->with('error', 'Silakan login sebagai Kasir terlebih dahulu.');
}
}
