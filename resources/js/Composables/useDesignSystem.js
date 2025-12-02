/**
 * BideshGomon Design System - Airbnb-Inspired
 * Brand Colors: Emerald Green Primary
 */

export const useDesignSystem = () => {
    const colors = {
        // Primary Brand Colors
        primary: {
            50: '#ecfdf5',
            100: '#d1fae5',
            200: '#a7f3d0',
            300: '#6ee7b7',
            400: '#34d399',
            500: '#10b981', // Main emerald
            600: '#059669',
            700: '#047857',
            800: '#065f46',
            900: '#064e3b',
        },
        // Accent Colors
        accent: {
            pink: '#FF385C', // Airbnb signature
            orange: '#FF5A5F',
            teal: '#00A699',
            purple: '#8B5CF6',
        },
        // Neutral Colors
        neutral: {
            50: '#fafafa',
            100: '#f5f5f5',
            200: '#e5e5e5',
            300: '#d4d4d4',
            400: '#a3a3a3',
            500: '#737373',
            600: '#525252',
            700: '#404040',
            800: '#262626',
            900: '#171717',
        },
        // Status Colors
        status: {
            success: '#10b981',
            warning: '#f59e0b',
            error: '#ef4444',
            info: '#3b82f6',
        }
    }

    const typography = {
        fontFamily: {
            sans: 'Inter, system-ui, -apple-system, sans-serif',
            display: 'Circular, Inter, sans-serif', // Airbnb-style
        },
        fontSize: {
            xs: ['0.75rem', { lineHeight: '1rem' }],
            sm: ['0.875rem', { lineHeight: '1.25rem' }],
            base: ['1rem', { lineHeight: '1.5rem' }],
            lg: ['1.125rem', { lineHeight: '1.75rem' }],
            xl: ['1.25rem', { lineHeight: '1.75rem' }],
            '2xl': ['1.5rem', { lineHeight: '2rem' }],
            '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
            '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
            '5xl': ['3rem', { lineHeight: '1' }],
            '6xl': ['3.75rem', { lineHeight: '1' }],
        },
        fontWeight: {
            normal: '400',
            medium: '500',
            semibold: '600',
            bold: '700',
            extrabold: '800',
        }
    }

    const spacing = {
        section: {
            sm: '2rem',
            md: '4rem',
            lg: '6rem',
            xl: '8rem',
        },
        container: {
            sm: '640px',
            md: '768px',
            lg: '1024px',
            xl: '1280px',
            '2xl': '1536px',
        },
        grid: {
            gap: {
                sm: '1rem',
                md: '1.5rem',
                lg: '2rem',
            }
        }
    }

    const borderRadius = {
        none: '0',
        sm: '0.375rem',
        md: '0.5rem',
        lg: '0.75rem',
        xl: '1rem',
        '2xl': '1.5rem',
        '3xl': '2rem',
        full: '9999px',
    }

    const shadows = {
        // Airbnb-style soft shadows
        sm: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
        md: '0 4px 12px rgba(0, 0, 0, 0.08)',
        lg: '0 8px 24px rgba(0, 0, 0, 0.12)',
        xl: '0 16px 48px rgba(0, 0, 0, 0.16)',
        card: '0 6px 16px rgba(0, 0, 0, 0.12)',
        cardHover: '0 12px 32px rgba(0, 0, 0, 0.18)',
    }

    const transitions = {
        fast: '150ms cubic-bezier(0.4, 0, 0.2, 1)',
        base: '200ms cubic-bezier(0.4, 0, 0.2, 1)',
        slow: '300ms cubic-bezier(0.4, 0, 0.2, 1)',
        bounce: '500ms cubic-bezier(0.68, -0.55, 0.265, 1.55)',
    }

    const breakpoints = {
        sm: 640,
        md: 768,
        lg: 1024,
        xl: 1280,
        '2xl': 1536,
    }

    return {
        colors,
        typography,
        spacing,
        borderRadius,
        shadows,
        transitions,
        breakpoints,
    }
}
