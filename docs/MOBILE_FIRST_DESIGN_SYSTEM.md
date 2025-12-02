# BideshGomon Mobile-First Design System
## World-Class, Airbnb-Inspired UI/UX

**Version**: 2.0  
**Last Updated**: December 2, 2025  
**Design Philosophy**: Mobile-first, progressive enhancement, accessibility-focused

---

## 🎯 Design Principles

### 1. Mobile-First Approach
- **Default design for mobile** (320px - 768px)
- Progressive enhancement for tablets and desktop
- Touch-friendly interactions (minimum 44x44px targets)
- Optimized for one-hand usage
- Fast loading and smooth animations

### 2. Airbnb-Inspired Aesthetics
- Clean, minimalist interface
- Generous whitespace
- Soft shadows and subtle depth
- Rounded corners (12px-24px)
- Professional photography and imagery
- Consistent spacing rhythm

### 3. Brand Consistency
- **Logo color**: Ocean Blue (#0087ff) - used consistently throughout
- **Icons**: Multi-colored for visual hierarchy and delight
- **Typography**: Inter font family for modern, readable text
- **Colors**: Purposeful palette aligned with brand values

---

## 🎨 Color System

### Primary Brand Colors

#### Ocean Blue (Logo Color)
```css
/* Primary brand identity - used for logo, primary actions, and key elements */
brand-50: #eff6ff   /* Lightest backgrounds */
brand-100: #dbeafe  /* Light backgrounds */
brand-200: #bfdbfe  /* Subtle accents */
brand-300: #93c5fd  /* Light interactive states */
brand-400: #60a5fa  /* Medium emphasis */
brand-500: #0087ff  /* PRIMARY - Logo color, main actions */
brand-600: #006dd1  /* Hover states, active elements */
brand-700: #0055a3  /* Pressed states */
brand-800: #004075  /* Dark accents */
brand-900: #002b47  /* Darkest text on light backgrounds */
```

**Usage Examples**:
```html
<!-- Primary button with logo color -->
<button class="bg-brand-500 hover:bg-brand-600 text-white">
    Book Now
</button>

<!-- Brand link -->
<a href="#" class="text-brand-600 hover:text-brand-700">
    Learn more
</a>

<!-- Focus ring -->
<input class="focus:ring-2 focus:ring-brand-500" />
```

### Semantic Colors (Multi-colored Icons)

#### Success - Growth Green
```css
success-500: #22c55e  /* Confirmation, success states */
success-600: #16a34a  /* Success hover */
```

**Icon Usage**: Checkmarks, completed steps, approved status, positive metrics

#### Warning - Sunrise Orange
```css
warning-500: #f97316  /* Alerts, pending actions */
warning-600: #ea580c  /* Warning hover */
```

**Icon Usage**: Pending items, important notices, attention needed, CTAs

#### Danger - Red
```css
red-500: #ef4444      /* Errors, critical actions */
red-600: #dc2626      /* Danger hover */
```

**Icon Usage**: Delete, cancel, errors, rejected status

#### Info - Sky Blue
```css
blue-500: #3b82f6    /* Informational content */
blue-600: #2563eb    /* Info hover */
```

**Icon Usage**: Information, help, guides, neutral actions

#### Premium - Gold
```css
gold-500: #eab308    /* Premium features, achievements */
gold-600: #ca8a04    /* Premium hover */
```

**Icon Usage**: Premium badges, rewards, achievements, stars

### Neutral Colors
```css
/* Gray scale for text, borders, backgrounds */
gray-50: #fafafa     /* Very light backgrounds */
gray-100: #f5f5f5    /* Light backgrounds, hover states */
gray-200: #e5e5e5    /* Borders, dividers */
gray-300: #d4d4d4    /* Disabled states, placeholders */
gray-400: #a3a3a3    /* Secondary text */
gray-500: #737373    /* Body text, captions */
gray-600: #525252    /* Headings, emphasis */
gray-700: #404040    /* Dark text */
gray-800: #262626    /* Very dark text */
gray-900: #171717    /* Darkest text */
```

---

## 📐 Spacing System

### Mobile-First Spacing Scale
Based on 4px base unit for visual rhythm:

```css
0: 0px
1: 4px      /* Minimal spacing */
2: 8px      /* Tight spacing */
3: 12px     /* Compact spacing */
4: 16px     /* Standard spacing */
5: 20px     /* Comfortable spacing */
6: 24px     /* Generous spacing */
8: 32px     /* Large spacing */
10: 40px    /* Extra large spacing */
12: 48px    /* Section spacing */
16: 64px    /* Hero spacing */
20: 80px    /* Major sections */
24: 96px    /* Page sections */
```

### Responsive Spacing Pattern
```html
<!-- Mobile: tight spacing, Desktop: generous spacing -->
<div class="p-4 md:p-6 lg:p-8">
    <h1 class="mb-4 md:mb-6 lg:mb-8">Title</h1>
    <div class="space-y-4 md:space-y-6">
        <!-- Content -->
    </div>
</div>
```

---

## 🔤 Typography

### Font Family
```css
font-sans: Inter, system-ui, -apple-system, sans-serif
font-display: Inter, system-ui, sans-serif
```

**Why Inter?**
- Highly legible on all screen sizes
- Excellent character spacing
- Modern, professional appearance
- Optimized for digital interfaces
- Open source, web-optimized

### Mobile-First Type Scale

#### Headings
```css
/* Mobile → Desktop */
text-display-2xl: 3rem → 5rem    /* Hero headlines */
text-display-xl: 2.5rem → 4rem   /* Major headings */
text-display-lg: 2rem → 3rem     /* Section headings */
text-display-md: 1.75rem → 2.5rem /* Subsection headings */
text-display-sm: 1.5rem → 2rem   /* Card headings */
```

#### Body Text
```css
text-base: 1rem (16px)           /* Standard body text */
text-lg: 1.125rem (18px)         /* Emphasized body text */
text-sm: 0.875rem (14px)         /* Secondary text, captions */
text-xs: 0.75rem (12px)          /* Fine print, labels */
```

### Typography Best Practices

#### Mobile (320px - 768px)
```html
<!-- Headings: bold, tight line-height -->
<h1 class="text-3xl md:text-5xl font-bold leading-tight">
    Explore Global Opportunities
</h1>

<!-- Body: comfortable line-height, readable size -->
<p class="text-base leading-relaxed text-gray-700">
    Apply for visas to 195+ countries with our intelligent platform.
</p>

<!-- Captions: smaller, subdued color -->
<span class="text-sm text-gray-500">
    Last updated 2 hours ago
</span>
```

#### Desktop (1024px+)
```html
<!-- Larger, more dramatic -->
<h1 class="text-3xl md:text-5xl lg:text-6xl font-bold">
    World-Class Visa Services
</h1>
```

---

## 🎴 Component Library

### Buttons

#### Primary Button (Brand Color)
```html
<button class="btn btn-primary">
    Apply Now
</button>

<!-- Generated CSS -->
.btn-primary {
    @apply bg-brand-500 text-white hover:bg-brand-600 
           active:bg-brand-700 shadow-sm hover:shadow-md
           rounded-xl px-6 py-3 font-semibold
           focus:ring-2 focus:ring-brand-500 focus:ring-offset-2;
    min-height: 44px; /* Touch-friendly */
}
```

#### Button Variants
```html
<!-- Secondary -->
<button class="btn btn-secondary">
    Cancel
</button>

<!-- Outline -->
<button class="btn btn-outline">
    Learn More
</button>

<!-- Ghost -->
<button class="btn btn-ghost">
    Skip
</button>

<!-- Success -->
<button class="btn btn-success">
    Confirm
</button>

<!-- Warning -->
<button class="btn btn-warning">
    Review Required
</button>

<!-- Danger -->
<button class="btn btn-danger">
    Delete Account
</button>
```

#### Button Sizes
```html
<!-- Small -->
<button class="btn btn-primary btn-sm">Small</button>

<!-- Default -->
<button class="btn btn-primary">Default</button>

<!-- Large -->
<button class="btn btn-primary btn-lg">Large</button>

<!-- Full width on mobile -->
<button class="btn btn-primary mobile-full">
    Continue
</button>
```

#### Button with Icons
```html
<button class="btn btn-primary">
    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor">
        <!-- Icon -->
    </svg>
    Apply for Visa
</button>
```

### Cards

#### Standard Card
```html
<div class="card p-6">
    <h3 class="text-xl font-bold mb-4">Card Title</h3>
    <p class="text-gray-600">Card content goes here.</p>
</div>

<!-- Generated CSS -->
.card {
    @apply bg-white rounded-2xl shadow-card transition-shadow duration-300;
}
```

#### Interactive Card
```html
<!-- Hover effect -->
<div class="card-hover p-6 cursor-pointer">
    <h3 class="text-xl font-bold mb-4">Clickable Card</h3>
</div>

<!-- Interactive with scale -->
<div class="card-interactive p-6">
    <h3 class="text-xl font-bold mb-4">Interactive Card</h3>
</div>
```

#### Profile Card
```html
<div class="profile-card">
    <div class="flex items-center space-x-4 mb-4">
        <img src="avatar.jpg" class="w-16 h-16 rounded-full" />
        <div>
            <h3 class="font-bold text-lg">Ahmed Rahman</h3>
            <p class="text-sm text-gray-500">Software Engineer</p>
        </div>
    </div>
    <p class="text-gray-600">
        Applied for 3 visas, 2 approved
    </p>
</div>
```

### Form Elements

#### Input Fields
```html
<!-- Standard input -->
<div class="form-group">
    <label class="label">Full Name</label>
    <input type="text" class="input" placeholder="Enter your name" />
</div>

<!-- Required field -->
<div class="form-group">
    <label class="label label-required">Email Address</label>
    <input type="email" class="input" />
    <p class="help-text">We'll never share your email</p>
</div>

<!-- Error state -->
<div class="form-group">
    <label class="label">Phone Number</label>
    <input type="tel" class="input input-error" />
    <p class="error-text">Please enter a valid phone number</p>
</div>
```

#### Form Layouts
```html
<!-- Stacked (mobile-first) -->
<div class="form-group">
    <input type="text" class="input" placeholder="First Name" />
    <input type="text" class="input" placeholder="Last Name" />
</div>

<!-- Grid (responsive) -->
<div class="form-grid-2">
    <input type="text" class="input" placeholder="First Name" />
    <input type="text" class="input" placeholder="Last Name" />
</div>

<!-- 3 columns on large screens -->
<div class="form-grid">
    <input type="text" class="input" placeholder="City" />
    <input type="text" class="input" placeholder="State" />
    <input type="text" class="input" placeholder="ZIP" />
</div>
```

### Status Badges

```html
<!-- Success -->
<span class="badge badge-success">Approved</span>

<!-- Warning -->
<span class="badge badge-warning">Pending</span>

<!-- Danger -->
<span class="badge badge-danger">Rejected</span>

<!-- Info -->
<span class="badge badge-info">In Review</span>

<!-- Neutral -->
<span class="badge badge-neutral">Draft</span>
```

### Loading States

```html
<!-- Skeleton loader -->
<div class="space-y-4">
    <div class="skeleton h-4 w-3/4"></div>
    <div class="skeleton h-4 w-1/2"></div>
    <div class="skeleton h-32 w-full"></div>
</div>

<!-- Avatar skeleton -->
<div class="skeleton-avatar"></div>

<!-- Text skeleton -->
<div class="skeleton-text"></div>
```

---

## 📱 Mobile-First Patterns

### Touch Targets
**All interactive elements must be at least 44x44px**

```html
<!-- Automatically applied to buttons -->
<button class="btn">Tap Me</button>

<!-- Manual application -->
<a href="#" class="touch-target inline-block p-2">
    <svg class="w-6 h-6"><!-- Icon --></svg>
</a>
```

### Safe Area Insets
For devices with notches (iPhone X+, etc.)

```html
<!-- Full screen layout -->
<div class="safe-top safe-bottom safe-left safe-right">
    Content respects notch and home indicator
</div>

<!-- Individual sides -->
<header class="safe-top sticky-header">
    Navigation
</header>
```

### Responsive Navigation

#### Mobile Bottom Nav (Thumb-friendly)
```html
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 
            safe-bottom z-50 md:hidden">
    <div class="flex justify-around items-center h-16">
        <a href="#" class="touch-target flex flex-col items-center">
            <svg class="w-6 h-6 text-brand-500"><!-- Home icon --></svg>
            <span class="text-xs mt-1 text-brand-600">Home</span>
        </a>
        <!-- More nav items -->
    </div>
</nav>
```

#### Desktop Horizontal Nav
```html
<nav class="hidden md:flex items-center space-x-8">
    <a href="#" class="text-gray-700 hover:text-brand-600 font-medium">
        Home
    </a>
    <a href="#" class="text-gray-700 hover:text-brand-600 font-medium">
        Services
    </a>
    <a href="#" class="text-gray-700 hover:text-brand-600 font-medium">
        About
    </a>
</nav>
```

### Mobile-Full Buttons
```html
<!-- Full width on mobile, auto on desktop -->
<button class="btn btn-primary mobile-full">
    Continue to Payment
</button>

<!-- Generated CSS handles breakpoints -->
```

### Sticky Headers with Blur
```html
<header class="sticky-header">
    <div class="container-wide py-4">
        <nav class="flex justify-between items-center">
            <img src="logo.png" class="h-10" />
            <button class="btn btn-primary">Sign In</button>
        </nav>
    </div>
</header>

<!-- CSS applies backdrop blur and translucency -->
.sticky-header {
    @apply sticky top-0 z-40 backdrop-blur-md bg-white/80 border-b border-gray-200;
}
```

---

## 🎭 Icon System

### Multi-Colored Icons Strategy

#### Principle
- **Logo**: Always ocean blue (#0087ff)
- **Navigation icons**: Brand color for active, gray for inactive
- **Status icons**: Semantic colors (green success, orange warning, red error)
- **Feature icons**: Diverse colors for visual hierarchy and delight
- **Action icons**: Context-appropriate colors

#### Examples

```html
<!-- Brand-colored navigation -->
<nav>
    <a href="#" class="text-brand-500">
        <HomeIcon class="w-6 h-6" />
        Home
    </a>
    <a href="#" class="text-gray-400 hover:text-brand-500">
        <SearchIcon class="w-6 h-6" />
        Search
    </a>
</nav>

<!-- Status icons -->
<div class="flex items-center space-x-2">
    <CheckCircleIcon class="w-5 h-5 text-success-500" />
    <span>Application Approved</span>
</div>

<div class="flex items-center space-x-2">
    <ClockIcon class="w-5 h-5 text-warning-500" />
    <span>Pending Review</span>
</div>

<div class="flex items-center space-x-2">
    <XCircleIcon class="w-5 h-5 text-red-500" />
    <span>Application Rejected</span>
</div>

<!-- Feature icons (diverse colors) -->
<div class="grid grid-cols-2 gap-4">
    <div class="card p-6">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
            <GlobeIcon class="w-6 h-6 text-blue-600" />
        </div>
        <h3>Visa Services</h3>
    </div>
    
    <div class="card p-6">
        <div class="w-12 h-12 bg-success-100 rounded-xl flex items-center justify-center mb-4">
            <BriefcaseIcon class="w-6 h-6 text-success-600" />
        </div>
        <h3>Job Search</h3>
    </div>
    
    <div class="card p-6">
        <div class="w-12 h-12 bg-warning-100 rounded-xl flex items-center justify-center mb-4">
            <DocumentIcon class="w-6 h-6 text-warning-600" />
        </div>
        <h3>Documents</h3>
    </div>
    
    <div class="card p-6">
        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
            <AcademicCapIcon class="w-6 h-6 text-purple-600" />
        </div>
        <h3>Education</h3>
    </div>
</div>
```

### Icon Sizes
```html
<!-- Extra small -->
<Icon class="w-3 h-3" />  <!-- 12px -->

<!-- Small -->
<Icon class="w-4 h-4" />  <!-- 16px -->

<!-- Base -->
<Icon class="w-5 h-5" />  <!-- 20px -->

<!-- Medium -->
<Icon class="w-6 h-6" />  <!-- 24px -->

<!-- Large -->
<Icon class="w-8 h-8" />  <!-- 32px -->

<!-- Extra large -->
<Icon class="w-12 h-12" /> <!-- 48px -->
```

---

## 🎬 Animation & Transitions

### Animation Principles
1. **Purposeful**: Animations should communicate state changes
2. **Fast**: 200-400ms for most transitions
3. **Smooth**: Use easing functions (cubic-bezier)
4. **Subtle**: Don't distract from content
5. **Performant**: Use transform and opacity only

### Built-in Animations

```html
<!-- Fade in -->
<div class="animate-fade-in">
    Content fades in smoothly
</div>

<!-- Fade in up (hero sections) -->
<div class="animate-fade-in-up">
    Content slides up while fading
</div>

<!-- Slide in from right -->
<div class="animate-slide-in-right">
    Slides from right edge
</div>

<!-- Scale in (modals) -->
<div class="animate-scale-in">
    Scales up from center
</div>

<!-- Subtle pulse (notifications) -->
<div class="animate-pulse-subtle">
    Gentle pulsing effect
</div>
```

### Transition Classes

```html
<!-- Smooth all transitions -->
<div class="transition-all duration-300">
    Hover me
</div>

<!-- Color transition -->
<button class="bg-brand-500 hover:bg-brand-600 transition-colors duration-200">
    Smooth color change
</button>

<!-- Transform transition -->
<div class="hover:scale-105 transition-transform duration-300">
    Scales on hover
</div>

<!-- Shadow transition -->
<div class="shadow-card hover:shadow-card-hover transition-shadow duration-300">
    Shadow grows on hover
</div>
```

---

## ♿ Accessibility

### Focus States
```html
<!-- Always show focus rings -->
<button class="focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
    Accessible Button
</button>

<!-- Focus visible (keyboard only) -->
<a href="#" class="focus-visible:ring-2 focus-visible:ring-brand-500">
    Link
</a>
```

### ARIA Labels
```html
<!-- Icon-only button -->
<button aria-label="Close dialog" class="btn btn-ghost">
    <XIcon class="w-6 h-6" />
</button>

<!-- Hidden text for screen readers -->
<span class="sr-only">Navigate to home page</span>
```

### Color Contrast
- **WCAG AA**: Minimum 4.5:1 for normal text
- **WCAG AAA**: 7:1 for enhanced readability
- All text meets AA standards
- Important text meets AAA standards

```html
<!-- Good contrast -->
<p class="text-gray-900">Dark text on light background</p>

<!-- Avoid low contrast -->
<p class="text-gray-400">❌ Too light for body text</p>
```

### Keyboard Navigation
- All interactive elements are keyboard accessible
- Tab order follows visual order
- Enter/Space triggers buttons
- Escape closes modals

---

## 📐 Layout Patterns

### Container Widths
```html
<!-- Narrow (blog posts, forms) -->
<div class="container-narrow">
    <article>Content</article>
</div>

<!-- Medium (most pages) -->
<div class="container-medium">
    <section>Content</section>
</div>

<!-- Wide (dashboards, tables) -->
<div class="container-wide">
    <div class="grid grid-cols-3 gap-6">
        <!-- Grid content -->
    </div>
</div>
```

### Grid Systems

#### Auto-responsive Grid
```html
<!-- 1 col mobile → 2 col tablet → 3 col desktop -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="card">Item 1</div>
    <div class="card">Item 2</div>
    <div class="card">Item 3</div>
</div>

<!-- 1 col mobile → 2 col tablet → 4 col desktop -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="card">Item 1</div>
    <!-- More items -->
</div>
```

#### Flex Layouts
```html
<!-- Responsive stack to row -->
<div class="flex flex-col md:flex-row gap-4">
    <div class="flex-1">Left column</div>
    <div class="flex-1">Right column</div>
</div>

<!-- Space between -->
<div class="flex justify-between items-center">
    <h2>Title</h2>
    <button class="btn btn-primary">Action</button>
</div>
```

### Page Sections
```html
<!-- Hero section -->
<section class="py-12 md:py-20 lg:py-24">
    <div class="container-medium">
        <h1 class="text-display-lg mb-6">Hero Title</h1>
        <p class="text-xl text-gray-600 mb-8">Subtitle</p>
        <button class="btn btn-primary btn-lg">Get Started</button>
    </div>
</section>

<!-- Content section -->
<section class="py-12 md:py-16 lg:py-20 bg-gray-50">
    <div class="container-wide">
        <div class="section-header text-center">
            <h2 class="section-title">Section Title</h2>
            <p class="section-subtitle">Section description</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Cards -->
        </div>
    </div>
</section>
```

---

## 🎨 Design Tokens Reference

### Quick Reference

```javascript
// Brand Colors
brand-500: #0087ff    // Logo color, primary actions

// Semantic Colors
success-500: #22c55e  // Success states
warning-500: #f97316  // Warnings, CTAs
red-500: #ef4444      // Errors, danger
blue-500: #3b82f6     // Information
gold-500: #eab308     // Premium features

// Spacing (base 4px)
1 = 4px, 2 = 8px, 3 = 12px, 4 = 16px, 6 = 24px, 8 = 32px

// Border Radius
rounded-lg = 12px      // Cards, buttons
rounded-xl = 16px      // Large cards
rounded-2xl = 24px     // Hero sections
rounded-full = 9999px  // Circles, pills

// Shadows
shadow-card          // Subtle card elevation
shadow-card-hover    // Prominent hover state
shadow-float         // Floating elements

// Typography
text-display-lg      // Hero headings
text-xl              // Section headings
text-base            // Body text
text-sm              // Secondary text
```

---

## 🚀 Performance Optimization

### Image Loading
```html
<!-- Lazy loading -->
<img src="image.jpg" loading="lazy" alt="Description" />

<!-- Responsive images -->
<img 
    srcset="image-320w.jpg 320w,
            image-640w.jpg 640w,
            image-1280w.jpg 1280w"
    sizes="(max-width: 640px) 100vw,
           (max-width: 1024px) 50vw,
           33vw"
    src="image-640w.jpg"
    alt="Description"
/>
```

### CSS Performance
- Use `transform` and `opacity` for animations (GPU-accelerated)
- Avoid animating `width`, `height`, `top`, `left`
- Use `will-change` sparingly
- Minimize repaints and reflows

```html
<!-- Good: GPU-accelerated -->
<div class="transition-transform hover:scale-105">
    Smooth animation
</div>

<!-- Avoid: Causes reflow -->
<div class="transition-all hover:w-64">
    Slow animation
</div>
```

---

## ✅ Design Checklist

### Before Launch
- [ ] All touch targets are 44x44px minimum
- [ ] Logo uses brand-500 (#0087ff) consistently
- [ ] Icons use appropriate semantic colors
- [ ] All text meets WCAG AA contrast (4.5:1)
- [ ] Focus states are visible
- [ ] Mobile navigation is thumb-friendly
- [ ] Forms prevent iOS zoom (16px+ font size)
- [ ] Safe area insets respected on notched devices
- [ ] Animations are smooth (60fps)
- [ ] Loading states implemented
- [ ] Error states are clear and helpful
- [ ] Empty states provide guidance
- [ ] Success confirmations are celebratory

---

## 📚 Resources

### Design Tools
- **Figma**: Design prototypes and mockups
- **Tailwind UI**: Component inspiration
- **Heroicons**: Icon library (used throughout)
- **Airbnb Design**: Reference for patterns and principles

### Testing Tools
- **Chrome DevTools**: Mobile emulation
- **WebAIM Contrast Checker**: Accessibility testing
- **Lighthouse**: Performance and accessibility audits
- **VoiceOver / NVDA**: Screen reader testing

### References
- [Tailwind CSS Documentation](https://tailwindcss.com)
- [Airbnb Design](https://airbnb.design)
- [Material Design (Mobile Patterns)](https://material.io)
- [Apple Human Interface Guidelines (iOS)](https://developer.apple.com/design)

---

**End of Mobile-First Design System Documentation**

For implementation questions or design requests, refer to this document first.
All components follow these patterns for consistency and quality.
