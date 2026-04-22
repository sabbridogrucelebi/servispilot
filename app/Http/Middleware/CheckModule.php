<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModule
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (!auth()->check()) {
            abort(403, 'Yetkisiz erişim.');
        }

        $user = auth()->user();

        // Super admin tüm modüllere erişebilir
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (!$user->canAccessModule($module)) {
            abort(403, 'Bu modüle erişim izniniz bulunmuyor. Lütfen firmanızın yöneticisiyle iletişime geçin.');
        }

        return $next($request);
    }
}
