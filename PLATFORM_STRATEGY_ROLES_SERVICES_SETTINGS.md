# BIDESHGOMON PLATFORM STRATEGY - ROLES, SERVICES & SETTINGS

**Generated**: December 2, 2025  
**Version**: 1.0  
**Status**: Strategic Planning Document  
**Market**: 🇧🇩 Bangladesh Migration Platform

---

## 🎯 EXECUTIVE SUMMARY

This document defines the **complete strategic framework** for BideshGomon's role-based access control, service tier system (Free/Paid/Premium), platform features, and comprehensive user settings architecture.

**Core Objectives**:
1. Maximize user value at every tier
2. Create clear upgrade paths
3. Ensure sustainable revenue model
4. Maintain Bangladesh market focus
5. Provide granular user control

---

## 👥 ROLE-BASED SYSTEM ARCHITECTURE

### 1. USER ROLES HIERARCHY

```
┌─────────────────────────────────────────────────────────────┐
│                         SUPER ADMIN                          │
│                    (System Administrator)                    │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                           ADMIN                              │
│              (Platform Administrator/Moderator)              │
└─────────────────────────────────────────────────────────────┘
                              ↓
            ┌─────────────────┴─────────────────┐
            ↓                                    ↓
┌───────────────────────────┐      ┌───────────────────────────┐
│         AGENCY            │      │       CONSULTANT          │
│   (Licensed Companies)    │      │  (Individual Experts)     │
└───────────────────────────┘      └───────────────────────────┘
            ↓                                    ↓
            └─────────────────┬─────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                           USER                               │
│              (Standard User - Job Seekers)                   │
│         Free | Premium | Enterprise Tiers                    │
└─────────────────────────────────────────────────────────────┘
```

---

### 2. ROLE DEFINITIONS & CAPABILITIES

#### 2.1 SUPER ADMIN (System Administrator)
**Purpose**: Complete system control and configuration

**Capabilities**:
- ✅ Full database access
- ✅ Create/edit/delete all content
- ✅ Manage all users and roles
- ✅ System configuration (payment gateways, APIs)
- ✅ View all financial transactions
- ✅ Access system logs and analytics
- ✅ Manage platform settings
- ✅ Override all restrictions
- ✅ Backup and restore operations
- ✅ Server configuration
- ✅ Feature flag management
- ✅ Emergency system maintenance

**Settings Access**: ALL

**Service Access**: ALL (bypass all restrictions)

**Revenue Share**: N/A (Platform owner)

---

#### 2.2 ADMIN (Platform Administrator)
**Purpose**: Day-to-day platform management and moderation

**Capabilities**:
- ✅ Manage users (suspend, verify, ban)
- ✅ Moderate content (blogs, reviews, directories)
- ✅ Approve/reject agency verifications
- ✅ Handle support tickets (Level 2)
- ✅ Review and approve visa applications
- ✅ Manage service modules (activate/deactivate)
- ✅ Process refunds
- ✅ Assign agencies to applications
- ✅ View analytics and reports
- ✅ Manage FAQs and help content
- ✅ Send bulk notifications
- ✅ Impersonate users (with audit log)
- ✅ Manage testimonials
- ✅ Configure SEO settings

**Restrictions**:
- ❌ Cannot access Super Admin settings
- ❌ Cannot modify payment gateway configs
- ❌ Cannot delete critical system data
- ❌ Cannot access server configurations

**Settings Access**: Platform settings, content management, user moderation

**Service Access**: ALL services (for review/moderation)

**Revenue Share**: Salary-based compensation

---

#### 2.3 AGENCY (Licensed Companies)
**Purpose**: Provide professional migration services to users

**Sub-Roles**:
```
Agency Owner → Agency Admin → Agency Staff → Agency Consultant
```

**Capabilities**:
- ✅ Create agency profile with verification
- ✅ Manage team members (add/remove staff)
- ✅ List services offered
- ✅ View assigned applications
- ✅ Submit quotes for applications
- ✅ Upload required documents for clients
- ✅ Communicate with clients
- ✅ Track application progress
- ✅ Receive payments through platform
- ✅ View earnings dashboard
- ✅ Manage agency resources (templates, checklists)
- ✅ Country/service specialization settings
- ✅ Set availability and working hours
- ✅ Generate invoices and receipts
- ✅ Access agency analytics

**Restrictions**:
- ❌ Cannot access other agencies' data
- ❌ Cannot modify platform settings
- ❌ Cannot directly contact users without assignment
- ❌ Must maintain minimum rating (3.5/5)
- ❌ Must complete verification before accepting clients

**Settings Access**: Agency profile, team management, service offerings, payment preferences

**Service Access**: Based on subscription tier + country assignments

**Revenue Share**: 
- **Standard**: 15% platform commission
- **Premium Agency**: 12% platform commission
- **Verified Elite Agency**: 10% platform commission

**Subscription Tiers**:

| Tier | Monthly Fee | Features |
|------|------------|----------|
| **Basic Agency** | ৳5,000/mo | 10 active applications, 3 team members, 5 countries |
| **Professional Agency** | ৳12,000/mo | 50 active applications, 10 team members, 15 countries |
| **Enterprise Agency** | ৳25,000/mo | Unlimited applications, unlimited team, all countries |

---

#### 2.4 CONSULTANT (Individual Experts)
**Purpose**: Independent migration consultants and advisors

**Capabilities**:
- ✅ Create consultant profile
- ✅ List expertise areas
- ✅ Receive one-on-one consultation requests
- ✅ Provide document reviews
- ✅ Offer profile assessments
- ✅ Conduct mock interviews
- ✅ Set hourly rates
- ✅ Manage appointment calendar
- ✅ Accept/decline bookings
- ✅ Receive payments directly
- ✅ View client history
- ✅ Generate consultation reports

**Restrictions**:
- ❌ Cannot form team
- ❌ Cannot handle full applications (only consultations)
- ❌ Cannot issue official documents
- ❌ Must maintain certification/credentials
- ❌ Limited to consultation services only

**Settings Access**: Consultant profile, availability, rates, specializations

**Service Access**: Consultation services, document review, profile assessment

**Revenue Share**: 
- **Standard Consultant**: 20% platform commission
- **Verified Expert**: 15% platform commission

**Subscription Tiers**:

| Tier | Monthly Fee | Features |
|------|------------|----------|
| **Solo Consultant** | ৳2,000/mo | 20 appointments/mo, 3 specializations |
| **Expert Consultant** | ৳5,000/mo | Unlimited appointments, all specializations, priority listing |

---

#### 2.5 USER (Standard User - Job Seekers)
**Purpose**: Individuals seeking migration/visa services

**Sub-Categories**:
```
Free User → Premium User → Enterprise User
```

**Base Capabilities** (All Users):
- ✅ Create profile
- ✅ Complete profile assessment
- ✅ Browse services
- ✅ View agency listings
- ✅ Read blogs and guides
- ✅ Submit basic applications
- ✅ Track application status
- ✅ Upload documents
- ✅ Receive notifications
- ✅ Access mobile app
- ✅ View public directory
- ✅ Join waitlists

**Tier-Specific Features**: See Section 3 (Service Tiers)

**Settings Access**: Personal profile, privacy, notifications, preferences

**Service Access**: Based on subscription tier

**Revenue Source**: Subscription fees + per-service charges

---

## 💰 SERVICE TIER SYSTEM (FREE, PAID, PREMIUM)

### 3. USER SUBSCRIPTION TIERS

#### 3.1 FREE TIER (৳0/month)
**Target**: Entry-level users, explorers, students

**Features**:
- ✅ Basic profile (up to 50% completion)
- ✅ Browse all services (view only)
- ✅ View agency listings
- ✅ Read all blogs and guides
- ✅ Access public FAQs
- ✅ Submit 1 application per month
- ✅ Upload up to 5 documents (10 MB total)
- ✅ Email notifications only
- ✅ Standard support (48-hour response)
- ✅ Access mobile app (limited)
- ✅ Basic profile assessment
- ✅ View visa requirements
- ✅ Public profile (basic)
- ✅ Join community forums

**Limitations**:
- ❌ No CV builder access
- ❌ No priority support
- ❌ No advanced analytics
- ❌ No document scanning
- ❌ No consultation bookings
- ❌ No referral rewards
- ❌ Profile visibility limited
- ❌ Ads displayed
- ❌ Watermarked certificates

**Upgrade Path**: Prompted after 1 application or 30 days

---

#### 3.2 PREMIUM TIER (৳499/month or ৳4,999/year)
**Target**: Active job seekers, serious applicants

**All FREE features PLUS**:
- ✅ Complete profile (100% completion)
- ✅ Submit 10 applications per month
- ✅ Upload unlimited documents (100 MB total)
- ✅ CV builder with 5 templates
- ✅ Document scanner (OCR)
- ✅ Advanced profile assessment with AI insights
- ✅ Priority agency matching
- ✅ Email + SMS notifications
- ✅ Priority support (24-hour response)
- ✅ Download certificates (no watermark)
- ✅ Enhanced public profile
- ✅ Profile QR code
- ✅ Referral program (earn ৳100 per referral)
- ✅ Ad-free experience
- ✅ 1 free consultation per month (30 min)
- ✅ Application status tracking (detailed)
- ✅ Interview preparation guides
- ✅ Salary insights and statistics
- ✅ Job alerts (email/SMS)

**Discount**: Save 17% on annual plan (৳4,999 vs ৳5,988)

**Cancellation**: Cancel anytime, access until period ends

---

#### 3.3 ENTERPRISE TIER (৳1,999/month or ৳19,999/year)
**Target**: High-frequency users, families, corporate relocations

**All PREMIUM features PLUS**:
- ✅ Unlimited applications
- ✅ Upload unlimited documents (1 GB total)
- ✅ All CV templates + custom branding
- ✅ Advanced document scanner with verification
- ✅ AI-powered application assistant
- ✅ Dedicated account manager
- ✅ Priority support (4-hour response)
- ✅ Phone support access
- ✅ 4 free consultations per month (1 hour each)
- ✅ Family member profiles (up to 5)
- ✅ White-glove application service
- ✅ Expedited processing
- ✅ Direct agency communication
- ✅ Advanced analytics dashboard
- ✅ Multi-country application support
- ✅ Document translation credits (৳5,000/mo)
- ✅ Airport lounge access partner discounts
- ✅ Travel insurance discounts (20%)
- ✅ Concierge service
- ✅ Custom reporting
- ✅ API access (for corporate users)
- ✅ Bulk operations

**Discount**: Save 17% on annual plan (৳19,999 vs ৳23,988)

**Contract**: Monthly or annual, cancel anytime

---

### 4. SERVICE PRICING MODEL

#### 4.1 Core Services (Available to All Tiers with Pricing)

**A. Visa Application Services**

| Service | Free User | Premium User | Enterprise User | Agency Fee |
|---------|-----------|--------------|-----------------|------------|
| Tourist Visa | ৳2,500 | ৳2,000 (20% off) | ৳1,500 (40% off) | 15% platform fee |
| Student Visa | ৳5,000 | ৳4,500 | ৳4,000 | 15% platform fee |
| Work Visa | ৳8,000 | ৳7,500 | ৳7,000 | 15% platform fee |
| Family Visa | ৳6,000 | ৳5,500 | ৳5,000 | 15% platform fee |
| Business Visa | ৳4,000 | ৳3,500 | ৳3,000 | 15% platform fee |

**B. Professional Services**

| Service | Free User | Premium User | Enterprise User |
|---------|-----------|--------------|-----------------|
| CV Builder | ❌ | Included | Included |
| Document Translation (per page) | ৳150 | ৳120 | ৳100 (+ ৳5,000 credit) |
| Document Attestation | ৳1,500 | ৳1,200 | ৳1,000 |
| Police Clearance | ৳2,000 | ৳1,800 | ৳1,500 |
| Medical Certificate | ৳3,000 | ৳2,700 | ৳2,400 |
| Consultation (30 min) | ৳800 | 1 free/mo, then ৳600 | 4 free/mo |
| Profile Assessment | Basic (free) | Advanced (included) | AI-powered (included) |

**C. Education Services**

| Service | Free User | Premium User | Enterprise User |
|---------|-----------|--------------|-----------------|
| University Search | Free | Free | Free |
| Course Recommendation | ❌ | Included | Included |
| Application Assistance | ৳5,000 | ৳4,500 | ৳4,000 |
| Scholarship Search | Free | Free (priority) | Free (priority + alerts) |
| SOP Writing | ৳3,000 | ৳2,500 | ৳2,000 |
| LOR Assistance | ৳2,500 | ৳2,000 | ৳1,500 |

**D. Employment Services**

| Service | Free User | Premium User | Enterprise User |
|---------|-----------|--------------|-----------------|
| Job Search | Free | Free | Free |
| Job Application | ৳500/application | ৳300/application | ৳200/application |
| Interview Preparation | ❌ | ৳1,500 | Included |
| Skill Assessment | ৳1,000 | ৳800 | ৳600 |
| Salary Negotiation | ❌ | ৳2,000 | ৳1,500 |

**E. Travel Services**

| Service | Free User | Premium User | Enterprise User |
|---------|-----------|--------------|-----------------|
| Flight Booking | 3% commission | 2% commission | 1.5% commission |
| Hotel Booking | 5% commission | 3% commission | 2% commission |
| Travel Insurance | ৳1,500 | ৳1,200 | ৳900 (20% discount) |
| Airport Transfer | Standard rates | 10% discount | 20% discount |
| Sim Card | Standard | 15% discount | 25% discount |

---

### 5. PLATFORM ADVANCE FEATURES (Revenue Drivers)

#### 5.1 Premium Platform Features (Add-ons)

**A. AI-Powered Tools** (Subscription or Pay-per-Use)

| Feature | Free | Premium | Enterprise | Pay-per-Use |
|---------|------|---------|------------|-------------|
| AI Application Assistant | ❌ | Limited (5/mo) | Unlimited | ৳200/use |
| Smart Document Scanner | ❌ | 20 scans/mo | Unlimited | ৳50/scan |
| AI Profile Optimizer | ❌ | 3 reviews/mo | Unlimited | ৳300/review |
| Visa Success Predictor | ❌ | Included | Included | ৳500/analysis |
| Interview Simulation (AI) | ❌ | 2/mo | Unlimited | ৳800/session |

**B. Priority Services** (Premium/Enterprise Only)

- 🚀 **Express Processing**: ৳3,000 (48-hour turnaround instead of 7 days)
- 🎯 **Dedicated Account Manager**: Included in Enterprise, ৳5,000/mo for Premium
- 📞 **Phone Support**: Included in Enterprise, ৳1,000/mo for Premium
- 🏆 **Priority Agency Matching**: Included in Premium+
- 📨 **SMS Notifications**: Included in Premium+

**C. Concierge Services** (Enterprise Only)

- ✈️ Airport lounge access coordination
- 🏨 Accommodation booking assistance
- 🚗 Transportation arrangement
- 📱 Local SIM card delivery
- 🏥 Medical appointment booking
- 🎓 University campus tours
- 💼 Networking event invitations

**D. Corporate/Bulk Services** (Custom Pricing)

- 👥 **Corporate Relocations**: Custom packages for companies
- 🏢 **University Partnerships**: Bulk student processing
- 🤝 **Recruitment Agency Partnerships**: White-label solutions
- 📊 **Custom Reporting**: Enterprise analytics and dashboards

---

### 6. MONETIZATION BREAKDOWN

#### 6.1 Revenue Streams

```
1. User Subscriptions (40% of revenue)
   - Premium: ৳499/mo × 10,000 users = ৳4,990,000/mo
   - Enterprise: ৳1,999/mo × 1,000 users = ৳1,999,000/mo
   - Total: ৳6,989,000/mo

2. Service Commissions (35% of revenue)
   - Visa applications: 15% of ৳50,000,000/mo = ৳7,500,000/mo
   - Document services: 20% of ৳10,000,000/mo = ৳2,000,000/mo
   - Travel bookings: 2-5% of ৳30,000,000/mo = ৳900,000/mo
   - Total: ৳10,400,000/mo

3. Agency/Consultant Subscriptions (15% of revenue)
   - Agency: ৳12,000/mo × 200 agencies = ৳2,400,000/mo
   - Consultant: ৳5,000/mo × 100 consultants = ৳500,000/mo
   - Total: ৳2,900,000/mo

4. Premium Features & Add-ons (10% of revenue)
   - AI tools, express processing, etc.: ৳1,500,000/mo

Total Projected Monthly Revenue: ৳21,789,000/mo (~ ৳261 million/year)
```

---

## ⚙️ COMPREHENSIVE SETTINGS ARCHITECTURE

### 7. USER SETTINGS (ALL ROLES)

#### 7.1 ACCOUNT SETTINGS (Universal)

```javascript
AccountSettings = {
  // Basic Information
  profile: {
    name: string,
    email: string (verified),
    phone: string (verified),
    date_of_birth: date,
    gender: enum ['Male', 'Female', 'Other', 'Prefer not to say'],
    profile_picture: file,
    cover_photo: file (Premium+),
    bio: text (500 chars),
    languages: array,
    timezone: string (default: 'Asia/Dhaka'),
    currency: string (default: 'BDT'),
  },

  // Address Information
  address: {
    current: {
      division: enum,
      district: string,
      upazila: string,
      post_office: string,
      village_or_house: string,
      postal_code: string,
    },
    permanent: {
      same_as_current: boolean,
      // Same fields as current if false
    },
  },

  // Identity Documents
  documents: {
    nid_number: string (10 or 17 digits),
    passport_number: string,
    birth_certificate: string,
    tin_number: string (optional),
  },

  // Account Security
  security: {
    password: string (encrypted),
    two_factor_enabled: boolean,
    two_factor_method: enum ['SMS', 'Email', 'Authenticator'],
    security_questions: array (3 questions),
    login_alerts: boolean,
    device_management: array (active sessions),
    last_password_change: datetime,
  },

  // Login & Access
  login: {
    email_login: boolean,
    phone_login: boolean,
    google_oauth: boolean,
    facebook_oauth: boolean,
    remember_me_default: boolean,
    session_timeout: integer (minutes),
  },
}
```

---

#### 7.2 PRIVACY SETTINGS

```javascript
PrivacySettings = {
  // Profile Visibility
  visibility: {
    profile_visibility: enum ['Public', 'Registered Users', 'Agencies Only', 'Private'],
    show_in_directory: boolean,
    show_in_search_results: boolean,
    allow_profile_indexing: boolean (SEO),
    profile_url: string (custom: /p/username),
  },

  // Contact Preferences
  contact: {
    allow_agencies_to_contact: boolean,
    allow_consultants_to_contact: boolean,
    show_email_publicly: boolean,
    show_phone_publicly: boolean,
    show_location: enum ['Exact', 'City Only', 'Division Only', 'Hidden'],
  },

  // Data Sharing
  data_sharing: {
    share_with_partner_agencies: boolean,
    share_with_recruiters: boolean,
    share_for_analytics: boolean (anonymized),
    allow_third_party_cookies: boolean,
  },

  // Activity Privacy
  activity: {
    show_last_active: boolean,
    show_application_count: boolean,
    show_success_rate: boolean,
    hide_specific_applications: array (IDs to hide),
  },

  // Download & Export
  data_portability: {
    download_my_data: button (generates ZIP),
    export_format: enum ['JSON', 'CSV', 'PDF'],
    request_deletion: button (30-day grace period),
  },
}
```

---

#### 7.3 NOTIFICATION SETTINGS

```javascript
NotificationSettings = {
  // Channel Preferences
  channels: {
    email_notifications: boolean,
    sms_notifications: boolean (Premium+),
    push_notifications: boolean,
    in_app_notifications: boolean,
  },

  // Application Updates
  applications: {
    status_changes: boolean,
    new_quotes_received: boolean,
    document_requests: boolean,
    appointment_reminders: boolean,
    deadline_alerts: boolean,
  },

  // Account Activity
  account: {
    login_alerts: boolean,
    password_changes: boolean,
    profile_views: boolean (Premium+),
    referral_signups: boolean,
    wallet_transactions: boolean,
  },

  // Marketing & Promotions
  marketing: {
    newsletters: boolean,
    promotional_offers: boolean,
    new_service_announcements: boolean,
    partner_offers: boolean,
    weekly_digest: boolean,
  },

  // Service-Specific
  services: {
    job_alerts: boolean,
    visa_updates: boolean,
    scholarship_alerts: boolean,
    webinar_invitations: boolean,
  },

  // Frequency Control
  frequency: {
    real_time: boolean,
    daily_digest: boolean,
    weekly_digest: boolean,
    custom_schedule: {
      days: array ['Mon', 'Tue', ...],
      time: time,
    },
  },

  // Quiet Hours
  quiet_hours: {
    enabled: boolean,
    start_time: time ('22:00'),
    end_time: time ('08:00'),
    timezone: string,
  },
}
```

---

#### 7.4 PAYMENT & BILLING SETTINGS

```javascript
PaymentSettings = {
  // Subscription Management
  subscription: {
    current_plan: enum ['Free', 'Premium', 'Enterprise'],
    billing_cycle: enum ['Monthly', 'Yearly'],
    next_billing_date: date,
    auto_renew: boolean,
    payment_method: string,
  },

  // Payment Methods
  payment_methods: [
    {
      id: uuid,
      type: enum ['Credit Card', 'Debit Card', 'bKash', 'Nagad', 'Bank Transfer'],
      is_default: boolean,
      details: object (masked),
      expiry: date (for cards),
    }
  ],

  // Wallet
  wallet: {
    balance: decimal,
    currency: string ('BDT'),
    auto_reload: boolean,
    reload_threshold: decimal,
    reload_amount: decimal,
  },

  // Billing Information
  billing: {
    name: string,
    email: string,
    phone: string,
    address: object,
    tax_id: string (optional),
    company_name: string (optional),
  },

  // Transaction History
  transactions: {
    view_history: link,
    download_invoices: boolean,
    tax_receipts: boolean,
  },

  // Referral Earnings
  referrals: {
    total_earned: decimal,
    pending_amount: decimal,
    withdraw_to_wallet: button,
    withdraw_to_bank: button,
    minimum_withdrawal: decimal (৳500),
  },
}
```

---

#### 7.5 PREFERENCE SETTINGS

```javascript
PreferenceSettings = {
  // Language & Localization
  localization: {
    interface_language: enum ['English', 'Bengali'],
    date_format: enum ['DD/MM/YYYY', 'MM/DD/YYYY'],
    time_format: enum ['12-hour', '24-hour'],
    number_format: enum ['1,234.56', '1.234,56'],
    currency_display: enum ['Symbol (৳)', 'Code (BDT)', 'Both'],
  },

  // Display Preferences
  display: {
    theme: enum ['Light', 'Dark', 'Auto'],
    color_scheme: enum ['Default', 'Blue', 'Green', 'Purple'],
    compact_mode: boolean,
    animations_enabled: boolean,
    font_size: enum ['Small', 'Medium', 'Large'],
  },

  // Dashboard Layout
  dashboard: {
    widgets: array (draggable widget IDs),
    default_view: enum ['Grid', 'List', 'Cards'],
    items_per_page: integer (10, 20, 50, 100),
    sort_order: enum ['Newest First', 'Oldest First', 'A-Z'],
  },

  // Accessibility
  accessibility: {
    screen_reader_mode: boolean,
    high_contrast: boolean,
    keyboard_navigation: boolean,
    reduce_motion: boolean,
    text_to_speech: boolean (Premium+),
  },

  // Communication
  communication: {
    preferred_contact_method: enum ['Email', 'SMS', 'Phone', 'In-App'],
    preferred_contact_time: time_range,
    language_for_support: enum ['English', 'Bengali'],
  },
}
```

---

#### 7.6 APPLICATION PREFERENCES

```javascript
ApplicationPreferences = {
  // Target Countries
  target_countries: {
    primary: array (top 3),
    secondary: array,
    blocked: array (countries to hide),
  },

  // Job Preferences
  job: {
    desired_roles: array,
    industries: array,
    experience_level: enum,
    employment_type: array ['Full-time', 'Part-time', 'Contract', 'Remote'],
    salary_expectation: {
      min: decimal,
      max: decimal,
      currency: string,
      negotiable: boolean,
    },
    willing_to_relocate: boolean,
    notice_period: integer (days),
  },

  // Education Preferences
  education: {
    degree_level: enum,
    fields_of_interest: array,
    intake_preference: enum ['Fall', 'Spring', 'Summer', 'Any'],
    scholarship_required: boolean,
    max_tuition_budget: decimal,
    accommodation_needed: boolean,
  },

  // Visa Preferences
  visa: {
    visa_types: array,
    urgency: enum ['Standard', 'Urgent', 'Emergency'],
    assistance_level: enum ['Self Service', 'Guided', 'Full Service'],
    budget_range: {
      min: decimal,
      max: decimal,
    },
  },

  // Travel Preferences
  travel: {
    cabin_class: enum ['Economy', 'Business', 'First'],
    meal_preference: enum ['Vegetarian', 'Non-Veg', 'Vegan', 'Halal'],
    seat_preference: enum ['Window', 'Aisle', 'Any'],
    accommodation_type: enum ['Hotel', 'Hostel', 'Apartment', 'Any'],
  },
}
```

---

### 8. AGENCY-SPECIFIC SETTINGS

```javascript
AgencySettings = {
  // Agency Profile
  profile: {
    legal_name: string,
    trading_name: string,
    logo: file,
    banner: file,
    registration_number: string,
    license_number: string,
    established_year: integer,
    description: text,
    website: url,
    office_address: object,
    contact_numbers: array,
    business_hours: object,
  },

  // Team Management
  team: {
    owner: user_id,
    admins: array (user_ids),
    staff: array (user_ids),
    consultants: array (user_ids),
    role_permissions: object,
  },

  // Service Configuration
  services: {
    offered_services: array (service_module_ids),
    specializations: array,
    countries_covered: array,
    languages_offered: array,
    processing_time_estimates: object,
    success_rates: object (auto-calculated),
  },

  // Pricing & Commission
  pricing: {
    default_markup: decimal (percentage),
    service_specific_pricing: object,
    discount_rules: array,
    bulk_pricing: boolean,
    commission_tier: enum ['Standard', 'Premium', 'Elite'],
    platform_commission: decimal (15%, 12%, 10%),
  },

  // Availability
  availability: {
    accepting_clients: boolean,
    max_concurrent_applications: integer,
    current_workload: integer (auto-calculated),
    vacation_mode: boolean,
    vacation_dates: date_range,
    auto_accept_applications: boolean,
  },

  // Payment Settings
  payment: {
    bank_name: string,
    account_name: string,
    account_number: string,
    routing_number: string,
    swift_code: string,
    bkash_number: string,
    nagad_number: string,
    preferred_payment_method: enum,
    payout_schedule: enum ['Weekly', 'Bi-weekly', 'Monthly'],
    minimum_payout: decimal,
  },

  // Verification Documents
  verification: {
    trade_license: file,
    registration_certificate: file,
    tax_documents: file,
    owner_nid: file,
    office_photos: array,
    verification_status: enum ['Pending', 'Verified', 'Rejected'],
    verified_at: datetime,
  },

  // Resources & Templates
  resources: {
    document_templates: array,
    email_templates: array,
    checklist_templates: array,
    contract_templates: array,
  },

  // Analytics Preferences
  analytics: {
    track_conversion_rate: boolean,
    track_client_sources: boolean,
    export_frequency: enum,
    email_reports: boolean,
  },
}
```

---

### 9. CONSULTANT-SPECIFIC SETTINGS

```javascript
ConsultantSettings = {
  // Professional Profile
  profile: {
    full_name: string,
    professional_title: string,
    photo: file,
    bio: text (1000 chars),
    years_of_experience: integer,
    specializations: array,
    certifications: array,
    education: array,
    languages: array,
  },

  // Credentials
  credentials: {
    license_numbers: array,
    certification_documents: array,
    verification_status: enum ['Pending', 'Verified'],
    expiry_dates: object,
  },

  // Services Offered
  services: {
    consultation_types: array ['Profile Review', 'Document Review', 'Interview Prep', 'General Advice'],
    countries_expertise: array,
    visa_types_expertise: array,
    session_durations: array [30, 60, 90], // minutes
  },

  // Pricing
  pricing: {
    hourly_rate: decimal,
    session_rates: object {
      '30min': decimal,
      '60min': decimal,
      '90min': decimal,
    },
    package_deals: array,
    discount_for_students: decimal (percentage),
  },

  // Availability
  availability: {
    calendar_integration: boolean,
    available_days: array ['Mon', 'Tue', ...],
    time_slots: object,
    buffer_time: integer (minutes between sessions),
    advance_booking: integer (days),
    max_daily_sessions: integer,
    timezone: string,
  },

  // Communication
  communication: {
    video_call: boolean (Zoom, Google Meet),
    phone_call: boolean,
    in_person: boolean,
    chat_support: boolean,
    preferred_platform: enum,
    response_time_guarantee: integer (hours),
  },

  // Payment
  payment: {
    bank_details: object,
    mobile_wallet: object,
    payout_schedule: enum ['Per Session', 'Weekly', 'Monthly'],
    minimum_payout: decimal,
  },
}
```

---

### 10. ADMIN-SPECIFIC SETTINGS

```javascript
AdminSettings = {
  // Platform Configuration
  platform: {
    site_name: string,
    tagline: string,
    logo: file,
    favicon: file,
    primary_color: color,
    secondary_color: color,
    maintenance_mode: boolean,
    registration_enabled: boolean,
    default_language: enum,
  },

  // Payment Gateway Configuration
  payment_gateways: {
    sslcommerz: {
      enabled: boolean,
      store_id: string,
      store_password: string (encrypted),
      sandbox_mode: boolean,
    },
    bkash: {
      enabled: boolean,
      app_key: string (encrypted),
      app_secret: string (encrypted),
      username: string,
      password: string (encrypted),
      sandbox_mode: boolean,
    },
    nagad: {
      enabled: boolean,
      merchant_id: string,
      merchant_key: string (encrypted),
      sandbox_mode: boolean,
    },
    stripe: {
      enabled: boolean,
      publishable_key: string,
      secret_key: string (encrypted),
      webhook_secret: string (encrypted),
    },
  },

  // Email Configuration
  email: {
    provider: enum ['SMTP', 'Mailgun', 'SendGrid', 'AWS SES', 'Resend'],
    from_name: string,
    from_email: string,
    reply_to: string,
    smtp_host: string,
    smtp_port: integer,
    smtp_username: string,
    smtp_password: string (encrypted),
    encryption: enum ['TLS', 'SSL'],
  },

  // SMS Configuration
  sms: {
    provider: enum ['Twilio', 'Nexmo', 'Local Provider'],
    api_key: string (encrypted),
    sender_id: string,
    test_mode: boolean,
  },

  // Service Management
  services: {
    enable_service_categories: array,
    featured_services: array,
    default_commission_rate: decimal,
    auto_assign_agencies: boolean,
    require_admin_approval: array (service IDs),
  },

  // User Management
  users: {
    require_email_verification: boolean,
    require_phone_verification: boolean,
    allow_social_login: boolean,
    default_role: string,
    auto_suspend_inactive: integer (days),
    password_expiry: integer (days),
  },

  // Moderation
  moderation: {
    auto_approve_agencies: boolean,
    auto_approve_consultants: boolean,
    review_flagged_content: boolean,
    spam_filter_enabled: boolean,
    profanity_filter_enabled: boolean,
  },

  // Analytics
  analytics: {
    google_analytics_id: string,
    facebook_pixel_id: string,
    hotjar_id: string,
    track_user_behavior: boolean,
  },

  // Backup & Maintenance
  backup: {
    auto_backup_enabled: boolean,
    backup_frequency: enum ['Daily', 'Weekly'],
    backup_retention: integer (days),
    backup_location: string,
  },

  // SEO
  seo: {
    meta_title_template: string,
    meta_description_template: string,
    default_og_image: file,
    sitemap_enabled: boolean,
    robots_txt_enabled: boolean,
  },
}
```

---

## 🔐 ACCESS CONTROL MATRIX

### 11. FEATURE ACCESS BY ROLE

| Feature | Free User | Premium User | Enterprise User | Consultant | Agency | Admin | Super Admin |
|---------|-----------|--------------|-----------------|------------|--------|-------|-------------|
| **Profile Management** |
| Basic Profile | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Complete Profile | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Public Profile | Basic | Enhanced | Premium | Professional | Professional | ✅ | ✅ |
| Profile QR Code | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Applications** |
| Submit Applications | 1/mo | 10/mo | Unlimited | N/A | N/A | View All | View All |
| Track Status | ✅ | ✅ | ✅ | N/A | View Assigned | View All | View All |
| Document Upload | 5 (10MB) | Unlimited (100MB) | Unlimited (1GB) | N/A | Unlimited | Unlimited | Unlimited |
| **Services** |
| Browse Services | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Service Discounts | 0% | 20% | 40% | N/A | Set Own | N/A | N/A |
| Priority Processing | ❌ | ❌ | ✅ | N/A | ✅ | ✅ | ✅ |
| **Communication** |
| Email Support | ✅ (48hr) | ✅ (24hr) | ✅ (4hr) | ✅ (24hr) | ✅ (24hr) | ✅ (1hr) | ✅ (1hr) |
| Phone Support | ❌ | Add-on | ✅ | ✅ | ✅ | ✅ | ✅ |
| Live Chat | ❌ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Advanced Features** |
| CV Builder | ❌ | ✅ | ✅ | N/A | N/A | ✅ | ✅ |
| Document Scanner | ❌ | 20/mo | Unlimited | Unlimited | Unlimited | Unlimited | Unlimited |
| AI Assistant | ❌ | 5/mo | Unlimited | 10/mo | Unlimited | Unlimited | Unlimited |
| Consultation Booking | ❌ | ✅ | ✅ | Provide | N/A | View All | View All |
| Referral Program | ❌ | ✅ | ✅ | ✅ | ✅ | N/A | N/A |
| **Agency Features** |
| Create Agency Profile | N/A | N/A | N/A | N/A | ✅ | Approve | ✅ |
| Team Management | N/A | N/A | N/A | N/A | ✅ | View | ✅ |
| Quote Submission | N/A | N/A | N/A | N/A | ✅ | View | ✅ |
| Client Management | N/A | N/A | N/A | Limited | ✅ | View All | ✅ |
| **Admin Functions** |
| User Management | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Content Moderation | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| Service Management | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ✅ |
| System Settings | ❌ | ❌ | ❌ | ❌ | ❌ | Limited | ✅ |
| Analytics | Basic | Advanced | Enterprise | Dashboard | Dashboard | Full | Full |

---

## 🎯 IMPLEMENTATION ROADMAP

### 12. PHASED ROLLOUT STRATEGY

#### Phase 1: Foundation (Weeks 1-2)
- ✅ Implement role-based middleware
- ✅ Create settings database schema
- ✅ Build base settings UI components
- ✅ Implement account settings
- ✅ Implement privacy settings
- ✅ Set up payment gateway integrations

#### Phase 2: Tier System (Weeks 3-4)
- ✅ Create subscription plans in database
- ✅ Implement subscription management
- ✅ Build pricing calculator
- ✅ Create upgrade/downgrade flows
- ✅ Implement feature gating
- ✅ Add usage tracking

#### Phase 3: Advanced Features (Weeks 5-6)
- ✅ Implement notification system
- ✅ Build preference management
- ✅ Create dashboard customization
- ✅ Add AI feature integrations
- ✅ Implement analytics tracking

#### Phase 4: Agency & Consultant (Weeks 7-8)
- ✅ Build agency registration flow
- ✅ Implement consultant onboarding
- ✅ Create team management tools
- ✅ Build quote submission system
- ✅ Implement commission tracking

#### Phase 5: Testing & Refinement (Weeks 9-10)
- ✅ Comprehensive testing
- ✅ Performance optimization
- ✅ Security audit
- ✅ User acceptance testing
- ✅ Documentation completion

---

## 📊 SUCCESS METRICS

### 13. KPIs to Track

#### User Acquisition
- Free signups per month
- Premium conversion rate (target: 10%)
- Enterprise conversion rate (target: 2%)
- Churn rate (target: < 5% monthly)

#### Revenue
- MRR (Monthly Recurring Revenue)
- ARR (Annual Recurring Revenue)
- ARPU (Average Revenue Per User)
- LTV (Customer Lifetime Value)

#### Engagement
- Daily Active Users (DAU)
- Monthly Active Users (MAU)
- Session duration
- Feature adoption rate

#### Service Quality
- Application completion rate
- User satisfaction score (NPS)
- Support response time
- Agency/consultant rating

---

## 🎓 USER EDUCATION STRATEGY

### 14. Onboarding & Training

#### Free Users
- Welcome email series (5 emails)
- Interactive platform tour
- Video tutorials (basic features)
- FAQ access

#### Premium Users
- Personalized onboarding call (optional)
- Advanced feature webinars
- Premium support documentation
- Monthly tips newsletter

#### Enterprise Users
- Dedicated onboarding specialist
- Custom training sessions
- Quarterly business reviews
- Priority documentation access

#### Agencies/Consultants
- Platform training webinars
- Best practices guides
- Success case studies
- Monthly partner meetings

---

## 🔒 COMPLIANCE & SECURITY

### 15. Data Protection & Legal

- ✅ GDPR-compliant data handling
- ✅ Bangladesh Data Protection Act compliance
- ✅ PCI-DSS for payment processing
- ✅ SSL/TLS encryption
- ✅ Regular security audits
- ✅ Data breach notification protocol
- ✅ Terms of Service per role
- ✅ Privacy Policy transparency
- ✅ Cookie consent management
- ✅ Right to be forgotten implementation

---

## 📝 CONCLUSION

This strategic framework provides a comprehensive blueprint for BideshGomon's role-based access control, tiered service offerings, and granular settings management. The system is designed to:

1. **Scale Gracefully**: From free users to enterprise clients
2. **Monetize Effectively**: Multiple revenue streams
3. **Maintain Quality**: Premium features drive upgrades
4. **Empower Users**: Granular control over all settings
5. **Support Partners**: Agency and consultant success
6. **Ensure Compliance**: Legal and security standards met

**Next Steps**:
1. Review and approve this strategy
2. Prioritize implementation phases
3. Allocate development resources
4. Begin Phase 1 development
5. Set up tracking and analytics
6. Launch beta program

---

**Document Owner**: Technical Leadership  
**Approvers**: CEO, CTO, Product Manager  
**Review Cycle**: Quarterly  
**Last Updated**: December 2, 2025  
**Status**: ✅ Ready for Implementation
