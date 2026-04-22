<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            abort(403, 'Bu alana erişim yetkin yok.');
        }

        $user = auth()->user();

        if (!$user->hasPermission($permission)) {
            abort(403, 'Bu sayfayı görüntüleme yetkin bulunmuyor.');
        }

        // Gözlemci (Viewer) kontrolü: Sadece GET (görüntüleme) isteklerine izin ver
        if ($user->role === 'viewer' && !$request->isMethod('get')) {
            abort(403, 'Gözlemci yetkisiyle bu işlemi yapamazsınız. Sadece görüntüleme yetkiniz var.');
        }

        return $next($request);
    }
}