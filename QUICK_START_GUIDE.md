# Mobile-First Design System - Quick Start Guide

**Target**: Development Team  
**Time to Read**: 5 minutes  
**Time to Implement**: 2-3 weeks

---

## 🚀 Getting Started (2 Minutes)

### What Changed?
We've implemented a **mobile-first, Airbnb-inspired design system** to achieve world-class UI/UX.

### Key Updates:
1. **Brand color** = `brand-500` (#0087ff) - ocean blue from logo
2. **Simple components** = `.btn-primary`, `.card`, `.input` instead of long utility chains
3. **Mobile-first** = Everything optimized for 320px+ screens first
4. **Touch-friendly** = All buttons ≥ 44x44px automatically
5. **Safe areas** = Support for iPhone notches

---

## 📖 Component Cheat Sheet

### Buttons
```html
<!-- Replace this -->
<button class="bg-ocean-500 hover:bg-ocean-600 text-white px-6 py-3 rounded-xl font-semibold">
    Click Me
</button>

<!-- With this -->
<button class="btn btn-primary">
    Click Me
</button>
```

**Available Variants**:
- `btn-primary` - Brand blue (main actions)
- `btn-secondary` - Gray (cancel, back)
- `btn-outline` - White with border (secondary actions)
- `btn-success` - Green (confirm, approve)
- `btn-warning` - Orange (alerts, pending)
- `btn-danger` - Red (delete, reject)

**Sizes**: `btn-sm`, default, `btn-lg`  
**Mobile**: Add `mobile-full` for full width on mobile

### Cards
```html
<!-- Replace this -->
<div class="bg-white rounded-xl shadow-md p-6">
    Content
</div>

<!-- With this -->
<div class="card p-6">
    Content
</div>
```

**Variants**:
- `card` - Basic card
- `card-hover` - Hover effect
- `card-interactive` - Click effect
- `profile-card` - Profile sections

### Forms
```html
<!-- Replace this -->
<label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
<input class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-ocean-500" />

<!-- With this -->
<label class="label">Email</label>
<input class="input" />
```

**Helpers**:
- `label-required` - Adds red asterisk
- `input-error` - Red border for errors
- `help-text` - Gray helper text
- `error-text` - Red error message

### Status Badges
```html
<span class="badge badge-success">Approved</span>
<span class="badge badge-warning">Pending</span>
<span class="badge badge-danger">Rejected</span>
```

### Containers
```html
<!-- Narrow (blog, forms) -->
<div class="container-narrow">...</div>

<!-- Medium (most pages) -->
<div class="container-medium">...</div>

<!-- Wide (dashboards) -->
<div class="container-wide">...</div>
```

---

## 🎨 Color System Quick Reference

### Primary (Logo Color)
```html
<!-- Backgrounds -->
<div class="bg-brand-500">...</div>

<!-- Text -->
<span class="text-brand-600">...</span>

<!-- Borders -->
<div class="border-brand-500">...</div>
```

### Semantic Colors
```html
<!-- Success -->
<div class="bg-success-500 text-white">Success</div>

<!-- Warning -->
<div class="bg-warning-500 text-white">Warning</div>

<!-- Danger -->
<div class="bg-red-500 text-white">Danger</div>

<!-- Info -->
<div class="bg-blue-500 text-white">Info</div>
```

### Icon Colors (Multi-colored Strategy)
- **Logo/Brand**: `text-brand-500`
- **Navigation**: `text-brand-600` (active), `text-gray-400` (inactive)
- **Success**: `text-success-500`
- **Warning**: `text-warning-500`
- **Error**: `text-red-500`
- **Info**: `text-blue-500`
- **Premium**: `text-gold-500`

---

## 📱 Mobile-First Patterns

### 1. Responsive Grids
```html
<!-- 1 column mobile → 2 tablet → 3 desktop -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="card p-6">Item 1</div>
    <div class="card p-6">Item 2</div>
    <div class="card p-6">Item 3</div>
</div>
```

### 2. Mobile-Full Buttons
```html
<!-- Full width on mobile, auto on desktop -->
<button class="btn btn-primary mobile-full">
    Continue
</button>
```

### 3. Safe Areas (iPhone Notches)
```html
<!-- Fixed header with notch support -->
<header class="fixed top-0 left-0 right-0 safe-top">
    Navigation
</header>

<!-- Fixed footer -->
<footer class="fixed bottom-0 left-0 right-0 safe-bottom">
    Bottom nav
</footer>
```

### 4. Stack to Row
```html
<!-- Stack on mobile, side-by-side on desktop -->
<div class="flex flex-col md:flex-row gap-4">
    <div class="flex-1">Left column</div>
    <div class="flex-1">Right column</div>
</div>
```

### 5. Hide/Show by Breakpoint
```html
<!-- Show only on mobile -->
<div class="md:hidden">
    Mobile-only content
</div>

<!-- Show only on desktop -->
<div class="hidden md:block">
    Desktop-only content
</div>
```

---

## 🔧 Common Patterns

### Hero Section
```html
<section class="py-12 md:py-20 lg:py-24">
    <div class="container-medium text-center">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6">
            Hero Title
        </h1>
        <p class="text-xl text-gray-600 mb-8">
            Subtitle text
        </p>
        <button class="btn btn-primary btn-lg">
            Get Started
        </button>
    </div>
</section>
```

### Feature Cards
```html
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="card p-6">
        <div class="w-12 h-12 bg-brand-100 rounded-xl flex items-center justify-center mb-4">
            <Icon class="w-6 h-6 text-brand-600" />
        </div>
        <h3 class="text-xl font-bold mb-2">Feature Title</h3>
        <p class="text-gray-600">Feature description</p>
    </div>
    <!-- More cards -->
</div>
```

### Form Layout
```html
<form class="space-y-6">
    <div class="form-group">
        <label class="label label-required">Full Name</label>
        <input type="text" class="input" placeholder="Enter your name" />
    </div>
    
    <div class="form-grid-2">
        <div class="form-group">
            <label class="label">Email</label>
            <input type="email" class="input" />
        </div>
        <div class="form-group">
            <label class="label">Phone</label>
            <input type="tel" class="input" />
        </div>
    </div>
    
    <button type="submit" class="btn btn-primary mobile-full">
        Submit
    </button>
</form>
```

### Profile Card
```html
<div class="profile-card">
    <div class="flex items-center space-x-4 mb-4">
        <img src="avatar.jpg" class="w-16 h-16 rounded-full" />
        <div>
            <h3 class="font-bold text-lg">User Name</h3>
            <p class="text-sm text-gray-500">User Role</p>
        </div>
    </div>
    <div class="space-y-2">
        <p class="text-gray-600">Additional info</p>
        <span class="badge badge-success">Active</span>
    </div>
</div>
```

---

## ⚡ Quick Wins (Do These First)

### 1. Update All Primary Buttons (15 minutes)
Find and replace:
```bash
# Search for
class="bg-ocean-500 hover:bg-ocean-600 text-white

# Replace with
class="btn btn-primary
```

### 2. Update All Cards (15 minutes)
Find and replace:
```bash
# Search for
class="bg-white rounded-xl shadow-md

# Replace with
class="card
```

### 3. Update Form Inputs (20 minutes)
Find and replace:
```bash
# Search for
class="w-full px-4 py-3 border border-gray-300 rounded-lg

# Replace with
class="input
```

### 4. Add Safe Area Insets (10 minutes)
Add to fixed headers/footers:
```html
<header class="fixed ... safe-top">
<footer class="fixed ... safe-bottom">
```

### 5. Update Brand Colors (15 minutes)
```bash
# Search for
bg-ocean-500

# Replace with
bg-brand-500
```

---

## 🐛 Troubleshooting

### Buttons Look Small on Mobile
**Solution**: Make sure you're using the `.btn` base class

### iOS Zoom When Typing
**Solution**: Already fixed! All inputs are 16px+ on mobile

### Content Hidden Behind iPhone Notch
**Solution**: Add `.safe-top` to fixed headers

### Colors Don't Match Logo
**Solution**: Use `brand-500` (#0087ff), not `ocean-500`

### Build Errors
**Solution**: Run `npm install` then `npm run dev`

---

## 📚 Full Documentation

**Comprehensive Guide**: `docs/MOBILE_FIRST_DESIGN_SYSTEM.md` (16,000 words)  
**Implementation Guide**: `MOBILE_FIRST_IMPLEMENTATION.md`

---

## ✅ Daily Checklist

When building new features:

- [ ] Start with mobile design (320px-768px)
- [ ] Use component classes (`.btn-primary`, `.card`)
- [ ] Ensure all buttons meet 44x44px minimum
- [ ] Test on iPhone SE (small screen)
- [ ] Test on iPhone 14 Pro (notch)
- [ ] Verify brand colors (logo = brand-500)
- [ ] Add safe area insets if fixed positioning
- [ ] Check contrast ratios (WCAG AA minimum)

---

## 🎯 This Week's Goals

**Monday-Tuesday**: Update all buttons and cards  
**Wednesday-Thursday**: Update all form inputs and labels  
**Friday**: Test on mobile devices, fix issues

---

## 💬 Questions?

**Slack**: #design-system  
**Documentation**: `docs/MOBILE_FIRST_DESIGN_SYSTEM.md`  
**Examples**: See implementation in `resources/js/Pages/Welcome.vue`

---

**Happy Coding!** 🎉

Remember: **Mobile-first, brand-consistent, touch-friendly**
