<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AuthAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::check())
            {
                if(Auth::user()->utype === 'ADM')
                {
                    return $next($request);
                }

                if(Auth::user()->utype === 'ADMM' && strtolower(Auth::user()->email) === 'admint@lvtn.vn')
                {
                    $allowedRoutes = [
                        'admin.index',
                        'admin.orders',
                        'admin.order.details',
                        'admin.staff',
                        'admin.staff.add',
                        'admin.staff.store',
                        'admin.staff.edit',
                        'admin.staff.update',
                        'admin.staff.delete',
                        'admin.customers',
                    ];

                    if(in_array($request->route()?->getName(), $allowedRoutes, true)) {
                        return $next($request);
                    }

                    return redirect()->route('admin.index');
                }
                else
                {
                    Session::flush();
                    return redirect()->route('login');
                }
            }
            else
            {
                return redirect()->route('login');
            }
        
    }
}
