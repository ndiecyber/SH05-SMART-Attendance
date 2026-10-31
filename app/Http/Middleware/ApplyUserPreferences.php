<?php

namespace App\Http\Middleware;

use App\Models\UserPreference;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyUserPreferences
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $user->load('preference');
            $preferences = array_merge(
                UserPreference::DEFAULT_SETTINGS,
                $user->preference?->settings ?? [],
            );

            app()->setLocale($preferences['language']);
            config(['app.timezone' => $preferences['timezone']]);
            date_default_timezone_set($preferences['timezone']);
        }

        return $next($request);
    }
}
