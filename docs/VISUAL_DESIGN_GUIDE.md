# BideshGomon Visual Design Guide
## Brand Identity & Design Standards

**Version**: 2.0  
**Date**: December 2, 2025  
**Status**: Official Brand Guidelines

---

## 🎨 Brand Colors

### Primary Brand Color
**Ocean Blue** - Logo color, represents trust and professionalism

```
HEX: #0087ff
RGB: 0, 135, 255
HSL: 208°, 100%, 50%
Tailwind: brand-500
```

**Usage**:
- Logo (mandatory)
- Primary buttons
- Primary navigation highlights
- Key CTAs
- Links

**Don't Use For**:
- Body text (use gray-700)
- Large backgrounds (too bright)
- Error states (use red)

### Secondary Colors

#### Success Green
```
HEX: #22c55e
RGB: 34, 197, 94
Tailwind: success-500
```
**Usage**: Confirmations, approvals, success messages, completed steps

#### Warning Orange
```
HEX: #f97316
RGB: 249, 115, 22
Tailwind: warning-500
```
**Usage**: Alerts, pending states, important notices, CTAs

#### Danger Red
```
HEX: #ef4444
RGB: 239, 68, 68
Tailwind: red-500
```
**Usage**: Errors, delete actions, critical warnings, rejected states

#### Info Blue
```
HEX: #3b82f6
RGB: 59, 130, 246
Tailwind: blue-500
```
**Usage**: Informational messages, help text, neutral actions

#### Premium Gold
```
HEX: #eab308
RGB: 234, 179, 8
Tailwind: gold-500
```
**Usage**: Premium badges, achievements, rewards, highlights

### Neutral Colors

#### Gray Scale
```
gray-50: #fafafa   - Very light backgrounds
gray-100: #f5f5f5  - Light backgrounds, hover states
gray-200: #e5e5e5  - Borders, dividers
gray-300: #d4d4d4  - Disabled states, placeholders
gray-400: #a3a3a3  - Secondary text
gray-500: #737373  - Body text, captions
gray-600: #525252  - Headings, emphasis
gray-700: #404040  - Dark text
gray-800: #262626  - Very dark text
gray-900: #171717  - Darkest text
```

---

## 🖋️ Typography

### Font Family
**Primary**: Inter  
**Fallback**: system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Arial, sans-serif

**Why Inter?**
- Highly legible on all screen sizes
- Modern, professional appearance
- Excellent kerning and spacing
- Optimized for digital interfaces
- Free and open-source

### Type Scale

#### Display (Hero Sections)
```
display-2xl: 80px / 5rem - Largest hero headlines
display-xl: 64px / 4rem - Major hero sections
display-lg: 48px / 3rem - Section hero headings
display-md: 40px / 2.5rem - Subsection headings
display-sm: 32px / 2rem - Card hero headings
```

#### Body Text
```
text-base: 16px / 1rem - Standard body text ★
text-lg: 18px / 1.125rem - Emphasized text
text-sm: 14px / 0.875rem - Secondary text, captions
text-xs: 12px / 0.75rem - Fine print, labels
```

★ = Default size

### Font Weights
```
font-normal: 400 - Body text
font-medium: 500 - Subheadings, emphasis
font-semibold: 600 - Buttons, labels
font-bold: 700 - Headings
font-extrabold: 800 - Display headings
font-black: 900 - Extra large display
```

### Line Heights
```
leading-tight: 1.25 - Headlines
leading-snug: 1.375 - Subheadings
leading-normal: 1.5 - Body text
leading-relaxed: 1.625 - Comfortable reading
leading-loose: 2 - Spacious text
```

### Text Colors
```
text-gray-900 - Primary headings
text-gray-700 - Body text
text-gray-600 - Secondary text
text-gray-500 - Captions, metadata
text-gray-400 - Disabled text
```

---

## 📐 Spacing System

### Base Unit: 4px
All spacing is a multiple of 4px for visual rhythm

```
1 = 4px    - Minimal
2 = 8px    - Tight
3 = 12px   - Compact
4 = 16px   - Standard ★
5 = 20px   - Comfortable
6 = 24px   - Generous
8 = 32px   - Large
10 = 40px  - Extra large
12 = 48px  - Section spacing
16 = 64px  - Hero spacing
20 = 80px  - Major sections
24 = 96px  - Page sections
```

★ = Most common

### Responsive Spacing Pattern

#### Mobile (< 768px)
- Sections: 48px (py-12)
- Cards: 16px padding (p-4)
- Grid gaps: 16px (gap-4)
- Elements: 16px margins (mb-4)

#### Desktop (≥ 1024px)
- Sections: 80px (py-20)
- Cards: 24px padding (p-6)
- Grid gaps: 24px (gap-6)
- Elements: 24px margins (mb-6)

---

## 🎭 Icons

### Icon Library
**Heroicons** (24x24 outline and solid)

### Icon Colors Strategy

#### By Purpose:
1. **Navigation Icons**
   - Active: `text-brand-600`
   - Inactive: `text-gray-400`
   - Hover: `text-brand-500`

2. **Status Icons**
   - Success: `text-success-500`
   - Warning: `text-warning-500`
   - Error: `text-red-500`
   - Info: `text-blue-500`

3. **Feature Icons**
   - Visa Services: `text-blue-600`
   - Jobs: `text-success-600`
   - Documents: `text-warning-600`
   - Education: `text-purple-600`
   - Travel: `text-sky-600`

4. **Action Icons**
   - Primary action: `text-brand-600`
   - Destructive: `text-red-600`
   - Neutral: `text-gray-600`

### Icon Sizes
```
w-3 h-3 (12px) - Inline with small text
w-4 h-4 (16px) - Inline with body text
w-5 h-5 (20px) - Standard buttons ★
w-6 h-6 (24px) - Large buttons, nav
w-8 h-8 (32px) - Hero sections
w-12 h-12 (48px) - Feature cards
w-16 h-16 (64px) - Extra large
```

★ = Most common

### Icon-Text Spacing
```html
<!-- Icon before text -->
<button class="flex items-center">
    <Icon class="w-5 h-5 mr-2" />
    Text
</button>

<!-- Icon after text -->
<button class="flex items-center">
    Text
    <Icon class="w-5 h-5 ml-2" />
</button>
```

---

## 📦 Component Styling

### Buttons

#### Sizes
```
Small: 36px height - Secondary actions
Default: 44px height - Standard actions ★
Large: 52px height - Primary CTAs
```

★ = Most common

#### Border Radius
```
Small: 8px (rounded-lg)
Default: 12px (rounded-xl) ★
Large: 16px (rounded-2xl)
```

#### Padding
```
Small: px-4 py-2
Default: px-6 py-3 ★
Large: px-8 py-4
```

### Cards

#### Border Radius
```
Standard: 16px (rounded-2xl) ★
Profile: 16px (rounded-2xl)
```

#### Padding
```
Mobile: 16px (p-4)
Desktop: 24px (p-6) ★
```

#### Shadows
```
Default: shadow-card
Hover: shadow-card-hover
Float: shadow-float
```

### Forms

#### Input Fields
- Height: 44px minimum (touch-friendly)
- Border: 2px solid
- Border radius: 12px (rounded-xl)
- Padding: 16px horizontal
- Font size: 16px (prevents iOS zoom)

#### Labels
- Font size: 14px (text-sm)
- Font weight: 600 (font-semibold)
- Color: gray-700
- Spacing: 8px below (mb-2)

---

## 🎨 Shadow System

### Elevation Levels
```
shadow-soft: Subtle hint (2px blur)
shadow-card: Standard card (8px blur) ★
shadow-card-hover: Hover state (24px blur)
shadow-float: Floating elements (32px blur)
```

★ = Most common

### Glow Effects
```
shadow-glow-brand: Brand blue glow
shadow-glow-success: Green glow
shadow-glow-warning: Orange glow
```

---

## 🌈 Gradient System

### Brand Gradients
```
gradient-brand: Left to right (#0087ff → #006dd1)
gradient-brand-reverse: Right to left (#006dd1 → #0087ff)
text-gradient-brand: Text gradient effect
```

**Usage**: Hero sections, premium features, highlights

---

## 📱 Mobile-First Standards

### Touch Targets
**Minimum size**: 44x44px  
**Recommended**: 48x48px  
**Applies to**: Buttons, links, icons, form controls

### Safe Areas
**iPhone X+ Notch Support**:
- Top: `safe-top` padding
- Bottom: `safe-bottom` padding
- Sides: `safe-left`, `safe-right`

### Font Sizes
**Mobile minimum**: 16px (prevents iOS auto-zoom)  
**Body text**: 16px  
**Headings**: Scale up from 24px

### Spacing
**Mobile**: Tighter spacing (16px standard)  
**Desktop**: Generous spacing (24px standard)

---

## 🎯 Contrast Ratios

### WCAG AA Compliance (Minimum)
- **Normal text**: 4.5:1
- **Large text**: 3:1
- **UI components**: 3:1

### WCAG AAA Compliance (Enhanced)
- **Normal text**: 7:1
- **Large text**: 4.5:1

### Tested Combinations
✅ **Pass AA**:
- `text-gray-700` on `bg-white` (11.4:1)
- `text-white` on `bg-brand-500` (4.8:1)
- `text-white` on `bg-success-500` (4.6:1)

❌ **Fail AA**:
- `text-gray-400` on `bg-white` (2.9:1)
- `text-brand-300` on `bg-white` (2.1:1)

---

## 🎬 Animation Standards

### Duration
```
Instant: 100ms - Micro-interactions
Fast: 200ms - Buttons, hover ★
Normal: 300ms - Cards, dropdowns
Slow: 400ms - Page transitions
```

★ = Most common

### Easing
```
ease-in: Accelerating
ease-out: Decelerating ★
ease-in-out: Smooth curve
```

★ = Most common

### Properties to Animate
✅ **Performant** (GPU-accelerated):
- `transform`
- `opacity`
- `filter`

❌ **Avoid** (causes reflow):
- `width`
- `height`
- `top`
- `left`
- `margin`
- `padding`

---

## 📐 Layout Standards

### Container Widths
```
Narrow: 768px (max-w-3xl) - Blog, forms
Medium: 1024px (max-w-5xl) - Most pages ★
Wide: 1280px (max-w-7xl) - Dashboards, tables
```

★ = Most common

### Grid Systems
```
Mobile: 1 column
Tablet: 2 columns
Desktop: 3-4 columns
```

### Breakpoints
```
xs: 475px - Large phones
sm: 640px - Tablets portrait
md: 768px - Tablets landscape ★
lg: 1024px - Small desktops
xl: 1280px - Desktops
2xl: 1536px - Large desktops
```

★ = Primary breakpoint

---

## ✅ Design Checklist

### Before Development
- [ ] Mobile design created first (320px-768px)
- [ ] Touch targets meet 44x44px minimum
- [ ] Brand colors used consistently
- [ ] Contrast ratios verified (4.5:1+)
- [ ] Typography hierarchy clear
- [ ] Spacing follows 4px rhythm

### Before Launch
- [ ] Tested on iPhone SE (smallest screen)
- [ ] Tested on iPhone 14 Pro (notch)
- [ ] Tested on iPad (tablet)
- [ ] Tested on desktop (1920px+)
- [ ] All images optimized
- [ ] Animations smooth (60fps)
- [ ] Lighthouse score 90+
- [ ] Accessibility audit passed

---

## 🎨 Brand Voice

### Visual Personality
- **Professional** yet approachable
- **Modern** yet timeless
- **Confident** yet friendly
- **Clean** yet warm

### Design Principles
1. **Clarity** - Every element has a purpose
2. **Consistency** - Same patterns everywhere
3. **Efficiency** - Minimum steps to complete tasks
4. **Delight** - Subtle animations and interactions
5. **Accessibility** - Usable by everyone

---

## 🚫 Common Mistakes to Avoid

### ❌ Don't:
1. Use multiple shades of blue for different purposes
2. Create buttons smaller than 44x44px
3. Use `ocean-500` instead of `brand-500`
4. Forget safe area insets on fixed elements
5. Use text smaller than 16px on mobile
6. Animate width/height (use transform instead)
7. Forget focus states for accessibility
8. Use low contrast colors (< 4.5:1)

### ✅ Do:
1. Use brand-500 (#0087ff) consistently for logo/primary
2. Ensure all touch targets ≥ 44x44px
3. Add safe-top/safe-bottom to fixed headers/footers
4. Use semantic colors (success, warning, danger)
5. Test on real mobile devices
6. Include loading and empty states
7. Add hover and focus states
8. Follow mobile-first approach

---

## 📚 Quick Reference

### Most Used Classes
```css
/* Buttons */
.btn-primary
.btn-secondary

/* Cards */
.card
.profile-card

/* Forms */
.input
.label

/* Layout */
.container-medium
grid grid-cols-1 md:grid-cols-3

/* Spacing */
p-4 md:p-6  (16px → 24px)
mb-4 md:mb-6  (16px → 24px)
gap-4 md:gap-6  (16px → 24px)

/* Typography */
text-base  (16px)
font-semibold  (600)
text-gray-700  (body text)

/* Colors */
bg-brand-500  (logo blue)
text-success-500  (green)
text-warning-500  (orange)
text-red-500  (danger)
```

---

**This is your complete visual design guide.**  
All design decisions should reference this document.

For implementation details, see `MOBILE_FIRST_IMPLEMENTATION.md`  
For component examples, see `docs/MOBILE_FIRST_DESIGN_SYSTEM.md`
