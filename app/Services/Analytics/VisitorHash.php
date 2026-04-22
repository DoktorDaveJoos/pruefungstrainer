<?php

namespace App\Services\Analytics;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VisitorHash
{
    public function for(Request $request): string
    {
        $ip = $request->ip() ?? '0.0.0.0';
        $ua = (string) $request->userAgent();
        $day = Carbon::now()->toDateString();
        $salt = (string) config('app.key');

        return hash('sha256', $ip.'|'.$ua.'|'.$day.'|'.$salt);
    }
}
