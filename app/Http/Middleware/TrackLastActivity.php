<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastActivity
{
    /**
     * Throttle interval in seconds. We only update the DB if the last
     * recorded activity was more than this many seconds ago, to avoid
     * hammering the database on every single request.
     */
    protected int $throttleSeconds = 120; // 2 minutes

    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $lastActive = $user->last_active_at;

            // Only write to DB if null or older than throttle interval
            if (! $lastActive || $lastActive->diffInSeconds(now()) >= $this->throttleSeconds) {
                $user->forceFill(['last_active_at' => now()])->saveQuietly();
            }
        }

        return $next($request);
    }
}

