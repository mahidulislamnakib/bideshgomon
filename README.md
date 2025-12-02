#  BideshGomon Platform

**Bangladesh-Focused Visa Application & Migration Platform**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-2.0-purple.svg)](https://inertiajs.com)
[![Vue](https://img.shields.io/badge/Vue.js-3.x-brightgreen.svg)](https://vuejs.org)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-blue.svg)](https://tailwindcss.com)

##  About

BideshGomon is a comprehensive digital platform designed specifically for Bangladeshi users seeking to manage visa applications, migration services, and related documentation. Built with modern technologies and tailored for the Bangladesh market with localized formatting, currency ( BDT), and cultural preferences.

##  Key Features

- ** Multi-Role System**: Admin, User, Agency, Consultant roles
- ** Comprehensive User Profiles**: 9 specialized profile sections (Passport, Education, Work Experience, etc.)
- ** Digital Wallet System**: Credit/Debit with transaction history
- ** Referral & Rewards**: Earn rewards by referring users
- ** Visa Management**: Application tracking, document uploads, status monitoring
- ** Agency System**: Verification, resource management, country assignments
- ** Bangladesh Localization**: Date (DD/MM/YYYY), Currency (), Phone formats
- ** Progressive Web App (PWA)**: Installable on mobile devices
- ** Modern UI**: TailwindCSS with mobile-first responsive design

##  Tech Stack

- **Backend**: Laravel 12
- **Frontend**: Vue 3 + Inertia.js 2.0
- **Styling**: TailwindCSS 3.x
- **Database**: MySQL/SQLite
- **Build Tool**: Vite
- **Authentication**: Laravel Breeze + Sanctum

##  Quick Start

\\\ash
# Clone repository
git clone https://github.com/mahidulislamnakib/bideshgomon.git
cd bideshgomon

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations and seed data
php artisan migrate --seed

# Build assets
npm run build

# Start development server
php artisan serve
npm run dev
\\\

##  Production Deployment

Use the included deployment script for production setup:

\\\ash
chmod +x deploy.sh
./deploy.sh
\\\

The script handles:
-  Environment configuration
-  Dependency installation
-  Database migrations
-  Asset compilation
-  Cache optimization
-  Permission setup

##  Project Structure

\\\
bideshgomon/
 app/
    Http/Controllers/     # API & Web Controllers
    Models/               # Eloquent Models
    Services/             # Business Logic
    Observers/            # Model Observers
    Helpers/              # Bangladesh Localization Helpers
 resources/
    js/
       Pages/           # Inertia Pages (Vue)
       Components/      # Reusable Vue Components
       Composables/     # Vue Composables
    views/               # Blade Templates
 database/
    migrations/          # Database Schema
    seeders/             # Data Seeders
 routes/
    web.php             # Web Routes
    api.php             # API Routes
 config/
     bangladesh.php      # BD-specific configs
\\\

##  Default Credentials

After seeding, use these credentials to log in:

**Admin**:
- Email: \dmin@bideshgomon.com\
- Password: \password\

**User**:
- Email: \user@bideshgomon.com\
- Password: \password\

##  Bangladesh Localization

All formatters are built-in:

**Backend (PHP)**:
\\\php
format_bd_currency(5000);     // 5,000.00
format_bd_date();        // 18/11/2025
format_bd_phone('01712345678'); // 01712-345678
\\\

**Frontend (Vue)**:
\\\javascript
import { useBangladeshFormat } from '@/Composables/useBangladeshFormat'
const { formatCurrency, formatDate } = useBangladeshFormat()

formatCurrency(1000)  // "1,000.00"
formatDate(date)      // "18/11/2025"
\\\

##  Documentation

- **Setup Guide**: \docs/DEPLOYMENT_GUIDE.md\
- **Design System**: \docs/DESIGN_SYSTEM.md\
- **Roadmap**: \docs/MASTER_ROADMAP.md\
- **AI Agent Instructions**: \.github/copilot-instructions.md\

##  Contributing

This is a proprietary project. For access or contributions, please contact the project owner.

##  License

Proprietary. All rights reserved.

##  Support

For support, contact: **mahidulislamnakib@gmail.com**

---

**Built with  for Bangladesh **

**Currency**:  BDT | **Timezone**: Asia/Dhaka (BST +06:00) | **Date Format**: DD/MM/YYYY
