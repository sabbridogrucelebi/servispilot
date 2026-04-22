<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403, 'Yetkisiz erişim.');
        }

        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Bu alana sadece platform yöneticisi erişebilir.');
        }

        return $next($request);
    }
}
