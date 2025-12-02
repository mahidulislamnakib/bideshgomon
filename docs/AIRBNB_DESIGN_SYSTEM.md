# Airbnb-Inspired Design System for BideshGomon

## 🎨 Overview
Complete redesign of BideshGomon platform with Airbnb-style modern UI using emerald green brand colors. This implementation provides a cohesive, user-friendly experience across all pages.

---

## 📁 File Structure

```
resources/js/
├── Composables/
│   └── useDesignSystem.js          # Design tokens & utilities
├── Components/
│   └── Airbnb/
│       ├── SearchBar.vue           # Expandable search component
│       ├── ServiceCard.vue         # Listing card with image gallery
│       ├── CategoryPills.vue       # Horizontal scrolling categories
│       └── FiltersModal.vue        # Advanced filters modal
└── Pages/
    └── AirbnbHome.vue              # New homepage with complete design
```

---

## 🎯 Components

### 1. **SearchBar.vue**
**Location:** `resources/js/Components/Airbnb/SearchBar.vue`

**Features:**
- ✅ Compact state with "Where to?" button
- ✅ Expands to full search bar on click
- ✅ 3 search fields: Destination, Service Type, Budget
- ✅ Dropdown suggestions with 8 popular countries
- ✅ Click-outside to close
- ✅ Mobile responsive

**Usage:**
```vue
<SearchBar />
```

**Integration:**
- Used in header (desktop)
- Separate mobile version below header
- Auto-focuses destination input on expand

---

### 2. **ServiceCard.vue**
**Location:** `resources/js/Components/Airbnb/ServiceCard.vue`

**Features:**
- ✅ Image gallery with navigation arrows
- ✅ Image indicator dots (bottom center)
- ✅ Wishlist heart button (top right)
- ✅ Badge overlay (top left) - "Bestseller", "High Success"
- ✅ Location with map pin icon
- ✅ Star rating with review count
- ✅ Price display in Bangladesh format (৳)
- ✅ Feature pills (up to 3 shown)
- ✅ Hover effects (scale, shadow)
- ✅ Click to navigate to service detail

**Props:**
```javascript
{
  service: {
    id: number,
    title: string,
    location: string,
    images: string[],
    price: number,
    rating: number,
    reviews: number,
    duration: string,
    type: string,
    badge: string,          // Optional
    features: string[],     // Optional
    isWishlisted: boolean   // Optional
  }
}
```

**Usage:**
```vue
<ServiceCard
    :service="serviceData"
    @wishlist-toggle="handleWishlist"
/>
```

---

### 3. **CategoryPills.vue**
**Location:** `resources/js/Components/Airbnb/CategoryPills.vue`

**Features:**
- ✅ Horizontal scrolling categories
- ✅ Icon + text for each category
- ✅ Active state highlighting
- ✅ Left/right scroll buttons
- ✅ "Filters" button on right
- ✅ Auto-hide scroll buttons when not needed
- ✅ Mobile responsive (hides scroll buttons)

**Props:**
```javascript
{
  categories: [
    { id: string, name: string, icon: string }
  ],
  initialCategory: string // default: 'all'
}
```

**Events:**
- `@category-change` - Emits selected category ID
- `@filters-open` - Triggers filters modal

**Usage:**
```vue
<CategoryPills
    :categories="categoriesList"
    @category-change="handleCategoryChange"
    @filters-open="showFiltersModal = true"
/>
```

---

### 4. **FiltersModal.vue**
**Location:** `resources/js/Components/Airbnb/FiltersModal.vue`

**Features:**
- ✅ Full-screen modal with backdrop
- ✅ **Price Range:** Min/max inputs + slider
- ✅ **Service Type:** Checkbox grid (6 types)
- ✅ **Processing Time:** Radio buttons (4 options)
- ✅ **Success Rate:** Slider (0-100%)
- ✅ **Verified Only:** Toggle switch
- ✅ "Clear all" button
- ✅ Live results count
- ✅ Smooth transitions (HeadlessUI)

**Props:**
```javascript
{
  isOpen: boolean,
  initialFilters: object
}
```

**Events:**
- `@close` - Close modal
- `@apply` - Apply filters and close

**Usage:**
```vue
<FiltersModal
    :is-open="showModal"
    :initial-filters="currentFilters"
    @close="showModal = false"
    @apply="handleFiltersApply"
/>
```

---

### 5. **useDesignSystem.js**
**Location:** `resources/js/Composables/useDesignSystem.js`

**Provides:**
- **Colors:** Primary (emerald), accent, neutral, status
- **Typography:** Font families, sizes, weights
- **Spacing:** Section, container, grid gaps
- **Border Radius:** 9 sizes (sm to 3xl)
- **Shadows:** Airbnb-style soft shadows (card, cardHover)
- **Transitions:** Fast, base, slow, bounce
- **Breakpoints:** sm, md, lg, xl, 2xl

**Usage:**
```javascript
import { useDesignSystem } from '@/Composables/useDesignSystem'

const { colors, shadows, borderRadius } = useDesignSystem()
```

---

## 🏠 Homepage (AirbnbHome.vue)

**Location:** `resources/js/Pages/AirbnbHome.vue`

### Sections:

#### 1. **Sticky Header**
- Logo + BideshGomon text
- Desktop search bar (centered)
- "Become a Partner" link
- Language/Globe button
- User menu dropdown (if authenticated)
- Login/Signup buttons (if guest)
- Mobile search bar (below header)

#### 2. **Category Pills Bar**
- 9 categories: All, Visa, Study, Work, Tourist, Migration, Business, Medical, Attestation
- Icons: 🌍 ✈️ 🎓 💼 🏖️ 🏠 📊 🏥 📋
- Horizontal scroll
- Filters button

#### 3. **Hero Section**
- Full-width background image
- Gradient overlay
- Large heading: "Your Journey Abroad Starts Here"
- Subheading: "Trusted by 50,000+ Bangladeshis"
- 2 CTA buttons: "Explore Services" + "Watch Success Stories"
- Height: 70vh (min 600px)

#### 4. **Trust Indicators**
- 4 stats: 50,000+ Clients, 100+ Countries, 95% Success, 24/7 Support
- Gray background
- Emerald green numbers

#### 5. **Popular Services Grid**
- Heading: "Popular Services"
- 4-column grid (responsive)
- ServiceCard components
- "View All" button
- Mock data: USA Student, Canada PR, etc.

#### 6. **Top Destinations**
- Heading: "Top Destinations"
- 4-column grid
- Large image cards with country names
- USA, Canada, Australia, UK
- Hover scale effect

#### 7. **How It Works**
- 3-step process
- Numbered circles (emerald)
- Center-aligned
- White background

#### 8. **Testimonials**
- 3-column grid
- 5-star ratings
- Avatar + name + destination
- Quote text
- White cards on gray background

#### 9. **CTA Section**
- Emerald gradient background
- "Ready to Start Your Journey?"
- 2 buttons: "Get Started Free" + "Browse Services"
- White text

#### 10. **Footer**
- Reuses existing Footer component

---

## 🎨 Design Tokens

### Colors
```javascript
Primary (Emerald):
- 500: #10b981 (main)
- 600: #059669 (hover)
- 700: #047857 (active)

Accent:
- Pink: #FF385C (Airbnb signature)
- Orange: #FF5A5F
- Teal: #00A699

Neutral:
- 50-900: Gray scale

Status:
- Success: #10b981
- Warning: #f59e0b
- Error: #ef4444
- Info: #3b82f6
```

### Shadows (Airbnb-Style)
```css
card: 0 6px 16px rgba(0, 0, 0, 0.12)
cardHover: 0 12px 32px rgba(0, 0, 0, 0.18)
```

### Border Radius
```css
lg: 0.75rem
xl: 1rem
2xl: 1.5rem
3xl: 2rem
full: 9999px (pills/circles)
```

---

## 🚀 Demo Access

**Public Demo (No Auth):**
- URL: `/demo/airbnb-design`
- Route name: `demo.airbnb`

**Test the following:**
1. ✅ Expand/collapse search bar
2. ✅ Search suggestions dropdown
3. ✅ Category pills scrolling
4. ✅ Open filters modal
5. ✅ Service card image navigation
6. ✅ Wishlist toggle
7. ✅ User menu dropdown (if logged in)
8. ✅ Mobile responsive layout

---

## 📱 Responsive Breakpoints

```javascript
sm: 640px   // Mobile landscape
md: 768px   // Tablet
lg: 1024px  // Desktop
xl: 1280px  // Large desktop
2xl: 1536px // Extra large
```

### Mobile Optimizations:
- Search bar full width
- Category pills hide scroll buttons
- Service grid: 1 column → 2 → 3 → 4
- Hero text scales down
- Trust indicators: 2 columns
- Testimonials: 1 column stacked

---

## 🔗 Integration Guide

### Replace Current Homepage
1. **Update `routes/web.php`:**
```php
Route::get('/', function () {
    return Inertia::render('AirbnbHome');
})->name('welcome');
```

2. **Update placeholder images:**
```bash
public/images/
├── hero-migration.jpg          # Hero background
├── services/
│   ├── usa-1.jpg
│   ├── usa-2.jpg
│   ├── canada-1.jpg
├── destinations/
│   ├── usa.jpg
│   ├── canada.jpg
│   ├── australia.jpg
│   ├── uk.jpg
└── avatars/
    ├── user1.jpg
    ├── user2.jpg
    └── user3.jpg
```

3. **Connect to real data:**
```javascript
// In AirbnbHome.vue, replace mock data with:
const props = defineProps({
    featuredServices: Array,
    topDestinations: Array,
    testimonials: Array,
    categories: Array,
})
```

4. **Create controller:**
```php
// app/Http/Controllers/HomeController.php
public function index()
{
    return Inertia::render('AirbnbHome', [
        'featuredServices' => ServiceModule::featured()->limit(8)->get(),
        'topDestinations' => Country::popular()->limit(4)->get(),
        'testimonials' => Testimonial::latest()->limit(3)->get(),
        'categories' => ServiceCategory::all(),
    ]);
}
```

---

## 🎯 Next Steps

### Priority 1: Backend Integration
- [ ] Create `featured` scope for ServiceModule
- [ ] Add `popular` scope for Country model
- [ ] Create Testimonial model & migration
- [ ] Seed real service data
- [ ] Add real images to `/public/images/`

### Priority 2: Additional Pages
- [ ] Service listing page (with filters working)
- [ ] Service detail page (full Airbnb-style)
- [ ] User dashboard redesign
- [ ] Profile page redesign
- [ ] Booking flow redesign

### Priority 3: Features
- [ ] Wishlist functionality (save to database)
- [ ] Search autocomplete with API
- [ ] Filter persistence (URL params)
- [ ] Image lazy loading
- [ ] Skeleton loaders
- [ ] Pagination for services grid

### Priority 4: Performance
- [ ] Image optimization (WebP, lazy load)
- [ ] Code splitting (dynamic imports)
- [ ] Caching strategy
- [ ] CDN for static assets

---

## 🐛 Known Issues

1. **Placeholder Images:** Currently using `/images/` paths that don't exist. Need to add real images or use placeholders like `https://via.placeholder.com/800x600`

2. **Route Dependencies:** Some routes referenced (e.g., `destinations.show`) don't exist yet. Create these or update links.

3. **Static Analysis Warnings:** Pre-existing lint warnings in `routes/web.php` (unrelated to this implementation)

---

## 📊 Component Dependencies

```bash
# Installed (already in project)
- @headlessui/vue (FiltersModal)
- @heroicons/vue (All icons)
- @inertiajs/vue3 (Navigation)

# Custom (created in this implementation)
- useBangladeshFormat (Currency formatting)
- useDesignSystem (Design tokens)
```

---

## 🎨 Brand Consistency

**BideshGomon Brand Colors:**
- **Primary:** Emerald Green (#10b981) - Trust, growth, success
- **Secondary:** White & Gray - Clean, professional
- **Accent:** Used sparingly for CTAs and highlights

**Typography:**
- **Headings:** Bold, large sizes (2xl to 7xl)
- **Body:** Regular weight, comfortable reading size
- **Font:** Inter (system fallback)

**Imagery:**
- **Hero:** Inspirational travel/migration images
- **Services:** High-quality country landmarks
- **People:** Diverse, authentic testimonials

---

## 📞 Support

For questions or customization requests, refer to:
- Main instructions: `PROJECT_SUMMARY.md`
- Design system: `useDesignSystem.js`
- Component examples: `/demo/airbnb-design`

---

**Created:** December 2, 2025  
**Version:** 1.0.0  
**Status:** ✅ Core components complete, ready for backend integration
