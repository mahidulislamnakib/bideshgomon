# BideshGomon Official Design System
**Version 2.0** | Aligned with Live Production Site

## 🎨 Brand Colors

### Primary Brand Identity
Based on https://bideshgomon.com/ live site analysis:

```css
/* PRIMARY GREEN - Main CTA & Interactive Elements */
--brand-green: #64ac44;        /* Success, primary buttons */
--brand-green-hover: #54923a;  /* Hover states */
--brand-green-light: #dcfce7;  /* Backgrounds */

/* SECONDARY RED - Accents Only */
--brand-red: #e4282b;          /* Emergency/Critical alerts only */
--brand-red-light: #fee2e2;    /* Error backgrounds */

/* NEUTRAL GRAYS */
--brand-gray: #505050;         /* Body text */
--gray-50: #f9fafb;            /* Backgrounds */
--gray-100: #f3f4f6;           /* Cards, sections */
--gray-600: #4b5563;           /* Secondary text */
--gray-900: #111827;           /* Headings */
```

### Color Usage Rules

✅ **DO:**
- Use GREEN for ALL primary CTAs (Get Started, Register, Submit)
- Use GREEN for success states, active nav, links
- Use GRAY for text hierarchy (900 → 600 → 400)
- Use WHITE backgrounds with gray-50/100 sections

❌ **DON'T:**
- Don't use RED for primary actions
- Don't mix red/green/blue randomly
- Don't use bright colors without purpose

## 📐 Layout Standards

### Spacing System (8px Grid)
```css
4px  → Gap between small elements
8px  → Card padding mobile
12px → Button padding
16px → Section padding mobile
24px → Section gaps
32px → Section padding desktop
48px → Section spacing
64px → Major section gaps
```

### Container Widths
```css
max-w-7xl   → Main content (1280px)
max-w-4xl   → Forms, articles (896px)
max-w-3xl   → Hero text (768px)
```

### Border Radius
```css
rounded-lg   → Cards (8px)
rounded-xl   → Featured cards (12px)
rounded-2xl  → Hero sections (16px)
rounded-full → Buttons, badges
```

## 🔤 Typography

### Font Stack
```css
font-family: 'Inter', system-ui, -apple-system, sans-serif;
```

### Hierarchy
```css
/* Headers */
H1: text-4xl md:text-5xl, font-bold, text-gray-900
H2: text-3xl md:text-4xl, font-bold, text-gray-900
H3: text-xl, font-semibold, text-gray-900

/* Body */
Body Large: text-lg, text-gray-600
Body: text-base, text-gray-700
Body Small: text-sm, text-gray-600

/* Labels */
Label: text-sm, font-semibold, text-emerald-600, uppercase, tracking-wider
```

## 🎛️ Components

### Primary Button
```vue
<button class="px-8 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 font-medium transition-colors">
  Get Started
</button>
```

### Secondary Button
```vue
<button class="px-8 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium transition-colors">
  Learn More
</button>
```

### Card
```vue
<div class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg transition-shadow">
  <!-- Content -->
</div>
```

### Section Label
```vue
<p class="text-sm font-semibold text-emerald-600 uppercase tracking-wider mb-2">
  Core Services
</p>
```

### Badge
```vue
<span class="px-2 py-1 text-xs font-semibold bg-emerald-100 text-emerald-700 rounded-full">
  Most Popular
</span>
```

## 🏗️ Page Layouts

### Hero Section (Welcome Page)
```vue
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-12">
  <div class="max-w-3xl mx-auto text-center">
    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
      Go Abroad with Confidence
    </h1>
    <p class="text-lg md:text-xl text-gray-600 mb-8">
      All-in-one platform for visa applications...
    </p>
    <div class="flex gap-4 justify-center">
      <a href="#" class="px-8 py-3 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
        Get Started Free
      </a>
      <a href="#" class="px-8 py-3 border border-gray-300 rounded-lg hover:bg-gray-50">
        Learn More
      </a>
    </div>
  </div>
</div>
```

### Dashboard Cards
```vue
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
  <a href="#" class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden group">
    <div class="p-6">
      <div class="flex items-start justify-between mb-4">
        <div class="p-3 rounded-lg bg-emerald-100">
          <Icon class="h-6 w-6 text-emerald-600" />
        </div>
        <span v-if="badge" class="px-2 py-1 text-xs bg-orange-100 text-orange-600 rounded-full">
          {{ badge }}
        </span>
      </div>
      <h3 class="font-semibold text-lg text-gray-900 mb-2">
        {{ title }}
      </h3>
      <p class="text-sm text-gray-600">
        {{ description }}
      </p>
    </div>
  </a>
</div>
```

### Feature Grid
```vue
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
  <div class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg transition-shadow">
    <div class="w-8 h-8 text-emerald-600 mb-4">
      <Icon />
    </div>
    <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ title }}</h3>
    <p class="text-sm text-gray-600 leading-relaxed">{{ description }}</p>
    <a href="#" class="mt-4 text-emerald-600 hover:text-emerald-700 text-sm font-medium inline-flex items-center">
      Learn more →
    </a>
  </div>
</div>
```

## 🎯 Navigation

### Header (Public)
```vue
<header class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-200">
  <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex h-16 items-center justify-between">
      <!-- Logo -->
      <!-- Nav Links: text-gray-700 hover:text-emerald-600 -->
      <!-- CTA: bg-emerald-600 -->
    </div>
  </nav>
</header>
```

### Header (Authenticated)
```vue
<nav class="border-b border-gray-100 bg-white sticky top-0 z-40">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="flex h-16 justify-between">
      <!-- Logo -->
      <!-- Nav Links: text-gray-500 hover:text-gray-700, active: border-b-2 border-emerald-400 text-gray-900 -->
    </div>
  </div>
</nav>
```

## 📱 Responsive Breakpoints

```css
xs:  475px  → Extra small phones
sm:  640px  → Small phones
md:  768px  → Tablets
lg:  1024px → Laptops
xl:  1280px → Desktops
2xl: 1536px → Large screens
```

### Mobile-First Patterns
```vue
<!-- Stack on mobile, row on desktop -->
<div class="flex flex-col lg:flex-row gap-6">

<!-- 1 column → 2 → 3 → 4 -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">

<!-- Responsive text -->
<h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl">

<!-- Responsive padding -->
<div class="p-4 sm:p-6 lg:p-8">
```

## 🌟 Shadows & Effects

### Elevation System
```css
/* Rest state */
shadow-none

/* Cards */
shadow-sm     → Subtle elevation
shadow-md     → Card hover
shadow-lg     → Modals, dropdowns

/* Floating elements */
shadow-xl     → Popovers
shadow-2xl    → Prominent dialogs
```

### Transitions
```vue
<!-- Standard transition -->
class="transition-colors duration-200"

<!-- Complex transition -->
class="transition-all duration-300 ease-out"

<!-- Shadow transition -->
class="hover:shadow-lg transition-shadow duration-300"
```

## ✨ Icons

### Usage
- **Size:** `h-5 w-5` for nav, `h-6 w-6` for cards, `h-8 w-8` for features
- **Color:** Match parent text color or use `text-emerald-600`
- **Library:** Heroicons Outline (primary), Solid (special cases)

```vue
<CheckCircleIcon class="h-6 w-6 text-emerald-600" />
```

## 🔗 Links

```vue
<!-- Inline text link -->
<a href="#" class="text-emerald-600 hover:text-emerald-700 font-medium">
  Learn more
</a>

<!-- Nav link -->
<a href="#" class="text-gray-700 hover:text-emerald-600 transition-colors">
  Services
</a>

<!-- Active nav link -->
<a href="#" class="text-emerald-600 font-medium border-b-2 border-emerald-400">
  Dashboard
</a>
```

## 📋 Forms

### Input Fields
```vue
<input 
  type="text"
  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
  placeholder="Enter your email"
/>
```

### Select Dropdown
```vue
<select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
  <option>Select option</option>
</select>
```

### Checkbox
```vue
<input 
  type="checkbox"
  class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
/>
```

## 🎨 Background Patterns

### Section Alternation
```vue
<!-- White background -->
<div class="bg-white py-16">

<!-- Gray background (alternating sections) -->
<div class="bg-gray-50 py-16">

<!-- Brand CTA section -->
<div class="bg-emerald-600 py-16">
  <h2 class="text-white">Ready to Start?</h2>
</div>
```

## 📊 Status Colors

```css
/* Success/Active */
bg-emerald-100 text-emerald-700

/* Warning/Pending -->
bg-yellow-100 text-yellow-700

/* Error/Rejected */
bg-red-100 text-red-700

/* Info/Neutral */
bg-blue-100 text-blue-700

/* Inactive */
bg-gray-100 text-gray-600
```

## 🚀 Animation Guidelines

### Hover States
- Scale: `hover:scale-105` (subtle)
- Shadow: `hover:shadow-lg`
- Color: `hover:bg-emerald-700`
- Duration: `transition-all duration-300`

### Page Loads
- Use fade-in for hero sections
- Stagger list items (0.1s delay each)
- Animate cards on scroll (optional)

## 📝 Content Guidelines

### Tone
- Professional yet friendly
- Clear, concise language
- Bangladesh-focused messaging
- Action-oriented CTAs

### CTAs
✅ "Get Started Free"
✅ "Create Free Account"
✅ "Apply Now"
✅ "Learn More"

❌ "Click Here"
❌ "Submit"
❌ Generic buttons

---

## 🔄 Migration Checklist

When updating existing pages:

- [ ] Replace all `brand-red-600` with `emerald-600` for primary actions
- [ ] Update button styles to match new system
- [ ] Use consistent card shadows (`shadow-sm` → `hover:shadow-lg`)
- [ ] Apply proper text hierarchy (gray-900 → gray-600)
- [ ] Add section labels (`text-emerald-600 uppercase`)
- [ ] Update spacing to 8px grid system
- [ ] Ensure responsive behavior (mobile-first)
- [ ] Add smooth transitions to interactive elements
- [ ] Use Heroicons consistently
- [ ] Update empty states with proper icons

---

**Last Updated:** December 3, 2025  
**Based On:** https://bideshgomon.com/ (Live Production)  
**Primary Color:** Emerald Green (#64ac44 / emerald-600)
