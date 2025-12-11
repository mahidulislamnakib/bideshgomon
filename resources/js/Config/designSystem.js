/**
 * BideshGomon Design System - Tailwind Configuration
 * 
 * Brand Colors:
 * - Primary: #e4282b (Red)
 * - Success: #64ac44 (Green)
 * - Neutral: #505050 (Gray)
 * 
 * Design Philosophy:
 * - Mobile-first responsive
 * - Clean white space
 * - Card-based layouts
 * - Airbnb-inspired simplicity
 */

export const designTokens = {
  // Brand Colors
  colors: {
    // Primary (Red)
    primary: {
      50: '#fef2f2',
      100: '#fee2e2',
      200: '#fecaca',
      300: '#fca5a7',
      400: '#f87171',
      500: '#e4282b', // Brand Primary
      600: '#dc2626',
      700: '#b91c1c',
      800: '#991b1b',
      900: '#7f1d1d',
    },
    
    // Success/Accent (Green)
    success: {
      50: '#f0fdf4',
      100: '#dcfce7',
      200: '#bbf7d0',
      300: '#86efac',
      400: '#4ade80',
      500: '#64ac44', // Brand Success
      600: '#16a34a',
      700: '#15803d',
      800: '#166534',
      900: '#14532d',
    },
    
    // Neutral (Gray)
    neutral: {
      50: '#fafafa',
      100: '#f5f5f5',
      200: '#e5e5e5',
      300: '#d4d4d4',
      400: '#a3a3a3',
      500: '#505050', // Brand Neutral
      600: '#525252',
      700: '#404040',
      800: '#262626',
      900: '#171717',
    },
  },

  // Spacing Scale (4px base)
  spacing: {
    0: '0px',
    1: '4px',    // 0.25rem
    2: '8px',    // 0.5rem
    3: '12px',   // 0.75rem
    4: '16px',   // 1rem
    5: '20px',   // 1.25rem
    6: '24px',   // 1.5rem
    8: '32px',   // 2rem
    10: '40px',  // 2.5rem
    12: '48px',  // 3rem
    16: '64px',  // 4rem
    20: '80px',  // 5rem
    24: '96px',  // 6rem
  },

  // Typography
  typography: {
    fonts: {
      sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
      mono: ['Fira Code', 'monospace'],
    },
    
    sizes: {
      xs: ['12px', { lineHeight: '16px' }],
      sm: ['14px', { lineHeight: '20px' }],
      base: ['16px', { lineHeight: '24px' }],
      lg: ['18px', { lineHeight: '28px' }],
      xl: ['20px', { lineHeight: '28px' }],
      '2xl': ['24px', { lineHeight: '32px' }],
      '3xl': ['30px', { lineHeight: '36px' }],
      '4xl': ['36px', { lineHeight: '40px' }],
      '5xl': ['48px', { lineHeight: '1' }],
    },
    
    weights: {
      normal: 400,
      medium: 500,
      semibold: 600,
      bold: 700,
    },
  },

  // Shadows (Airbnb-inspired)
  shadows: {
    sm: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
    DEFAULT: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
    md: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
    lg: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
    xl: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
    '2xl': '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
    inner: 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)',
    none: 'none',
  },

  // Border Radius
  borderRadius: {
    none: '0px',
    sm: '4px',
    DEFAULT: '8px',
    md: '12px',
    lg: '16px',
    xl: '24px',
    '2xl': '32px',
    full: '9999px',
  },

  // Breakpoints (Mobile-first)
  screens: {
    sm: '640px',   // Small devices
    md: '768px',   // Tablets
    lg: '1024px',  // Laptops
    xl: '1280px',  // Desktops
    '2xl': '1536px', // Large screens
  },

  // Z-index layers
  zIndex: {
    base: 0,
    dropdown: 10,
    sticky: 20,
    fixed: 30,
    overlay: 40,
    modal: 50,
    popover: 60,
    tooltip: 70,
  },

  // Animation durations
  transitions: {
    fast: '150ms',
    base: '200ms',
    slow: '300ms',
    slower: '500ms',
  },
};

// Component-specific tokens
export const componentTokens = {
  // Card
  card: {
    padding: '24px',
    borderRadius: '12px',
    shadow: 'md',
    border: '1px solid #e5e5e5',
  },

  // Button
  button: {
    padding: {
      sm: '8px 16px',
      md: '12px 24px',
      lg: '16px 32px',
    },
    borderRadius: '8px',
    fontWeight: 600,
  },

  // Input
  input: {
    padding: '12px 16px',
    borderRadius: '8px',
    border: '1px solid #d4d4d4',
    focusBorder: '#e4282b',
  },

  // Sidebar
  sidebar: {
    width: {
      expanded: '280px',
      collapsed: '80px',
    },
    itemPadding: '12px 16px',
    itemHeight: '44px',
  },

  // TopNav
  topNav: {
    height: '64px',
    padding: '16px 24px',
  },

  // Dashboard Grid
  grid: {
    gap: '24px',
    columns: {
      mobile: 1,
      tablet: 2,
      desktop: 3,
      wide: 4,
    },
  },
};

// Utility class mapping
export const utilityClasses = {
  // Container
  container: 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8',
  
  // Card
  card: 'bg-white rounded-xl shadow-md border border-neutral-200 p-6',
  cardHover: 'hover:shadow-lg transition-shadow duration-200',
  
  // Button Primary
  btnPrimary: 'inline-flex items-center justify-center px-6 py-3 bg-primary-500 text-white font-semibold rounded-lg hover:bg-primary-600 active:bg-primary-700 transition-colors duration-200 shadow-sm hover:shadow-md',
  
  // Button Secondary
  btnSecondary: 'inline-flex items-center justify-center px-6 py-3 bg-white text-neutral-700 font-semibold rounded-lg border border-neutral-300 hover:bg-neutral-50 active:bg-neutral-100 transition-colors duration-200',
  
  // Button Success
  btnSuccess: 'inline-flex items-center justify-center px-6 py-3 bg-success-500 text-white font-semibold rounded-lg hover:bg-success-600 active:bg-success-700 transition-colors duration-200 shadow-sm hover:shadow-md',
  
  // Input
  input: 'w-full px-4 py-3 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all duration-200',
  
  // Page header
  pageHeader: 'mb-8',
  pageTitle: 'text-3xl font-bold text-neutral-900 mb-2',
  pageDescription: 'text-base text-neutral-600',
  
  // Stats card
  statCard: 'bg-white rounded-xl shadow-md p-6 border border-neutral-200',
  statIcon: 'w-12 h-12 rounded-lg flex items-center justify-center',
  statLabel: 'text-sm font-medium text-neutral-600 mt-4 mb-1',
  statValue: 'text-3xl font-bold text-neutral-900',
  statChange: 'text-sm font-medium mt-2',
  
  // Table
  table: 'min-w-full divide-y divide-neutral-200',
  tableHeader: 'bg-neutral-50',
  tableHeaderCell: 'px-6 py-4 text-left text-xs font-semibold text-neutral-700 uppercase tracking-wider',
  tableBody: 'bg-white divide-y divide-neutral-200',
  tableRow: 'hover:bg-neutral-50 transition-colors duration-150',
  tableCell: 'px-6 py-4 whitespace-nowrap text-sm text-neutral-900',
  
  // Badge
  badge: 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
  badgePrimary: 'bg-primary-100 text-primary-800',
  badgeSuccess: 'bg-success-100 text-success-800',
  badgeWarning: 'bg-yellow-100 text-yellow-800',
  badgeDanger: 'bg-red-100 text-red-800',
  badgeNeutral: 'bg-neutral-100 text-neutral-800',
};

export default {
  designTokens,
  componentTokens,
  utilityClasses,
};
