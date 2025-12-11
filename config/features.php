<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Define feature toggles for safe rollouts and A/B testing
    |
    | Naming convention: <domain>_<feature>_<date>
    | Example: visa_new_flow_20250101
    |
    | Lifecycle:
    | 1. Create flag with 0% rollout
    | 2. Test with user whitelist
    | 3. Gradually increase rollout (5%, 25%, 50%, 100%)
    | 4. Remove flag after 30 days of 100% rollout
    |
    */

    // Example: New visa application flow (short-lived)
    'visa_new_flow_20250101' => [
        'enabled' => true,
        'rollout_percentage' => 25, // Start at 25%
        'user_whitelist' => [], // Test users
        'description' => 'New visa application flow with improved UX',
        'created_at' => '2025-01-01',
        'remove_after' => '2025-02-01', // Remove 30 days after 100% rollout
    ],

    // Example: Payment provider switch
    'payment_stripe_v2_20250103' => [
        'enabled' => true,
        'rollout_percentage' => 10,
        'user_whitelist' => [1, 2, 3], // Admin users for testing
        'description' => 'Migrate to Stripe V2 API',
        'created_at' => '2025-01-03',
    ],

    // Example: New dashboard design
    'dashboard_redesign_20250105' => [
        'enabled' => false, // Not yet ready
        'rollout_percentage' => 0,
        'description' => 'New dashboard with charts and analytics',
        'created_at' => '2025-01-05',
    ],

    // Example: AI-powered document analysis
    'ai_document_analysis_20250107' => [
        'enabled' => true,
        'rollout_percentage' => 5, // Very gradual rollout
        'user_whitelist' => [],
        'description' => 'AI-powered document verification',
        'created_at' => '2025-01-07',
    ],

    // Long-lived operational flags (kill switches)
    'maintenance_mode' => [
        'enabled' => false,
        'rollout_percentage' => 0,
        'description' => 'Global maintenance mode (kill switch)',
        'long_lived' => true,
    ],

    'rate_limiting_strict' => [
        'enabled' => true,
        'rollout_percentage' => 100,
        'description' => 'Strict rate limiting for API endpoints',
        'long_lived' => true,
    ],

    'sentry_error_tracking' => [
        'enabled' => env('SENTRY_ENABLED', true),
        'rollout_percentage' => 100,
        'description' => 'Sentry error tracking and monitoring',
        'long_lived' => true,
    ],
];
