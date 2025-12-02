# BideshGomon Platform - Comprehensive Project Summary

**Generated**: December 2, 2025  
**Version**: 1.0.0  
**Status**: Production-Ready  

---

## 📋 Executive Overview

**Bidesh Gomon** is a comprehensive, Bangladesh-focused visa application and migration platform built with modern web technologies. The platform manages end-to-end migration services including visa applications, profile management, digital wallet, referral rewards, and multi-role user systems—all optimized for Bangladeshi users with complete localization.

### Quick Stats
- **Total Files**: 2,154 production files
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue 3 + Inertia.js 2.0
- **Database**: 200+ migrations (MySQL/SQLite)
- **Models**: 80+ Eloquent models
- **Controllers**: 50+ controllers
- **Vue Pages**: 314+ pages/components
- **Services**: 39 plugin services across 6 categories
- **Localization**: Complete Bangladesh formatting (BDT, DD/MM/YYYY, +880)

---

## 🎯 Core Features

### 1. Multi-Role System
- **Roles**: Admin, User, Agency, Consultant
- **Access Control**: Role-based middleware (`role:admin`, `role:agency`)
- **Impersonation**: Admin can impersonate users with full audit trail
- **Activity Logging**: Comprehensive system using `spatie/laravel-activitylog`

### 2. User Profile System (9 Specialized Sections)
Comprehensive profile data split across specialized tables:

| Section | Table | Features |
|---------|-------|----------|
| **Basic Profile** | `user_profiles` | Name, DOB, NID, address, contact |
| **Passports** | `user_passports` | Multiple passports, primary flag, scans |
| **Visa History** | `user_visa_history` | Previous visas, rejections tracking |
| **Travel History** | `user_travel_history` | Past trips (required by USA/UK/AU) |
| **Family Members** | `user_family_members` | Spouse, children for family visas |
| **Financial Info** | `user_financial_information` | Bank statements, income proof |
| **Security Info** | `user_security_information` | Criminal records, medical history |
| **Education** | `user_educations` | Degrees, certificates, institutions |
| **Work Experience** | `user_work_experiences` | Employment history, job references |
| **Languages** | `user_languages` | IELTS, TOEFL, language proficiency |

**Profile Patterns**:
- Primary flags for main records (`is_primary` on passports, addresses)
- File uploads with automatic cleanup on update
- Relationship integrity checks before delete
- Bangladesh-specific validation (NID, phone, divisions)

### 3. Digital Wallet System
Complete financial management with audit trail:

**Features**:
- Credit/Debit operations with transaction history
- Balance snapshots (`balance_before`, `balance_after`)
- Reference tracking (`reference_type`, `reference_id`)
- Transaction metadata storage
- Admin approval workflows
- Auto-created on user registration

**Service**: `WalletService`
- `createWallet(User)`: Auto-initialization
- `creditWallet()`: Add funds with DB transaction
- `debitWallet()`: Withdraw funds with balance check
- `getTransactionHistory()`: Paginated history

**Currency**: BDT (৳) with Bangladesh formatting

### 4. Referral & Rewards System
Multi-tier reward system with admin approval:

**Components**:
- `referrals`: Tracks referrer → referred relationships
- `rewards`: Pending/approved rewards with amounts
- `users.referral_code`: 8-character unique codes
- `users.referred_by`: Foreign key to referrer

**Flow**:
1. User registers with `?ref=CODE` parameter
2. System creates `Referral` record (status: pending)
3. Creates `Reward` record (৳100 signup bonus)
4. Admin approves reward
5. `ReferralService` calls `WalletService` to credit wallet
6. Transaction recorded with audit trail

**Service**: `ReferralService`
- `generateReferralCode()`: Auto-generated on user creation
- `trackReferral()`: Register new referral
- `approveReward()`: Admin approval + wallet credit

### 5. Plugin Service Architecture (39 Services)
Modular service system with dynamic forms:

**Categories** (6):
- Jobs & Employment
- Visa Services
- Education Services
- Travel & Tourism
- Hajj & Umrah
- Professional Services

**Service Modules**: 39 pre-configured services
- Dynamic form fields (JSON schema)
- Document requirements per service
- Agency assignments per country/service
- Application workflow tracking
- Status history logging

**Key Tables**:
- `service_modules`: Service definitions
- `service_categories`: Service grouping
- `service_applications`: User applications
- `service_quotes`: Pricing requests
- `agency_country_assignments`: Agency capabilities

### 6. Agency Management System
Complete agency verification and management:

**Features**:
- Agency profiles with verification status
- Team member management (roles: admin, consultant, staff)
- Country/service assignments
- Resource management (documents, templates)
- Review system with ratings
- Verification workflow with admin approval

**Agency Types**: Travel agencies, Educational consultancies, Recruitment firms, Immigration consultants

### 7. Bangladesh Localization
Everything formatted for Bangladesh market:

**Helper Functions** (`app/Helpers/bangladesh_helpers.php`):
```php
format_bd_currency($amount)     // ৳5,000.00
format_bd_date($date)           // 18/11/2025 (DD/MM/YYYY)
format_bd_phone($phone)         // 01712-345678
validate_bd_nid($nid)           // 10 or 17 digits
detect_bd_operator($phone)      // 'Grameenphone', 'Robi'
get_bd_divisions()              // 8 divisions array
get_popular_destinations_bd()   // By purpose
```

**Vue Composable** (`resources/js/Composables/useBangladeshFormat.js`):
```javascript
const { formatCurrency, formatDate, formatTime } = useBangladeshFormat()
formatCurrency(1000)  // "৳1,000.00"
formatDate(date)      // "18/11/2025"
formatTime(date)      // "9:30 AM"
```

**Configuration** (`config/bangladesh.php`):
- Currency: BDT (৳)
- Date format: DD/MM/YYYY
- Phone: +880 format with operator detection
- Divisions: 8 administrative divisions
- Common destinations by purpose
- Document formats (NID, passport, TIN)
- Financial limits (min/max transactions)

### 8. Service-Specific Modules

#### Visa Services
- **Tourist Visa**: Country-specific applications with travel plans
- **Student Visa**: University applications, acceptance letters
- **Work Visa**: Job offer letters, profession-based requirements
- **Visa Requirements**: Country + visa type + profession matrix
- **Document Requirements**: Dynamic per destination

#### Jobs & Employment
- **Job Postings**: International job listings with categories
- **Job Applications**: Application tracking with status workflow
- **CV Builder**: Multiple templates, download as PDF
- **Skill Verification**: Skill assessment and certification

#### Education Services
- **Universities**: 100+ international universities
- **Courses**: Course catalogs with prerequisites
- **Applications**: University application management
- **Document Attestation**: Educational document verification

#### Travel & Tourism
- **Flight Bookings**: Multi-city flight search and booking
- **Hotel Bookings**: Hotel reservations with room selection
- **Travel Insurance**: Insurance packages by destination
- **Tour Packages**: Pre-designed tour packages

#### Hajj & Umrah
- **Packages**: Hajj/Umrah package management
- **Group Bookings**: Group registration system
- **Visa Processing**: Saudi Arabia visa coordination

### 9. Advanced Features

#### Payment Gateway Integration
- **SSLCommerz**: Bangladesh's leading payment gateway
- **bKash**: Mobile financial service (planned)
- **Nagad**: Digital payment service (planned)
- **Wallet Payment**: Internal wallet balance usage

**Dependencies**:
```json
"resend/resend-php": "^1.0"  // Email notifications
"twilio/sdk": "^8.8"         // SMS notifications
```

#### Document Management
- **Document Scanner**: AI-powered document scanning
- **Document Verification**: Admin approval workflow
- **File Storage**: Organized by category (passports, visas, certificates)
- **Storage Link**: `php artisan storage:link` for public access

#### Progressive Web App (PWA)
- **Manifest**: `/manifest.json` for installability
- **Service Worker**: `/sw.js` for offline support
- **Offline Page**: Fallback when disconnected
- **Push Notifications**: Real-time updates (Laravel Reverb)

#### SEO & Marketing
- **Sitemap**: Auto-generated XML sitemap (`spatie/laravel-sitemap`)
- **JSON-LD Schema**: Structured data for Google
- **SEOHead Component**: Dynamic meta tags per page
- **Breadcrumbs**: Schema.org markup
- **Canonical Tags**: Duplicate content prevention
- **Blog System**: SEO-optimized blog with categories/tags

#### Communication Systems
- **Support Tickets**: User support with categories, priorities
- **Appointments**: Booking system with availability management
- **Notifications**: In-app + email + SMS notifications
- **Email Templates**: Dynamic email template system
- **Real-time Updates**: Laravel Reverb WebSocket integration

---

## 🏗️ Technical Architecture

### Backend Structure

#### Service Layer Pattern
Controllers are thin—business logic lives in services:

```
Controller (validation only)
    ↓
Service (business logic)
    ↓
Model (data operations)
    ↓
Database
```

**Key Services**:
- `WalletService`: All wallet operations
- `ReferralService`: Referral tracking and rewards
- `ProfileAssessmentService`: AI-powered profile scoring
- `NotificationService`: Multi-channel notifications

#### Observer-Driven Automation
**UserObserver** auto-initializes on user creation:
```php
User::created → auto-create wallet (৳0.00)
             → auto-generate referral code (8-char)
```

Registered in `AppServiceProvider::boot()`:
```php
User::observe(UserObserver::class);
```

#### Inertia.js SPA Architecture
**No traditional Blade views** except root template (`app.blade.php`):

```php
// Controller returns Inertia, not views
return Inertia::render('Wallet/Index', [
    'balance' => format_bd_currency($wallet->balance),
    'transactions' => $transactions->paginate(20)
]);
```

**Benefits**:
- Single-page app experience
- No API endpoints needed for page data
- Server-side routing with client-side rendering
- Automatic CSRF protection
- File uploads via `useForm()`

#### Database Design

**Key Relationships**:
```
users (1) → (1) wallet
users (1) → (*) referrals (as referrer)
users (1) → (*) rewards
users (1) → (1) user_profile
users (1) → (*) user_passports
users (1) → (*) user_educations
users (1) → (*) service_applications
agencies (1) → (*) agency_country_assignments
agencies (1) → (*) agency_team_members
service_modules (*) → (*) countries (via assignments)
```

**Migration Count**: 200+ migrations
- User management: 15+ tables
- Profile system: 9 specialized tables
- Services: 25+ tables
- Agency system: 8 tables
- Financial: 5 tables (wallet, transactions, rewards)
- Content: 10+ tables (blogs, FAQs, pages)
- System: 8+ tables (settings, logs, notifications)

### Frontend Structure

#### Vue 3 Composition API
All components use `<script setup>` syntax:

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'

const props = defineProps(['balance'])
const { formatCurrency } = useBangladeshFormat()

const form = useForm({
    amount: ''
})
</script>
```

#### Component Organization
```
resources/js/
├── Pages/           # Full page components (Inertia pages)
│   ├── Admin/       # Admin dashboard pages
│   ├── Profile/     # User profile management
│   ├── Services/    # Service-specific pages
│   ├── Wallet/      # Wallet & transactions
│   └── User/        # User-facing pages
├── Components/      # Reusable components
│   ├── Base/        # Base design system components
│   ├── Profile/     # Profile section components
│   ├── SEO/         # SEO-related components
│   └── Services/    # Service UI components
├── Composables/     # Vue composables
│   ├── useBangladeshFormat.js
│   ├── useSEO.js
│   └── usePermissions.js
└── Layouts/         # Page layouts
    ├── AuthenticatedLayout.vue
    ├── GuestLayout.vue
    └── AdminLayout.vue
```

#### Design System
Professional ocean-themed palette (see `DESIGN_SYSTEM.md`):

**Colors**:
- **Ocean Blue** (Primary): `#0087ff` → Trust, professionalism
- **Sky** (Secondary): `#0ea5e9` → Peaceful actions
- **Growth Green** (Success): `#10b981` → Positive outcomes
- **Sunrise Orange** (CTA): `#f97316` → Energy, warnings
- **Gold** (Premium): `#eab308` → Achievement badges
- **Heritage** (Cultural): `#db2777` → Bangladesh accent

**Base Components** (10):
- `BaseCard`, `BaseButton`, `BaseInput`, `BaseSelect`
- `BaseBadge`, `BaseModal`, `BaseAlert`, `BaseProgress`
- `BaseSpinner`, `BaseStepper`

**Typography**:
- Font: Proxima Nova (fallback: Arial)
- Display sizes: `4xl`, `3xl`, `2xl`, `xl`
- Rhythm spacing: Consistent padding system

---

## 📦 Dependencies

### Backend (Composer)
```json
{
  "laravel/framework": "^12.0",           // Core framework
  "inertiajs/inertia-laravel": "^2.0",    // SPA adapter
  "laravel/breeze": "^2.3",               // Authentication scaffolding
  "laravel/sanctum": "^4.2",              // API authentication
  "laravel/scout": "^10.22",              // Full-text search
  "laravel/socialite": "^5.0",            // OAuth (Google, Facebook)
  "laravel/telescope": "^5.15",           // Debug & monitoring
  "laravel/reverb": "^1.6",               // WebSocket server
  "tightenco/ziggy": "^2.0",              // Route helpers in JS
  "barryvdh/laravel-dompdf": "^3.1",      // PDF generation
  "intervention/image": "^3.11",          // Image processing
  "spatie/laravel-activitylog": "^4.10",  // Audit trail
  "spatie/laravel-sitemap": "^7.3",       // XML sitemap
  "resend/resend-php": "^1.0",            // Email service
  "twilio/sdk": "^8.8"                    // SMS service
}
```

### Frontend (NPM)
```json
{
  "@inertiajs/vue3": "^2.0.0",           // Inertia.js client
  "@vitejs/plugin-vue": "^5.2.4",        // Vite Vue plugin
  "vue": "^3.5.0",                       // Vue 3 framework
  "tailwindcss": "^3.2.1",               // Utility-first CSS
  "@headlessui/vue": "^1.7.23",          // Unstyled UI components
  "@heroicons/vue": "^2.2.0",            // Icon library
  "chart.js": "^4.5.1",                  // Charts & analytics
  "flag-icons": "^7.5.0",                // Country flags
  "qrcode": "^1.5.4",                    // QR code generation
  "axios": "^1.11.0",                    // HTTP client
  "laravel-echo": "^2.2.6",              // WebSocket client
  "pusher-js": "^8.4.0"                  // Pusher client
}
```

---

## 🚀 Development Workflow

### Essential Commands

```powershell
# Start dev server (http://127.0.0.1:8000)
php artisan serve

# Watch assets with Hot Module Replacement
npm run dev

# Production build (commit to git)
npm run build

# Database operations
php artisan migrate:fresh --seed    # Fresh DB + seed data
php artisan migrate                 # Run new migrations
php artisan db:seed                 # Seed data only

# Route updates (CRITICAL after route changes)
php artisan ziggy:generate          # Generate JS route helpers

# Cache management
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

# File storage setup (required once)
php artisan storage:link            # Create public/storage symlink

# Development tools
php artisan telescope:install       # Debug dashboard
php artisan pail                    # Tail logs in terminal
php artisan tinker                  # Interactive REPL
```

### Git Workflow
```powershell
# Standard flow
git add .
git commit -m "feat: description"
git push origin main

# Branch management
git checkout -b feature/new-feature
git push -u origin feature/new-feature
```

### Testing
```powershell
php artisan test                    # Run PHPUnit tests
php artisan test --filter TestName  # Run specific test
```

---

## 🗂️ Key Files Reference

### Configuration Files
| File | Purpose |
|------|---------|
| `config/app.php` | Application settings |
| `config/auth.php` | Authentication configuration |
| `config/bangladesh.php` | Bangladesh-specific settings |
| `config/database.php` | Database connections |
| `config/filesystems.php` | File storage configuration |
| `config/mail.php` | Email configuration |
| `config/payment.php` | Payment gateway settings |
| `config/services.php` | Third-party service credentials |

### Route Files
| File | Purpose | Prefix |
|------|---------|--------|
| `routes/web.php` | Main web routes | `/` |
| `routes/api.php` | API endpoints | `/api` |
| `routes/auth.php` | Authentication routes | `/login`, `/register` |
| `routes/enhancements.php` | Feature enhancements | Various |
| `routes/pwa.php` | PWA-related routes | `/offline` |

**Route Groups**:
- Admin: `/admin/*` with `role:admin` middleware
- Profile: `/profile/*` with `auth` middleware
- Services: `/services/*` with `auth` middleware
- Public: No authentication required

### Helper Files
| File | Auto-Loaded | Functions |
|------|-------------|-----------|
| `app/Helpers/bangladesh_helpers.php` | ✅ Yes | `format_bd_*`, `validate_bd_*`, `get_bd_*` |
| `app/Helpers/DateHelper.php` | ✅ Yes | Date formatting utilities |
| `app/Helpers/settings_helper.php` | ✅ Yes | Site settings retrieval |

### Core Models (Top 20)
1. `User` - User accounts with roles
2. `UserProfile` - Basic profile information
3. `Wallet` - Digital wallet management
4. `WalletTransaction` - Transaction history
5. `Referral` - Referral relationships
6. `Reward` - Reward tracking
7. `ServiceModule` - Service definitions
8. `ServiceApplication` - User applications
9. `UserPassport` - Passport records
10. `UserEducation` - Education history
11. `UserWorkExperience` - Employment history
12. `Agency` - Agency profiles
13. `JobPosting` - Job listings
14. `JobApplication` - Job applications
15. `TouristVisa` - Tourist visa applications
16. `BlogPost` - Blog content
17. `SupportTicket` - Support system
18. `Appointment` - Booking system
19. `PaymentTransaction` - Payment records
20. `Country` - Country data

### Documentation Files
| File | Content |
|------|---------|
| `README.md` | Project overview & quick start |
| `MIGRATION_COMPLETE.md` | Repository migration details |
| `docs/MASTER_ROADMAP.md` | Development roadmap & phases |
| `docs/DEPLOYMENT_GUIDE.md` | Production deployment guide |
| `docs/DESIGN_SYSTEM.md` | UI/UX design specifications |
| `.github/copilot-instructions.md` | AI coding assistant guidelines |

---

## 🔐 Security Features

### Authentication & Authorization
- **Laravel Breeze**: Authentication scaffolding
- **Laravel Sanctum**: API token authentication
- **Multi-Role System**: Role-based access control
- **Password Hashing**: Bcrypt hashing
- **CSRF Protection**: Automatic token validation
- **Email Verification**: Optional email verification
- **Password Reset**: Secure password reset flow

### Data Protection
- **SQL Injection**: Eloquent ORM parameterized queries
- **XSS Prevention**: Vue.js automatic escaping
- **CORS Protection**: Configured in `fruitcake/php-cors`
- **File Upload Validation**: MIME type and size checks
- **Rate Limiting**: API throttling middleware

### Privacy Compliance
- **Privacy Policy**: Legal compliance page
- **Terms of Service**: User agreement documentation
- **Refund Policy**: Payment gateway requirement
- **Data Encryption**: Sensitive data encryption at rest
- **Activity Logging**: Comprehensive audit trail

---

## 📊 Database Statistics

### Table Categories
- **User Management**: 15 tables
- **Profile System**: 9 tables
- **Services**: 25 tables
- **Agency System**: 8 tables
- **Financial**: 5 tables
- **Content Management**: 10 tables
- **System/Admin**: 8 tables
- **Communication**: 6 tables

### Migration History
- **Total Migrations**: 200+
- **First Migration**: 2024-01-11 (User educations)
- **Latest Migration**: 2025-12-01 (Plugin architecture)
- **Migration Strategy**: Incremental with rollback support

### Seeder Data
Available seeders for testing:
- `RolesSeeder`: 4 roles (admin, user, agency, consultant)
- `CountrySeeder`: 195+ countries with ISO codes
- `CurrencySeeder`: 150+ currencies
- `LanguageSeeder`: 100+ languages
- `DemoUserSeeder`: Test users for each role
- `PluginServiceArchitectureSeeder`: 39 services
- `VisaRequirementsSeeder`: Country-specific requirements
- `HotelSeeder`, `CourseSeeder`, `UniversitySeeder`: Sample data

---

## 🌐 Internationalization (i18n)

### Current Support
- **Primary**: Bengali (bn) - Coming soon
- **Secondary**: English (en) - Active
- **Localization**: Bangladesh-specific formatting

### Language Files
```
lang/
├── bn/              # Bengali translations (planned)
│   ├── auth.php
│   ├── validation.php
│   └── messages.php
└── en/              # English translations (active)
    ├── auth.php
    ├── validation.php
    └── messages.php
```

### Future Expansion
- Arabic (ar) - Middle East market
- Hindi (hi) - Indian market
- Urdu (ur) - Pakistani market

---

## 📈 Performance Optimizations

### Backend
- **Query Optimization**: Eager loading relationships
- **Caching**: Redis/Memcached support
- **Queue Jobs**: Async email/SMS sending
- **Database Indexing**: Foreign keys and frequently queried columns
- **Route Caching**: `php artisan route:cache`
- **Config Caching**: `php artisan config:cache`
- **View Caching**: `php artisan view:cache`

### Frontend
- **Code Splitting**: Vite automatic code splitting
- **Lazy Loading**: Route-based component loading
- **Image Optimization**: Responsive images with `intervention/image`
- **Asset Minification**: Vite production build
- **CDN Support**: Configurable asset URL

### Database
- **Connection Pooling**: MySQL persistent connections
- **Query Caching**: Laravel query cache
- **Pagination**: All lists paginated (20 items default)
- **Soft Deletes**: Archive instead of hard delete

---

## 🔧 Troubleshooting Guide

### Common Issues

#### 1. Login Session Not Persisting
**Symptom**: Backend `Auth::attempt()` works, frontend session lost  
**Status**: Known bug, documented  
**Workaround**: Test with direct API calls or seed test users

#### 2. Ziggy Routes Not Updating
**Solution**:
```powershell
php artisan ziggy:generate
# Restart Vite dev server
npm run dev
```

#### 3. Form Validation Errors Not Showing
**Check**:
```vue
<input v-model="form.email" :class="{ 'border-red-500': form.errors.email }" />
<p v-if="form.errors.email">{{ form.errors.email }}</p>
```

#### 4. File Upload 404 Errors
**Solution**:
```powershell
php artisan storage:link
# Verify files in storage/app/public/, not storage/app/
```

#### 5. Currency/Date Format Issues
**Solution**: Always use helpers
```php
// PHP
format_bd_currency($amount)
format_bd_date($date)
```
```javascript
// Vue
const { formatCurrency, formatDate } = useBangladeshFormat()
```

#### 6. Migration Errors
**Fresh start**:
```powershell
php artisan migrate:fresh --seed
```

**Check migrations**:
```powershell
php artisan migrate:status
```

### Debugging Tools

#### Laravel Telescope
Access at `/telescope` (dev environment only):
- View all requests
- Database queries with timing
- Exception logs
- Mail preview
- Queue jobs

#### Laravel Pail
Real-time log streaming:
```powershell
php artisan pail
```

#### Database Queries
Enable query log temporarily:
```php
DB::enableQueryLog();
// ... your code
dd(DB::getQueryLog());
```

---

## 📝 Development Standards

### Code Style
- **PHP**: PSR-12 coding standard
- **JavaScript**: ESLint + Prettier
- **Vue**: Vue 3 style guide
- **Formatting**: Laravel Pint for PHP

### Naming Conventions
```
✅ CORRECT                    ❌ AVOID
user_profiles (plural)        user_profile (singular)
ServiceModule (PascalCase)    serviceModule (camelCase)
created_at (snake_case)       createdAt (camelCase)
format_bd_currency()          formatBdCurrency()
```

### Git Commit Messages
```
feat: Add wallet transaction history page
fix: Resolve passport upload validation error
docs: Update API documentation
refactor: Extract wallet service logic
test: Add referral system tests
```

### File Organization
```
✅ Organized by feature        ❌ Organized by type
Controllers/
  Profile/
    PassportController.php
    EducationController.php
  Wallet/
    WalletController.php
```

---

## 🎯 Roadmap & Future Enhancements

### Phase 3: In Progress (Current)
- [ ] Dedicated service interfaces (Jobs, Visa, Education, Travel)
- [ ] Support ecosystem (Help Center, FAQ search, Live Chat)
- [ ] Public features (Profile pages, Certificate verification)

### Phase 4: Planned
- [ ] Mobile apps (React Native)
- [ ] AI chatbot integration
- [ ] Advanced analytics dashboard
- [ ] Blockchain document verification
- [ ] Multi-language support (Arabic, Hindi, Urdu)

### Technical Debt
- [ ] Comprehensive test coverage (currently limited)
- [ ] Server-Side Rendering (Inertia SSR)
- [ ] API documentation (Swagger/OpenAPI)
- [ ] Performance benchmarking
- [ ] Load testing

---

## 🤝 Team & Support

### Roles
- **Developer**: Mahidul Islam Nakib
- **Email**: mahidulislamnakib@gmail.com
- **Repository**: https://github.com/mahidulislamnakib/bideshgomon

### Support Channels
- **Documentation**: `/docs` directory
- **Issues**: GitHub Issues
- **Email**: support@bideshgomon.com (if configured)

### Contribution Guidelines
1. Fork repository
2. Create feature branch
3. Make changes with tests
4. Submit pull request
5. Wait for review

---

## 📄 License

**License**: MIT License  
**Copyright**: © 2025 BideshGomon Platform

---

## 🎓 Learning Resources

### Laravel 12
- Official Docs: https://laravel.com/docs/12.x
- Laracasts: https://laracasts.com

### Inertia.js
- Official Docs: https://inertiajs.com
- Vue 3 Adapter: https://inertiajs.com/client-side-setup

### Vue 3
- Official Docs: https://vuejs.org
- Composition API: https://vuejs.org/guide/extras/composition-api-faq.html

### TailwindCSS
- Official Docs: https://tailwindcss.com/docs
- Playground: https://play.tailwindcss.com

---

## 📊 Appendix: File Counts

### Backend
- **Controllers**: 50+ files
- **Models**: 80+ files
- **Migrations**: 200+ files
- **Seeders**: 20+ files
- **Services**: 5+ files
- **Observers**: 1 file
- **Helpers**: 3 files

### Frontend
- **Pages**: 314+ Vue files
- **Components**: 50+ Vue files
- **Composables**: 5+ JavaScript files
- **Layouts**: 3 Vue files

### Configuration
- **Config Files**: 15+ PHP files
- **Route Files**: 5 PHP files
- **Environment**: `.env.example`

### Total Production Files: **2,154**

---

**Last Updated**: December 2, 2025  
**Document Version**: 1.0.0  
**Platform Version**: Laravel 12.x | Vue 3.x | Inertia 2.x  
**Target Market**: 🇧🇩 Bangladesh  
**Currency**: ৳ BDT  
**Timezone**: Asia/Dhaka (BST +06:00)

---

## 🎉 Quick Reference Card

### Bangladesh Formatting
```php
// Currency
৳1,000.00        → format_bd_currency(1000)

// Date
18/11/2025       → format_bd_date($date)

// Phone
01712-345678     → format_bd_phone($phone)
```

### Common Commands
```powershell
php artisan serve              # Start server
npm run dev                    # Watch assets
php artisan migrate:fresh --seed   # Reset DB
php artisan ziggy:generate     # Update routes
php artisan storage:link       # Link storage
```

### Key URLs
```
http://127.0.0.1:8000          # Dev server
http://127.0.0.1:8000/admin    # Admin dashboard
http://127.0.0.1:8000/telescope # Debug dashboard
http://127.0.0.1:8000/profile  # User profile
```

### Important Paths
```
app/Services/               # Business logic
app/Helpers/                # Helper functions
resources/js/Pages/         # Vue pages
resources/js/Components/    # Vue components
storage/app/public/         # File uploads
config/bangladesh.php       # Localization
```

---

**End of Project Summary** 🚀
