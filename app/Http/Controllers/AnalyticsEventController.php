<?php

namespace App\Http\Controllers;

use App\Services\WebsiteAnalytics;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnalyticsEventController extends Controller
{
    public function store(Request $request, WebsiteAnalytics $analytics)
    {
        $data = $request->validate([
            'event_id' => ['required', 'uuid'],
            'event_type' => ['required', Rule::in(['page_view', 'action'])],
            'page' => ['required', Rule::in(array_keys(WebsiteAnalytics::PAGES))],
            'action' => ['required_if:event_type,action', 'nullable', Rule::in(array_keys(WebsiteAnalytics::CLICKS))],
        ]);
        $analytics->record($request, $data['event_type'], $data['page'],
            $data['event_type'] === 'action' ? $data['action'] : null, eventId: $data['event_id'], area: 'website');

        return response()->noContent();
    }
}
