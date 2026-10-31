<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $storedUser = $user === null ? null : User::query()->find($user->getAuthIdentifier());

        abort_unless($storedUser !== null && in_array($storedUser->role, $roles, true) && (bool) $storedUser->is_active, 403);

        $user->setAttribute('role', $storedUser->role);
        $user->setAttribute('is_active', $storedUser->is_active);

        return $next($request);
    }
}
