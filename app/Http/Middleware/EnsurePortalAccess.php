<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $administrative = $request->is('admin', 'admin/*', 'staff', 'staff/*', 'commission', 'commission/*')
            || $request->routeIs('admin.*', 'staff.*', 'commission.*');
        $user = $request->user();

        if ($administrative) {
            if (! $user) {
                throw new AuthenticationException;
            }

            abort_unless($user->canAccessAdminPortal(), 403, 'This page is restricted to parish staff.');
        }

        $response = $next($request);
        if ($user && str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            // A previous account's portal must not be reused from the browser's HTTP cache.
            $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        }

        return $response;
    }
}
