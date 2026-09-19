<?php

namespace App\Http\Middleware;

use App\Services\WebsiteAnalytics;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordWebsiteActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $analytics = app(WebsiteAnalytics::class);
        if ($request->is('/') && $request->isMethod('GET')) {
            $analytics->prepare($request);
        }
        $response = $next($request);
        $route = $request->route()?->getName() ?? '';

        if ($request->isMethod('GET') && $response->getStatusCode() === 200
            && str_contains($response->headers->get('Content-Type', ''), 'text/html')
            && $request->user() && $route && ! str_starts_with($route, 'admin.analytics')) {
            $analytics->record($request, 'page_view', $route);
        }

        // Only successful submissions, not validation failures, polling, or message contents.
        if (! $request->isMethod('GET') && isset(WebsiteAnalytics::ACTIONS[$route])
            && $response->getStatusCode() < 400
            && ! array_intersect(['errors', 'error', 'info'], $request->session()->get('_flash.new', []))) {
            $analytics->record($request, 'action', $route, $route);
        }

        return $response;
    }
}
