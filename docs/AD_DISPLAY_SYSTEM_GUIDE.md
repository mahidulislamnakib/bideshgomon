# Ad Display System - User Guide

## Overview
The BideshGomon platform now includes a comprehensive ad management system that allows admins to create, manage, and display targeted advertisements throughout the platform.

## How It Works

### 1. Admin Creates Ads
Go to **Admin Dashboard → Ad Management** (`http://127.0.0.1:8000/admin/ads`)

**Create New Ad:**
- **Title**: Ad headline
- **Body**: Description text
- **Image URL**: Link to ad image
- **CTA URL**: Where the ad links to
- **CTA Text**: Button text (e.g., "Learn More", "Get Started")
- **Placement**: Where ad appears (sidebar, banner, inline, etc.)
- **Target Pages**: Specific pages to show ad (comma-separated: "dashboard,profile,services")
- **Target Roles**: Which users see it (user, admin, agency, consultant)
- **Target Devices**: Desktop, tablet, or mobile
- **Scheduling**: Start and end dates

### 2. Ads Display Automatically
Ads appear on pages where the `<AdDisplay>` component is placed. The component:
- Fetches the best matching ad based on criteria
- Records impressions when ad is visible
- Records clicks when user interacts
- Tracks performance metrics

### 3. Check Ad Performance
Go to **Ad Analytics** from the ad listing page to see:
- Total impressions (views)
- Total clicks
- Click-Through Rate (CTR)
- Top performing pages
- Device breakdown

---

## How to Check Ads on User Dashboard

### Step 1: Create a Test Ad

1. Navigate to: `http://127.0.0.1:8000/admin/ads`
2. Click **"Create New Ad"**
3. Fill in the form:

**Example Banner Ad:**
```
Title: Welcome to BideshGomon!
Body: Complete your profile to unlock all services
Image URL: (leave empty or add image)
CTA URL: /profile
CTA Text: Complete Profile
Placement: banner
Priority: 1
Start Date: Today
Status: active
Target Pages: dashboard
Target Roles: ✓ user
Target Devices: ✓ desktop ✓ tablet ✓ mobile
```

4. Click **"Create Ad"**

### Step 2: View the Ad

1. **Logout from admin** (important!)
2. **Login as a regular user** or create a new user account
3. Go to your dashboard: `http://127.0.0.1:8000/dashboard`
4. You should see:
   - **Banner ad** below the "Profile Strength" card
   - **Sidebar ad** on the right side of "Your Services & Tools" section

### Step 3: Test Different Placements

Create multiple ads with different placements:

**Sidebar Ad Example:**
```
Title: Need Help?
Body: Get expert guidance for your visa application
Placement: sidebar
Target Pages: dashboard
```

**Inline Ad Example:**
```
Title: Special Offer!
Body: Get 20% off on travel insurance
Placement: inline
Target Pages: dashboard,services
```

---

## Ad Placement Types

### 1. **Banner** (`placement="banner"`)
- Full-width horizontal ad
- Appears prominently at top of content
- Best for announcements or promotions
- Current location: Below profile completion card

### 2. **Sidebar** (`placement="sidebar"`)
- Vertical ad on the side
- Doesn't interrupt content flow
- Best for persistent promotions
- Current location: Right side of services grid

### 3. **Inline** (`placement="inline"`)
- Appears between content sections
- Blends with page content
- Good for related offers

### 4. **Sticky Bottom** (`placement="sticky_bottom"`)
- Fixed at bottom of screen
- Always visible while scrolling
- Use sparingly (can be intrusive)

### 5. **Modal** (`placement="modal"`)
- Popup overlay
- Requires manual triggering
- Use for important announcements

### 6. **Empty State** (`placement="empty_state"`)
- Shows when lists are empty
- Good for first-time user guidance

---

## Adding Ads to Other Pages

To add ads to any page, import and use the `AdDisplay` component:

```vue
<script setup>
import AdDisplay from '@/Components/AdDisplay.vue';
</script>

<template>
    <div>
        <!-- Your page content -->
        
        <!-- Banner Ad -->
        <AdDisplay 
            placement="banner" 
            page="services"
        />
        
        <!-- Sidebar Ad -->
        <div class="grid grid-cols-4 gap-6">
            <div class="col-span-3">
                <!-- Main content -->
            </div>
            <div class="col-span-1">
                <AdDisplay 
                    placement="sidebar" 
                    page="services"
                />
            </div>
        </div>
    </div>
</template>
```

---

## Current Implementation

### Pages with Ads:
✅ **Dashboard** (`/dashboard`)
- Banner ad below profile completion
- Sidebar ad next to services grid

### To Add Ads to More Pages:
Simply add the `<AdDisplay>` component to any Vue page:

1. **Services Page** (`resources/js/Pages/Services/Index.vue`)
2. **Profile Page** (`resources/js/Pages/Profile/Show.vue`)
3. **Jobs Page** (`resources/js/Pages/Jobs/Index.vue`)
4. **Applications Page** (`resources/js/Pages/User/Applications/Index.vue`)

---

## Troubleshooting

### Ads Not Showing?

**Check These:**

1. **Ad Status**: Must be "active"
2. **Dates**: Current date must be between Start Date and End Date
3. **Target Pages**: Must include the page you're viewing (e.g., "dashboard")
4. **Target Roles**: Must match your user role
5. **Target Devices**: Must match your device type
6. **Priority**: Higher priority ads show first (1 is highest)

### Console Errors?

Open browser console (F12) and look for:
- `Failed to fetch ad` - API issue
- `Failed to record impression` - Tracking issue
- Fix by checking Laravel logs: `storage/logs/laravel.log`

### Ad Blocker Issues?

If using ad blocker extensions:
- Whitelist `localhost` or `127.0.0.1`
- OR disable ad blocker for local development
- Ad files may be blocked by browser extensions

---

## Analytics & Metrics

### View Performance:
1. Go to **Admin → Ad Management**
2. Click **Analytics icon** (chart) for any ad
3. See metrics:
   - Impressions trend (last 30 days)
   - Clicks trend (last 30 days)
   - CTR percentage
   - Top performing pages
   - Device breakdown

### Good CTR Rates:
- **< 1%**: Poor performance - review ad content
- **1-2%**: Average - consider improvements
- **2-5%**: Good - ad is effective
- **> 5%**: Excellent - keep it running!

---

## Best Practices

### 1. **Targeting**
- Don't target all pages - be specific
- Match content to page (e.g., visa ads on visa pages)
- Test different role targeting

### 2. **Scheduling**
- Set end dates for time-sensitive offers
- Use priority for urgent announcements
- Rotate ads regularly to avoid fatigue

### 3. **Content**
- Keep titles short (< 10 words)
- Clear, actionable CTA text
- Use high-quality images (1200x628px for banners)
- Test different messages

### 4. **Performance**
- Monitor CTR weekly
- Pause low-performing ads
- A/B test different versions
- Update content based on seasons/events

---

## API Endpoints

For developers or external integrations:

```javascript
// Fetch ad
POST /api/ads/fetch
{
    "placement": "banner",
    "page": "dashboard",
    "device_type": "desktop" // optional
}

// Record impression
POST /api/ads/impression
{
    "ad_id": 1,
    "page": "dashboard",
    "device_type": "desktop"
}

// Record click
POST /api/ads/click
{
    "ad_id": 1,
    "page": "dashboard",
    "device_type": "desktop"
}
```

---

## Support

For issues or questions:
- Check `storage/logs/laravel.log` for errors
- Review ad targeting criteria
- Ensure database has active ads
- Test with different user roles
- Clear browser cache if needed

---

**Created:** December 2025  
**Last Updated:** December 2025  
**Version:** 1.0
