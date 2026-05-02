<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddPermissionsUpdatedHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (auth()->check() && auth()->user()->permissions_updated_at) {
            $response->headers->set('X-Permissions-Updated-At', auth()->user()->permissions_updated_at->timestamp);
        }

        return $response;
    }
}
