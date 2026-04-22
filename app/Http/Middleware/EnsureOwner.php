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
        $ownerEmails = (array) config('app.owner_emails', []);

        if ($user === null || ! in_array($user->email, $ownerEmails, true)) {
            abort(404);
        }

        return $next($request);
    }
}
