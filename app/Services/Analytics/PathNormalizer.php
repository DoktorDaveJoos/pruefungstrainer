<?php

namespace App\Services\Analytics;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PathNormalizer
{
    public function forRequest(Request $request): string
    {
        $route = $request->route();

        $path = $route !== null
            ? '/'.ltrim($route->uri(), '/')
            : '/'.ltrim($request->path(), '/');

        return Str::limit($path, 512, '');
    }
}
