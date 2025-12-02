#!/bin/bash

# BideshGomon Platform - Complete Setup Script
# This script sets up the Laravel application for production deployment

echo "🚀 Starting BideshGomon Platform Setup..."

# Check if .env exists, copy from example if not
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
else
    echo "✅ .env file already exists"
fi

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev

# Install Node dependencies
echo "📦 Installing Node.js dependencies..."
npm install

# Generate application key
echo "🔑 Generating application key..."
php artisan key:generate

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Seed essential data
echo "🌱 Seeding database with essential data..."
php artisan db:seed --class=RolesSeeder
php artisan db:seed --class=ProfileManagementSeeder

# Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link

# Clear and cache configuration
echo "🧹 Clearing and caching configuration..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate Ziggy routes for frontend
echo "🗺️ Generating Ziggy routes..."
php artisan ziggy:generate

# Build frontend assets
echo "🎨 Building frontend assets..."
npm run build

# Set proper permissions
echo "🔒 Setting proper permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

echo "✅ Setup complete! Your application is ready to deploy."
echo ""
echo "📋 Next steps:"
echo "1. Configure your .env file with production credentials"
echo "2. Update APP_URL, DB_*, MAIL_* settings"
echo "3. Run: php artisan serve (for testing) or configure web server"
