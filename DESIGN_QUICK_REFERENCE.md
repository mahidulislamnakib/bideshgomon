# BideshGomon Design System - Quick Reference Card

## 🎨 Brand Colors

```css
/* PRIMARY - Emerald Green (Main Brand) */
emerald-600: #64ac44   /* Buttons, Links, Active States */
emerald-700: #54923a   /* Hover States */

/* SECONDARY - Grays (Text & Backgrounds) */
gray-50:  #f9fafb     /* Page BG */
gray-100: #f3f4f6     /* Section BG */
gray-600: #4b5563     /* Secondary Text */
gray-900: #111827     /* Headings */

/* ACCENT - Use Sparingly */
red-600:  #dc2626     /* Errors ONLY */
blue-600: #2563eb     /* Info/Links (rare) */
orange-600: #ea580c   /* Featured Items */
```

## 🎯 Component Patterns

### Primary Button
```vue
<button class="bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500 
               text-white rounded-lg px-6 py-3 font-medium">
  Get Started
</button>
```

### Secondary Button
```vue
<button class="border border-gray-300 hover:bg-gray-50 
               text-gray-700 rounded-lg px-6 py-3 font-medium">
  Learn More
</button>
```

### Card
```vue
<div class="bg-white rounded-xl border border-gray-200 p-6 
            hover:shadow-lg transition-shadow">
  <h3 class="text-lg font-semibold text-gray-900 mb-2">Title</h3>
  <p class="text-sm text-gray-600">Description</p>
</div>
```

### Link
```vue
<a href="#" class="text-emerald-600 hover:text-emerald-700 font-medium">
  View details
</a>
```

### Badge
```vue
<span class="px-2 py-1 bg-emerald-100 text-emerald-700 
             text-xs font-semibold rounded-full">
  Active
</span>
```

### Input
```vue
<input type="text" 
       class="border border-gray-300 rounded-lg px-4 py-2
              focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
```

## 📐 Spacing (8px Grid)

```
gap-2  → 8px   (tight)
gap-4  → 16px  (normal)
gap-6  → 24px  (comfortable)
gap-8  → 32px  (spacious)

p-4   → 16px padding (mobile cards)
p-6   → 24px padding (desktop cards)
p-8   → 32px padding (sections)
```

## 🔤 Typography

```vue
<!-- Page Title -->
<h1 class="text-4xl md:text-5xl font-bold text-gray-900">
  
<!-- Section Title -->
<h2 class="text-3xl md:text-4xl font-bold text-gray-900">

<!-- Card Title -->
<h3 class="text-xl font-semibold text-gray-900">

<!-- Section Label (Overline) -->
<p class="text-sm font-semibold text-emerald-600 uppercase tracking-wider">
  
<!-- Body Text -->
<p class="text-base text-gray-700">

<!-- Secondary Text -->
<p class="text-sm text-gray-600">
```

## 🏗️ Layout

```vue
<!-- Container -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

<!-- Section -->
<section class="py-12 md:py-16">

<!-- Grid (1→2→3 columns) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
```

## 🎯 Status Colors

```vue
<!-- Success/Active -->
<span class="bg-emerald-100 text-emerald-700">Active</span>

<!-- Warning/Pending -->
<span class="bg-yellow-100 text-yellow-700">Pending</span>

<!-- Error/Rejected -->
<span class="bg-red-100 text-red-700">Rejected</span>

<!-- Info -->
<span class="bg-blue-100 text-blue-700">Info</span>

<!-- Inactive -->
<span class="bg-gray-100 text-gray-600">Inactive</span>
```

## 🌊 Shadows

```
shadow-sm   → Subtle cards
shadow-md   → Default cards  
shadow-lg   → Hover/Featured cards
shadow-xl   → Modals/Overlays
```

## 🎬 Transitions

```
transition-colors duration-200    → Fast color changes
transition-shadow duration-300    → Card hover shadows
transition-all duration-300       → Complex animations
```

## ⚡ Quick Dos & Don'ts

### ✅ DO
- Use `emerald-600` for ALL primary CTAs
- Use `gray-900` for headings
- Use `gray-600` for body text
- Use `rounded-xl` for cards
- Use `gap-6` for card grids
- Add `hover:shadow-lg` to cards

### ❌ DON'T
- Don't use `brand-red-600` for primary actions
- Don't use `indigo` for brand elements
- Don't mix multiple primary colors
- Don't forget focus rings on inputs
- Don't use hard-coded colors (use Tailwind classes)

## 🔗 Resources

- **Full Guide:** `docs/DESIGN_SYSTEM_OFFICIAL.md`
- **Migration Report:** `DESIGN_MIGRATION_COMPLETE.md`
- **Live Site:** https://bideshgomon.com/

---

**Primary Brand Color:** 🟢 Emerald Green (`emerald-600` / #64ac44)
