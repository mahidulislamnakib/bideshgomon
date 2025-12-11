# Ad System - Quick Start Guide

## 🎉 System Overview

The BideshGomon Ad System is now **fully operational** with:
- ✅ Database tables created (ads, ad_impressions, ad_clicks)
- ✅ Backend models & controllers configured
- ✅ API endpoints ready (/api/ads/fetch, /impression, /click)
- ✅ Admin management routes (/admin/ads/*)
- ✅ Vue AdSlot component ready to use
- ✅ Sample data seeded (7 test ads)

---

## 📋 Current Sample Ads (Seeded)

1. **Special Visa Processing Service** (Sidebar, Active)
   - Target: user_dashboard, visa_applications
   - Desktop only, priority 90

2. **Free Visa Consultation** (Inline, Active)
   - Target: blog_posts, faq
   - All devices, priority 80

3. **Start Your Journey Today** (Empty State, Active)
   - Target: jobs_list
   - Priority 70

4. **Download Our Mobile App** (Sticky Bottom, Active)
   - Mobile only, priority 60

5. **Limited Time Offer: 20% Off** (Banner, Active)
   - Priority 100, all pages

6. **Summer Travel Packages** (Draft)
   - Not visible yet

7. **Weekend Workshop** (Paused)
   - Temporarily hidden

---

## 🚀 How to Use AdSlot Component

### Step 1: Import the Component

```vue
<script setup>
import AdSlot from '@/Components/AdSlot.vue';
</script>
```

### Step 2: Add to Your Template

```vue
<template>
  <!-- Your page content -->
  
  <!-- Add ad slot -->
  <AdSlot 
    slot="sidebar" 
    page="user_dashboard" 
    :dismissible="true"
  />
</template>
```

### Available Placements

| Placement | Description | Visibility |
|-----------|-------------|------------|
| `sidebar` | Sidebar card | Desktop only |
| `inline` | Between content | All devices |
| `empty_state` | Large hero card | All devices |
| `banner` | Top of page | All devices |
| `sticky_bottom` | Fixed bottom | Mobile only |
| `modal` | Popup overlay | All devices |

---

## 📍 Usage Examples

### 1. Sidebar Ad on Dashboard

**File:** `resources/js/Pages/Dashboard.vue`

```vue
<template>
  <AuthenticatedLayout>
    <Head title="Dashboard" />
    
    <div class="page-container">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Main Content (8 columns) -->
        <div class="lg:col-span-8">
          <PageHeader title="Dashboard" />
          
          <!-- Your dashboard widgets -->
          <div class="space-y-6">
            <!-- Wallet, Profile, etc. -->
          </div>
        </div>
        
        <!-- Sidebar (4 columns) -->
        <div class="lg:col-span-4 space-y-6">
          <!-- Ad Slot: Shows visa processing ad -->
          <AdSlot slot="sidebar" page="user_dashboard" />
          
          <!-- Quick Links, Notifications, etc. -->
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AdSlot from '@/Components/AdSlot.vue';
// ... other imports
</script>
```

### 2. Inline Ad Between Blog Posts

**File:** `resources/js/Pages/Blog/Index.vue`

```vue
<template>
  <div class="page-container">
    <div class="space-y-8">
      <!-- Blog post 1 -->
      <BlogPostCard :post="posts[0]" />
      
      <!-- Blog post 2 -->
      <BlogPostCard :post="posts[1]" />
      
      <!-- Ad Slot: Shows consultation ad -->
      <AdSlot slot="inline" page="blog_posts" />
      
      <!-- Blog post 3 -->
      <BlogPostCard :post="posts[2]" />
      
      <!-- More posts... -->
    </div>
  </div>
</template>

<script setup>
import AdSlot from '@/Components/AdSlot.vue';
// ... other imports
</script>
```

### 3. Empty State Ad (No Results)

**File:** `resources/js/Pages/Jobs/Index.vue`

```vue
<template>
  <div class="page-container">
    <PageHeader title="Job Listings" />
    
    <!-- If no jobs found -->
    <div v-if="jobs.length === 0">
      <!-- Ad Slot: Large hero-style ad -->
      <AdSlot slot="empty_state" page="jobs_list" />
    </div>
    
    <!-- Jobs list -->
    <div v-else class="space-y-4">
      <JobCard v-for="job in jobs" :key="job.id" :job="job" />
    </div>
  </div>
</template>

<script setup>
import AdSlot from '@/Components/AdSlot.vue';
// ... other imports
</script>
```

### 4. Global Sticky Ad (App Layout)

**File:** `resources/js/Layouts/AuthenticatedLayout.vue`

```vue
<template>
  <div class="min-h-screen bg-gray-100">
    <Navigation />
    
    <!-- Page content -->
    <main>
      <slot />
    </main>
    
    <!-- Global sticky ad (mobile only) -->
    <AdSlot slot="sticky_bottom" page="global" />
  </div>
</template>

<script setup>
import AdSlot from '@/Components/AdSlot.vue';
// ... other imports
</script>
```

### 5. Banner Ad at Top of Page

```vue
<template>
  <div class="page-container">
    <!-- Banner ad: full-width, top of page -->
    <AdSlot slot="banner" page="services" />
    
    <!-- Page content -->
    <PageHeader title="Our Services" />
    <!-- ... -->
  </div>
</template>
```

---

## 🔧 Admin Panel Access

### View All Ads
Navigate to: `/admin/ads`

Features:
- List all ads with stats (impressions, clicks, CTR)
- Search by title/body
- Filter by status (active, paused, draft, expired)
- Filter by placement (sidebar, inline, etc.)
- Toggle active/paused status
- Edit/Delete ads
- View analytics

### Create New Ad
Navigate to: `/admin/ads/create`

Required fields:
- **Title** (max 255 characters)
- **Placement** (sidebar, inline, empty_state, banner, sticky_bottom, modal)
- **Status** (draft, active, paused)

Optional fields:
- Body text
- Image (max 2MB)
- CTA URL & Button Text
- Start/End dates
- Priority (0-100, higher = shown first)
- Targeting (pages, roles, devices)

### Edit Existing Ad
Navigate to: `/admin/ads/{id}/edit`

- Update any field
- Replace image
- View performance stats inline
- Link to detailed analytics

---

## 📊 How Targeting Works

### Target Specific Pages
```php
// In admin form, enter comma-separated page identifiers:
user_dashboard, blog_posts, jobs_list
```

**Ad will ONLY show on those pages.**

### Target Specific Roles
```php
// Check role boxes in admin form
☑ User
☐ Admin
☐ Agency
☐ Consultant
```

**Ad will ONLY show to users with those roles.**

### Target Specific Devices
```php
// Check device boxes in admin form
☑ Desktop
☑ Tablet
☐ Mobile
```

**Ad will ONLY show on those devices.**

### No Targeting = Show Everywhere
If you leave all targeting options empty, the ad shows on **all pages, all roles, all devices**.

---

## 🎯 Ad Selection Logic

When a page requests an ad:

1. **Filter by placement** (e.g., `sidebar`)
2. **Filter by status** (only `active`)
3. **Filter by date range** (if start_at/end_at set)
4. **Filter by targeting** (page, role, device match OR empty targeting)
5. **Sort by priority** (highest first)
6. **Return first match**
7. **Cache for 1 hour** (per placement/page/role/device combo)

---

## 📈 Analytics Tracking

### Automatic Tracking

**Impressions**: Recorded when ad is 50% visible for 1 second (IntersectionObserver)

**Clicks**: Recorded when user clicks CTA button

**CTR**: Automatically calculated: `(clicks / impressions) × 100`

### View Analytics
Navigate to: `/admin/ads/{id}/analytics`

See:
- Total impressions & clicks
- CTR percentage
- Daily breakdown (last 30 days)
- Device/page performance

---

## 🧪 Testing the System

### 1. View Sample Ads on Frontend

Add AdSlot component to any page:

```vue
<AdSlot slot="sidebar" page="test_page" />
```

**Expected**: Should show "Special Visa Processing Service" ad (if on desktop)

### 2. Test Mobile Sticky Ad

1. Open any page on mobile device (or resize browser to <768px)
2. Look at bottom of screen
3. **Expected**: "Download Our Mobile App" sticky ad appears

### 3. Test Ad Dismissal

1. View any ad
2. Click the X button (top-right)
3. Refresh page
4. **Expected**: Ad stays hidden for 24 hours (stored in localStorage)

### 4. Test Admin Management

1. Go to `/admin/ads`
2. Click "Create Ad"
3. Fill form, upload image (optional)
4. Set placement to `inline`
5. Set status to `active`
6. Save
7. **Expected**: New ad appears in list

### 5. Test Toggle Status

1. In ad list, click the power icon on an active ad
2. **Expected**: Status changes to "paused", ad disappears from frontend

### 6. Test Analytics

1. View a few ads on frontend (impressions recorded)
2. Click CTA buttons (clicks recorded)
3. Go to `/admin/ads/{id}/analytics`
4. **Expected**: See impression/click counts

---

## 🔐 Security Notes

### Admin Routes
All `/admin/ads/*` routes require:
- Authentication (`auth` middleware)
- Admin role (`role:admin` middleware)

### API Routes
`/api/ads/*` routes are **public** (no auth required):
- `/api/ads/fetch` - Get ad for display
- `/api/ads/impression` - Record view
- `/api/ads/click` - Record click

**Why?**: Ads need to load for guest users

**Privacy**: IP addresses stored (consider hashing in production)

---

## ⚡ Performance Features

### Caching
- Ad queries cached for **1 hour** per targeting combo
- Cache auto-clears when ad saved/deleted
- File cache used (Redis tagging supported if configured)

### Lazy Loading
- IntersectionObserver: Ad images load only when visible
- 50% threshold: Impression only counted when half visible
- Non-blocking: Ads don't slow down page load

### Database Optimization
- Indexes on: status, placement, priority, ad_id + created_at
- Separate tables for impressions/clicks (no main table bloat)
- No `updated_at` on tracking tables (performance gain)

---

## 🐛 Troubleshooting

### Ad Not Showing

**Check:**
1. Is ad status `active`?
2. Is date range valid (if set)?
3. Does targeting match (page, role, device)?
4. Is placement correct for device (sidebar = desktop only)?
5. Clear cache: `php artisan cache:clear`

### Impression Not Recording

**Check:**
1. Ad must be 50% visible in viewport
2. Console errors (F12 Developer Tools)
3. API endpoint: `POST /api/ads/impression` should return 200

### Click Not Recording

**Check:**
1. CTA URL must be set
2. Console errors
3. API endpoint: `POST /api/ads/click` should return 200

### Image Not Showing

**Check:**
1. Run: `php artisan storage:link`
2. Image stored in `storage/app/public/ads/`
3. Accessible at `/storage/ads/{filename}`
4. Max file size: 2MB

---

## 🔄 Next Steps

### Recommended Enhancements

1. **Add Real Images**: Replace sample ads with actual promotional images
2. **Create More Ads**: Different placements, targeting combinations
3. **A/B Testing**: Create multiple ads for same placement, compare CTR
4. **Analytics Dashboard**: Chart.js integration for visual reports
5. **Scheduled Campaigns**: Set start/end dates for seasonal promotions
6. **Ad Templates**: Pre-designed layouts for common use cases
7. **Conversion Tracking**: Track beyond click (e.g., registration, purchase)

### Integration with Existing Pages

The AdSlot component is ready to use in **any** Vue page:

- **Dashboard**: Sidebar ad for services
- **Blog**: Inline ads between posts
- **Jobs**: Empty state ad when no results
- **Services**: Banner ad for promotions
- **Profile**: Sidebar ad for upgrades
- **Wallet**: Inline ad for top-up offers

**Simply import and add `<AdSlot />` wherever appropriate.**

---

## 📚 Documentation References

- **Full Implementation Guide**: `AD_SYSTEM_DOCUMENTATION.md`
- **Component API**: `resources/js/Components/AdSlot.vue`
- **Backend Logic**: `app/Models/Ad.php`
- **Admin Controllers**: `app/Http/Controllers/Admin/AdManagementController.php`
- **API Controllers**: `app/Http/Controllers/Api/AdController.php`

---

## ✅ System Status

| Component | Status |
|-----------|--------|
| Database Migration | ✅ Complete |
| Backend Models | ✅ Complete |
| API Controllers | ✅ Complete |
| Admin Controllers | ✅ Complete |
| Routes (API + Web) | ✅ Complete |
| Vue Component (AdSlot) | ✅ Complete |
| Admin UI (Index) | ✅ Complete |
| Admin UI (Create) | ✅ Complete |
| Admin UI (Edit) | ✅ Complete |
| Sample Data | ✅ Seeded |
| Documentation | ✅ Complete |

**The ad system is production-ready!** 🎉

Start using it by adding `<AdSlot />` components to your pages.
