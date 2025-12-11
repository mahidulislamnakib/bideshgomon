<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdController extends Controller
{
    /**
     * Fetch ad for display
     */
    public function fetch(Request $request)
    {
        $request->validate([
            'placement' => 'required|in:sidebar,inline,empty_state,banner,sticky_bottom,modal',
            'page' => 'required|string',
            'device_type' => 'nullable|in:desktop,tablet,mobile',
        ]);

        $placement = $request->input('placement');
        $page = $request->input('page');
        $deviceType = $request->input('device_type', $this->detectDeviceType($request));
        $userRole = Auth::check() ? Auth::user()->role : null;

        // Get ad based on targeting criteria
        $ad = Ad::getForDisplay($placement, $page, $userRole, $deviceType);

        if (! $ad) {
            return response()->json([
                'ad' => null,
                'message' => 'No ad available for this placement',
            ]);
        }

        return response()->json([
            'ad' => [
                'id' => $ad->id,
                'title' => $ad->title,
                'body' => $ad->body,
                'image_url' => $ad->image_url,
                'cta_url' => $ad->cta_url,
                'cta_text' => $ad->cta_text,
                'placement' => $ad->placement,
            ],
        ]);
    }

    /**
     * Record impression
     */
    public function impression(Request $request)
    {
        $request->validate([
            'ad_id' => 'required|exists:ads,id',
            'page' => 'nullable|string',
            'device_type' => 'nullable|in:desktop,tablet,mobile',
        ]);

        $ad = Ad::find($request->input('ad_id'));

        if (! $ad) {
            return response()->json(['message' => 'Ad not found'], 404);
        }

        $userId = Auth::id();
        $page = $request->input('page');
        $deviceType = $request->input('device_type', $this->detectDeviceType($request));

        $ad->recordImpression($userId, $page, $deviceType);

        return response()->json([
            'message' => 'Impression recorded',
            'total_impressions' => $ad->impressions,
        ]);
    }

    /**
     * Record click
     */
    public function click(Request $request)
    {
        $request->validate([
            'ad_id' => 'required|exists:ads,id',
            'page' => 'nullable|string',
            'device_type' => 'nullable|in:desktop,tablet,mobile',
        ]);

        $ad = Ad::find($request->input('ad_id'));

        if (! $ad) {
            return response()->json(['message' => 'Ad not found'], 404);
        }

        $userId = Auth::id();
        $page = $request->input('page');
        $deviceType = $request->input('device_type', $this->detectDeviceType($request));

        $ad->recordClick($userId, $page, $deviceType);

        return response()->json([
            'message' => 'Click recorded',
            'total_clicks' => $ad->clicks,
            'ctr' => $ad->ctr,
        ]);
    }

    /**
     * Detect device type from user agent
     */
    protected function detectDeviceType(Request $request): string
    {
        $userAgent = $request->userAgent();

        if (preg_match('/mobile|android|iphone|ipad|ipod/i', $userAgent)) {
            if (preg_match('/ipad|tablet/i', $userAgent)) {
                return 'tablet';
            }

            return 'mobile';
        }

        return 'desktop';
    }
}
