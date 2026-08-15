<?php

namespace Tokalink\Inermin\middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Tokalink\Inermin\helpers\Inermin;

class InerminAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Session::get('admin_id')) {
            return redirect()->to(Inermin::adminPath('login'))->with('message', 'Please login to continue');
        }

        return $next($request);
    }
}
