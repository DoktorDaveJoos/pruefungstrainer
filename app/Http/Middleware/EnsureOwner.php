<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $ownerEmail = config('app.owner_email');

        if ($user === null || $user->email !== $ownerEmail) {
            abort(404);
        }

        return $next($request);
    }
}
