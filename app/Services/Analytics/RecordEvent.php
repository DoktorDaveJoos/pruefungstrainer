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
    public function record(string $name, ?array $metadata = null): void
    {
        TrackedEvent::create([
            'name' => $name,
            'visitor_hash' => $this->resolveVisitorHash(),
            'user_id' => $this->auth->id(),
            'metadata' => $metadata,
            'created_at' => Carbon::now(),
        ]);
    }

    private function resolveVisitorHash(): ?string
    {
        $request = app('request');

        if (! $request instanceof Request || $request->route() === null) {
            return null;
        }

        return $this->visitorHash->for($request);
    }
}
