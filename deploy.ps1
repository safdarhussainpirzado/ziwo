# CRM Deployment Script (PowerShell)
# This script is intended to be run by a GitHub Actions runner on Windows Server.

$ErrorActionPreference = "Stop"

# 1. Navigate to project root
Write-Host "--- Current Directory: $(Get-Location)"
# cd C:\inetpub\wwwroot\130crm # Update this if needed

# 2. Pull latest changes
Write-Host "--- Pulling latest code..."
git pull origin main

# 3. PHP Dependencies
Write-Host "--- Installing PHP dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# 4. Frontend Assets
Write-Host "--- Building frontend assets..."
npm install
npm run build

# 5. Database Migrations
Write-Host "--- Running database migrations..."
php artisan migrate --force

# 6. Optimization & Cache
Write-Host "--- Clearing and building caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 7. Permissions (Optional, dependent on setup)
# icacls "storage" /grant "IIS AppPool\YourAppPoolName:(OI)(CI)F" /T

Write-Host "--- Deployment finished successfully! 🚀"
