import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        // Mobile-first breakpoints (Airbnb-style)
        screens: {
            'xs': '475px',
            'sm': '640px',
            'md': '768px',
            'lg': '1024px',
            'xl': '1280px',
            '2xl': '1536px',
        },
        extend: {
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Arial', 'sans-serif'],
                display: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
            },
            colors: {
                // ============================================
                // PRIMARY BRAND COLORS - BideshGomon Official
                // ============================================
                primary: {
                    red: '#e4282b',      // Main brand red - primary actions
                    green: '#64ac44',    // Success states, CTAs
                    gray: '#505050',     // Text, borders, secondary elements
                },
                
                // Red variations (derived from #e4282b)
                'brand-red': {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#ef4444',
                    600: '#e4282b',  // PRIMARY
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                },
                
                // Green variations (derived from #64ac44)
                'brand-green': {
                    50: '#f0fdf4',
                    100: '#dcfce7',
                    200: '#bbf7d0',
                    300: '#86efac',
                    400: '#4ade80',
                    500: '#22c55e',
                    600: '#64ac44',  // PRIMARY
                    700: '#15803d',
                    800: '#166534',
                    900: '#14532d',
                },
                
                // Gray variations (derived from #505050)
                'brand-gray': {
                    50: '#f9fafb',
                    100: '#f3f4f6',
                    200: '#e5e7eb',
                    300: '#d1d5db',
                    400: '#9ca3af',
                    500: '#6b7280',
                    600: '#505050',  // PRIMARY
                    700: '#374151',
                    800: '#1f2937',
                    900: '#111827',
                },
                    700: '#0055a3',
                    800: '#004075',
                    900: '#002b47',
                    950: '#001729',
                },
                // Success - Growth & aspiration
                success: {
                    50: '#f0fdf4',
                    100: '#dcfce7',
                    200: '#bbf7d0',
                    300: '#86efac',
                    400: '#4ade80',
                    500: '#22c55e',
                    600: '#16a34a',
                    700: '#15803d',
                    800: '#166534',
                    900: '#14532d',
                },
                // Growth Greens - Alternative success
                growth: {
                    50: '#f0fdf4',
                    100: '#dcfce7',
                    200: '#bbf7d0',
                    300: '#86efac',
                    400: '#4ade80',
                    500: '#22c55e',
                    600: '#16a34a',
                    700: '#15803d',
                    800: '#166534',
                    900: '#14532d',
                },
                // Warning - Sunrise warmth
                warning: {
                    50: '#fff7ed',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f97316',
                    600: '#ea580c',
                    700: '#c2410c',
                    800: '#9a3412',
                    900: '#7c2d12',
                },
                // Sunrise Warmth - CTAs & energy
                sunrise: {
                    50: '#fff7ed',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f97316',
                    600: '#ea580c',
                    700: '#c2410c',
                    800: '#9a3412',
                    900: '#7c2d12',
                },
                // Gold - Premium features
                gold: {
                    50: '#fefce8',
                    100: '#fef9c3',
                    200: '#fef08a',
                    300: '#fde047',
                    400: '#facc15',
                    500: '#eab308',
                    600: '#ca8a04',
                    700: '#a16207',
                    800: '#854d0e',
                    900: '#713f12',
                },
                // Heritage - Cultural accent (Bangladesh)
                heritage: {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#ef4444',
                    600: '#dc2626',
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                },
                // Neutral grays (Airbnb-style)
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
                    950: '#0a0a0a',
                },
            },
            spacing: {
                // Mobile-first spacing (4px base)
                '18': '4.5rem',
                '88': '22rem',
                '128': '32rem',
                'safe-top': 'env(safe-area-inset-top)',
                'safe-bottom': 'env(safe-area-inset-bottom)',
                'safe-left': 'env(safe-area-inset-left)',
                'safe-right': 'env(safe-area-inset-right)',
                // Design System Rhythm (8px base)
                'rhythm-xs': '4px',
                'rhythm-sm': '8px',
                'rhythm-md': '12px',
                'rhythm-base': '16px',
                'rhythm-lg': '24px',
                'rhythm-xl': '32px',
                'rhythm-2xl': '48px',
                'rhythm-3xl': '64px',
            },
            fontSize: {
                // Mobile-optimized typography
                'xs': ['0.75rem', { lineHeight: '1rem' }],
                'sm': ['0.875rem', { lineHeight: '1.25rem' }],
                'base': ['1rem', { lineHeight: '1.5rem' }],
                'lg': ['1.125rem', { lineHeight: '1.75rem' }],
                'xl': ['1.25rem', { lineHeight: '1.75rem' }],
                '2xl': ['1.5rem', { lineHeight: '2rem' }],
                '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
                '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
                '5xl': ['3rem', { lineHeight: '1' }],
                '6xl': ['3.75rem', { lineHeight: '1' }],
                '7xl': ['4.5rem', { lineHeight: '1' }],
                '8xl': ['6rem', { lineHeight: '1' }],
                '9xl': ['8rem', { lineHeight: '1' }],
                // Display sizes for hero sections
                'display-sm': ['2rem', { lineHeight: '2.5rem', letterSpacing: '-0.01em', fontWeight: '700' }],
                'display-md': ['2.5rem', { lineHeight: '3rem', letterSpacing: '-0.01em', fontWeight: '700' }],
                'display-lg': ['3rem', { lineHeight: '3.5rem', letterSpacing: '-0.02em', fontWeight: '800' }],
                'display-xl': ['4rem', { lineHeight: '4.5rem', letterSpacing: '-0.02em', fontWeight: '800' }],
                'display-2xl': ['5rem', { lineHeight: '5.5rem', letterSpacing: '-0.03em', fontWeight: '900' }],
            },
            borderRadius: {
                '4xl': '2rem',
                '5xl': '2.5rem',
            },
            boxShadow: {
                // Airbnb-inspired shadows
                'soft': '0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04)',
                'card': '0 2px 8px rgba(0, 0, 0, 0.1)',
                'card-hover': '0 8px 24px rgba(0, 0, 0, 0.15)',
                'float': '0 12px 32px rgba(0, 0, 0, 0.12)',
                'glow-brand': '0 0 32px rgba(0, 135, 255, 0.25)',
                'glow-success': '0 0 32px rgba(34, 197, 94, 0.25)',
                'glow-warning': '0 0 32px rgba(249, 115, 22, 0.25)',
            },
            animation: {
                // Smooth, professional animations
                'fade-in': 'fadeIn 0.5s ease-out',
                'fade-in-up': 'fadeInUp 0.6s ease-out',
                'fade-in-down': 'fadeInDown 0.6s ease-out',
                'slide-in-right': 'slideInRight 0.4s ease-out',
                'slide-in-left': 'slideInLeft 0.4s ease-out',
                'slide-up': 'slideUp 0.4s ease-out',
                'scale-in': 'scaleIn 0.3s ease-out',
                'bounce-soft': 'bounceSoft 1s ease-in-out infinite',
                'pulse-subtle': 'pulseSubtle 2s ease-in-out infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                fadeInUp: {
                    '0%': { opacity: '0', transform: 'translateY(24px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                fadeInDown: {
                    '0%': { opacity: '0', transform: 'translateY(-24px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                slideInRight: {
                    '0%': { opacity: '0', transform: 'translateX(-24px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                slideInLeft: {
                    '0%': { opacity: '0', transform: 'translateX(24px)' },
                    '100%': { opacity: '1', transform: 'translateX(0)' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(100%)' },
                    '100%': { transform: 'translateY(0)' },
                },
                scaleIn: {
                    '0%': { opacity: '0', transform: 'scale(0.9)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                bounceSoft: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-8px)' },
                },
                pulseSubtle: {
                    '0%, 100%': { opacity: '1' },
                    '50%': { opacity: '0.85' },
                },
            },
            backdropBlur: {
                xs: '2px',
            },
            transitionDuration: {
                '400': '400ms',
            },
            transitionTimingFunction: {
                'smooth': 'cubic-bezier(0.4, 0, 0.2, 1)',
            },
            maxWidth: {
                '8xl': '88rem',
                '9xl': '96rem',
            },
            zIndex: {
                '60': '60',
                '70': '70',
                '80': '80',
                '90': '90',
                '100': '100',
            },
        },
    },

    plugins: [],
};
