#!/bin/bash

# Production Deployment Script for DISI COMMANDES
# ----------------------------------------------

# Exit on error
set -e

echo "Starting deployment process..."

# Pull latest changes from git
echo "Pulling latest changes from repository..."
git pull origin main

# Install/update composer dependencies
echo "Installing/updating Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Install/update npm dependencies and build assets
echo "Installing/updating npm dependencies..."
npm install
echo "Building frontend assets..."
npm run build

# Copy production environment file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from .env.production..."
    cp .env.production .env
fi

# Generate application key if needed
if grep -q "APP_KEY=" .env && grep -q "APP_KEY=base64:" .env; then
    echo "Application key exists."
else
    echo "Generating application key..."
    php artisan key:generate
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Clear and optimize the application
echo "Optimizing application..."
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "Setting file permissions..."
chmod -R 755 .
chmod -R 777 storage bootstrap/cache

echo "Deployment completed successfully!" 