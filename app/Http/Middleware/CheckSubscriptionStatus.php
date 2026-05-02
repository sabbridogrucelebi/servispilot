<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckSubscriptionStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Super Admin ise her şeyi görebilir
        if ($user && $user->role === 'super_admin') {
            return $next($request);
        }

        // Giriş yapmamışsa veya firma yoksa (normal auth middleware halleder ama güvenlik için)
        if (!$user || !$user->company_id) {
            return $next($request);
        }

        $company = $user->company;

        // Firmanın aktif bir aboneliği var mı kontrol et
        $activeSubscription = $company->subscriptions()
            ->where('status', 'active')
            ->where(function($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
            })
            ->first();

        // Eğer abonelik yoksa ve şu an faturalandırma sayfasında değilse yönlendir
        if (!$activeSubscription) {
            // İzin verilen rotalar (Ödeme yapabilmesi için)
            $allowedRoutes = [
                'billing.*',
                'logout',
                'support-tickets.*', // Destek alabilmeli
            ];

            if (!$request->routeIs($allowedRoutes)) {
                return redirect()->route('billing.index')->with('error', 'Kullanım süreniz dolmuştur. Lütfen devam etmek için aboneliğinizi yenileyin.');
            }
        }

        return $next($request);
    }
}
