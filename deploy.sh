#!/bin/bash
# 🚀 High Availability Zero-Downtime Deployment Script
# This script implements the Atomic Symlink Deployment strategy for PHP-FPM nodes.

set -e

# --- Configuration ---
if [ -f .deploy_config.sh ]; then
    source .deploy_config.sh
else
    echo "❌ .deploy_config.sh not found! Please create it."
    exit 1
fi

RELEASE_ID=$(date +%Y%m%d%H%M%S)

echo "📦 Starting deployment: ${RELEASE_ID}"

# 1. Build locally
echo "🏗️  Building application locally..."
docker run --rm -v $(pwd):/app -w /app composer:2.7 composer install --no-dev --optimize-autoloader --no-interaction
docker run --rm -v $(pwd):/app -w /app node:22-alpine sh -c "npm install && npm run build"

# 2. Distribute to all nodes
for node in "${NODES[@]}"; do
    echo "🚚 Syncing to ${node}..."
    ssh $node "mkdir -p ${APP_PATH}/releases/${RELEASE_ID}"
    rsync -azP --delete --exclude=".git" --exclude="node_modules" ./ $node:${APP_PATH}/releases/${RELEASE_ID}
    
    # Ensure shared directories exist on node
    ssh $node "mkdir -p ${APP_PATH}/shared/storage ${APP_PATH}/shared/framework/cache ${APP_PATH}/shared/framework/sessions ${APP_PATH}/shared/framework/views"
done

# 3. Migration (Run on a single node)
echo "📂 Running migrations on ${NODES[0]}..."
ssh ${NODES[0]} "cd ${APP_PATH}/releases/${RELEASE_ID} && php artisan migrate --force"

# 4. Atomic Switch & Reload
for node in "${NODES[@]}"; do
    echo "🔄 Switching symlinks on ${node}..."
    ssh $node "
        # Link shared .env
        ln -sfn ${APP_PATH}/shared/.env ${APP_PATH}/releases/${RELEASE_ID}/.env
        
        # Link shared storage
        rm -rf ${APP_PATH}/releases/${RELEASE_ID}/storage
        ln -sfn ${APP_PATH}/shared/storage ${APP_PATH}/releases/${RELEASE_ID}/storage
        
        # Update current pointer
        ln -sfn ${APP_PATH}/releases/${RELEASE_ID} ${APP_PATH}/current
        
        # Optimize & Reload
        cd ${APP_PATH}/current
        php artisan optimize
        sudo systemctl reload ${PHP_SERVICE}
    "
done

# 5. Cleanup old releases (keep last 5)
for node in "${NODES[@]}"; do
    echo "🧹 Cleaning up old releases on ${node}..."
    ssh $node "cd ${APP_PATH}/releases && ls -t | tail -n +6 | xargs rm -rf --"
done

echo "✅ Deployment successful!"
