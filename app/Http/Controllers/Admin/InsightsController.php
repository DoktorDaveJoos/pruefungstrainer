<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\InsightsQuery;
use Inertia\Inertia;
use Inertia\Response;

class InsightsController extends Controller
{
    public function __invoke(InsightsQuery $query): Response
    {
        return Inertia::render('admin/insights', [
            'overview' => $query->overview(),
            'daily' => $query->daily(),
            'funnel' => $query->funnel(),
            'topPages' => $query->topPages(),
            'topReferrers' => $query->topReferrers(),
        ]);
    }
}
