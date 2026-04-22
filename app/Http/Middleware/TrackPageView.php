<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Services\Analytics\PathNormalizer;
use App\Services\Analytics\VisitorHash;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrackPageView
{
    private const SKIP_PATHS = [
        'admin/insights',
        'api/*',
        'livewire/*',
        '_debugbar/*',
        'up',
        'polar/*',
        'build/*',
    ];

    private const BOT_UA_PATTERN = '/bot|crawler|spider|preview|lighthouse|headlesschrome/i';

    public function __construct(
        private readonly VisitorHash $visitorHash,
        private readonly PathNormalizer $pathNormalizer,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $this->shouldRecord($request, $response)) {
            return;
        }

        try {
            PageView::create([
                'visitor_hash' => $this->visitorHash->for($request),
                'path' => $this->pathNormalizer->forRequest($request),
                'referrer_host' => $this->referrerHost($request),
                'user_id' => $request->user()?->id,
                'created_at' => Carbon::now(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function shouldRecord(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');
        if (! str_contains(strtolower($contentType), 'text/html')) {
            return false;
        }

        if ($request->is(...self::SKIP_PATHS)) {
            return false;
        }

        $ua = (string) $request->userAgent();
        if ($ua === '' || preg_match(self::BOT_UA_PATTERN, $ua) === 1) {
            return false;
        }

        return true;
    }

    private function referrerHost(Request $request): ?string
    {
        $referrer = $request->headers->get('referer');
        if ($referrer === null) {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);
        if (! is_string($host) || $host === $request->getHost()) {
            return null;
        }

        return substr($host, 0, 255);
    }
}
