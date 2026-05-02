<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Enums\CompanyStatus;

class CheckCompanyStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Eğer süper admin ise kısıtlama yok
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        // Kullanıcının firması var ve askıya alınmışsa API erişimini kes
        if ($user && $user->company && $user->company->status === CompanyStatus::Suspended) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'company_suspended',
                    'error' => 'Your company account has been suspended.'
                ], 403);
            }
            abort(403, 'Firmanızın lisansı askıya alınmıştır.');
        }

        return $next($request);
    }
}
