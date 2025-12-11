// Sentry configuration for Vue/Vite
import * as Sentry from '@sentry/vue'
import { BrowserTracing } from '@sentry/tracing'
import type { App } from 'vue'
import type { Router } from 'vue-router'

interface SentryConfig {
    dsn: string
    environment: string
    release?: string
    tracesSampleRate?: number
    replaysSessionSampleRate?: number
    replaysOnErrorSampleRate?: number
}

export function initSentry(app: App, router: Router, config: SentryConfig) {
    // Skip initialization if DSN not provided or in local development
    if (!config.dsn || config.environment === 'local') {
        console.log('Sentry: Skipping initialization (local environment)')
        return
    }

    Sentry.init({
        app,
        dsn: config.dsn,
        environment: config.environment,
        release: config.release,

        // Performance monitoring
        integrations: [
            new BrowserTracing({
                routingInstrumentation: Sentry.vueRouterInstrumentation(router),
                tracePropagationTargets: [
                    'localhost',
                    /^https:\/\/bideshgomon\.com/,
                    /^https:\/\/api\.bideshgomon\.com/,
                ],
            }),
            
            // Session replay
            new Sentry.Replay({
                maskAllText: true,
                blockAllMedia: true,
            }),
        ],

        // Performance monitoring sample rate (0.0 to 1.0)
        tracesSampleRate: config.tracesSampleRate ?? 0.1,

        // Session replay sample rates
        replaysSessionSampleRate: config.replaysSessionSampleRate ?? 0.1,
        replaysOnErrorSampleRate: config.replaysOnErrorSampleRate ?? 1.0,

        // Error filtering
        ignoreErrors: [
            // Browser extensions
            'top.GLOBALS',
            'canvas.contentDocument',
            'MyApp_RemoveAllHighlights',
            'atomicFindClose',
            
            // Network errors
            'NetworkError',
            'Network request failed',
            'Failed to fetch',
            
            // AbortController
            'AbortError',
            'The user aborted a request',
            
            // ResizeObserver
            'ResizeObserver loop limit exceeded',
        ],

        // Normalize URLs to remove query strings with PII
        beforeSend(event, hint) {
            // Remove query strings from URLs
            if (event.request?.url) {
                event.request.url = event.request.url.split('?')[0]
            }

            // Scrub sensitive data from breadcrumbs
            if (event.breadcrumbs) {
                event.breadcrumbs = event.breadcrumbs.map(breadcrumb => {
                    if (breadcrumb.data) {
                        // Remove potential PII from XHR/fetch breadcrumbs
                        if (breadcrumb.category === 'xhr' || breadcrumb.category === 'fetch') {
                            delete breadcrumb.data.email
                            delete breadcrumb.data.password
                            delete breadcrumb.data.token
                            delete breadcrumb.data.api_key
                        }
                    }
                    return breadcrumb
                })
            }

            return event
        },

        // Context enrichment
        beforeBreadcrumb(breadcrumb, hint) {
            // Don't log console.log breadcrumbs in production
            if (breadcrumb.category === 'console' && config.environment === 'production') {
                return null
            }
            return breadcrumb
        },
    })

    // Set user context
    if (window.user) {
        Sentry.setUser({
            id: window.user.id,
            username: window.user.name,
            email: config.environment !== 'production' ? window.user.email : undefined,
        })
    }

    // Set custom tags
    Sentry.setTags({
        'app.version': config.release || 'unknown',
        'app.environment': config.environment,
    })

    console.log(`✅ Sentry initialized (${config.environment})`)
}

// Manually capture exceptions
export function captureException(error: Error, context?: Record<string, any>) {
    Sentry.captureException(error, {
        contexts: context ? { custom: context } : undefined,
    })
}

// Manually capture messages
export function captureMessage(message: string, level: Sentry.SeverityLevel = 'info', context?: Record<string, any>) {
    Sentry.captureMessage(message, {
        level,
        contexts: context ? { custom: context } : undefined,
    })
}

// Add breadcrumb
export function addBreadcrumb(message: string, data?: Record<string, any>) {
    Sentry.addBreadcrumb({
        message,
        data,
        level: 'info',
        timestamp: Date.now() / 1000,
    })
}

// Set user context
export function setUser(user: { id: string | number; email?: string; username?: string }) {
    Sentry.setUser({
        id: String(user.id),
        email: user.email,
        username: user.username,
    })
}

// Clear user context (on logout)
export function clearUser() {
    Sentry.setUser(null)
}

// Performance monitoring
export function startTransaction(name: string, op: string) {
    return Sentry.startTransaction({
        name,
        op,
    })
}
