<?php

namespace App\Services\Analytics;

use App\Models\TrackedEvent;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RecordEvent
{
    public function __construct(
        private readonly VisitorHash $visitorHash,
        private readonly Guard $auth,
    ) {}

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function record(string $name, ?array $metadata = null, bool $includeVisitorHash = true, ?int $userId = null): void
    {
        TrackedEvent::create([
            'name' => $name,
            'visitor_hash' => $includeVisitorHash ? $this->resolveVisitorHash() : null,
            'user_id' => $userId ?? $this->auth->id(),
            'metadata' => $metadata,
            'created_at' => Carbon::now(),
        ]);
    }

    private function resolveVisitorHash(): ?string
    {
        $request = app('request');

        if (! $request instanceof Request || $request->route() === null || $request->userAgent() === null) {
            return null;
        }

        return $this->visitorHash->for($request);
    }
}
