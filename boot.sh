#!/bin/bash
docker-compose up -d db redis
sed -i 's/DB_HOST=.*$/DB_HOST=127.0.0.1/g' .env
sed -i 's/REDIS_HOST=.*$/REDIS_HOST=127.0.0.1/g' .env
php artisan config:clear
echo "Waiting for MySQL 8.4 to initialize natively..."
sleep 20
php artisan migrate:fresh --seed --force
nohup php artisan serve --port=8080 > storage/logs/serve.log 2>&1 &
echo "Done"
