.PHONY: build up down restart test fresh optimize bash logs migrate-legacy bootstrap-db deploy assets sync sync_clear

# Deploy to Production Cluster
deploy:
	./deploy.sh
 
# Deploy Production Image locally
build:
	docker compose build

# Setup Environment
up:
	docker compose up -d --remove-orphans

# Spin down
down:
	docker compose down

# Restart
restart: down up

# Bootstrap MySQL Group Replication Cluster
bootstrap-db:
	@echo "Initializing MySQL Group Replication Cluster..."
	@echo "  -> Preparing db-1..."
	-docker compose exec db-1 mysql -u root -psecret -e "INSTALL PLUGIN group_replication SONAME 'group_replication.so';"
	docker compose exec db-1 mysql -u root -psecret -e "SET GLOBAL group_replication_group_name='aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'; SET GLOBAL group_replication_local_address='db-1:33061'; SET GLOBAL group_replication_group_seeds='db-1:33061,db-2:33061,db-3:33061'; SET GLOBAL group_replication_single_primary_mode=ON; SET GLOBAL group_replication_enforce_update_everywhere_checks=OFF; SET GLOBAL group_replication_bootstrap_group=ON; START GROUP_REPLICATION; SET GLOBAL group_replication_bootstrap_group=OFF;"
	@echo "  -> Preparing db-2..."
	-docker compose exec db-2 mysql -u root -psecret -e "INSTALL PLUGIN group_replication SONAME 'group_replication.so';"
	docker compose exec db-2 mysql -u root -psecret -e "SET GLOBAL group_replication_group_name='aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'; SET GLOBAL group_replication_local_address='db-2:33061'; SET GLOBAL group_replication_group_seeds='db-1:33061,db-2:33061,db-3:33061'; SET GLOBAL group_replication_single_primary_mode=ON; SET GLOBAL group_replication_enforce_update_everywhere_checks=OFF; RESET BINARY LOGS AND GTIDS; START GROUP_REPLICATION;"
	@echo "  -> Preparing db-3..."
	-docker compose exec db-3 mysql -u root -psecret -e "INSTALL PLUGIN group_replication SONAME 'group_replication.so';"
	docker compose exec db-3 mysql -u root -psecret -e "SET GLOBAL group_replication_group_name='aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee'; SET GLOBAL group_replication_local_address='db-3:33061'; SET GLOBAL group_replication_group_seeds='db-1:33061,db-2:33061,db-3:33061'; SET GLOBAL group_replication_single_primary_mode=ON; SET GLOBAL group_replication_enforce_update_everywhere_checks=OFF; RESET BINARY LOGS AND GTIDS; START GROUP_REPLICATION;"
	@echo "Cluster initialized!"

# Run DB Migrations & Reset
fresh:
	docker compose exec app-1 php artisan migrate:fresh --seed --force --seeder=ProductionSeeder 
	docker compose exec app-1 php artisan optimize:clear
	$(MAKE) assets

# Sync assets from container to host for Nginx
assets:
	@echo "Syncing assets from container to host..."
	docker compose cp app-1:/var/www/html/public/build ./public/
	@echo "Assets synced!"

# Sync local code changes into both running app containers
# Use this when docker-compose volumes are commented out (production image mode)
sync:
	@echo "==> Syncing code into app-1 and app-2..."
	@for container in app-1 app-2; do \
		echo "  -> $$container: routes..."; \
		docker compose cp routes/. $$container:/var/www/html/routes/; \
		echo "  -> $$container: app/..."; \
		docker compose cp app/. $$container:/var/www/html/app/; \
		echo "  -> $$container: resources/views/..."; \
		docker compose cp resources/views/. $$container:/var/www/html/resources/views/; \
		echo "  -> $$container: config/..."; \
		docker compose cp config/. $$container:/var/www/html/config/; \
		echo "  -> $$container: Modules/..."; \
		docker compose exec $$container mkdir -p /var/www/html/Modules; \
		docker compose cp Modules/. $$container:/var/www/html/Modules/; \
		echo "  -> $$container: vendor/..."; \
		docker compose cp vendor/. $$container:/var/www/html/vendor/; \
		echo "  -> $$container: composer.json..."; \
		docker compose cp composer.json $$container:/var/www/html/composer.json; \
		echo "  -> $$container: bootstrap/..."; \
		docker compose cp bootstrap/. $$container:/var/www/html/bootstrap/; \
		docker compose exec --user root $$container chmod -R 777 /var/www/html/bootstrap/cache; \
		echo "  [OK] $$container synced"; \
	done
	@echo "==> All containers synced!"

# Sync code AND clear all caches on both nodes (use after any code change)
sync_clear: sync
	@echo "==> Clearing and rebuilding caches on both nodes..."
	docker compose exec app-1 php artisan optimize:clear
	docker compose exec app-2 php artisan optimize:clear
	docker compose exec app-1 php artisan optimize
	docker compose exec app-2 php artisan optimize
	@echo "==> Syncing build assets to all nodes (app + web)..."
	@for c in app-1 app-2 web-1 web-2; do \
		docker compose cp public/build/. $$c:/var/www/html/public/build/ && echo "  [OK] $$c build synced"; \
	done
	@echo "==> Done! All nodes are up to date."

# Full Legacy Migration Process
migrate-legacy:
	@echo "Step 1: Setting up fresh structural database..."
	docker compose exec app-1 php artisan migrate:fresh --force -v
	docker compose exec app-1 php artisan db:seed --class=ProductionSeeder --force -v
	
	@echo "Step 2 & 3: Extracting Legacy Schema & Data into Staging..."
	@rm -f staging_raw.sql
	@for table in audit_logs designations calls call_status_history users user_scopes beats sectors zones call_types call_sub_types vehicle_types regions; do \
		echo "  -> Extracting $$table..." ; \
		sed -n "/-- Table structure for table \`$$table\`/,/-- Table structure for table \`/p" Dump20260507.sql | grep -v "\-\- Table structure for table \`" >> staging_raw.sql || true ; \
	done
	
	@# Rename target tables to legacy_*
	@for t in audit_logs designations calls call_status_history users user_scopes beats sectors zones call_types call_sub_types vehicle_types regions; do \
		sed -i "s/\`$$t\`/\`legacy_$$t\`/gI" staging_raw.sql ; \
		sed -i "s/ $$t / legacy_$$t /gI" staging_raw.sql ; \
	done
	@sed -i "s/INSERT INTO/INSERT IGNORE INTO/gI" staging_raw.sql
	
	@# Remove constraints and locking
	@sed -i '/LOCK TABLES/d' staging_raw.sql
	@sed -i '/UNLOCK TABLES/d' staging_raw.sql
	@sed -i '/CONSTRAINT/d' staging_raw.sql
	@sed -i '/FOREIGN KEY/d' staging_raw.sql
	@sed -i '/REFERENCES/d' staging_raw.sql
	@perl -i -0777 -pe 's/,\s*\)/\n\)/g' staging_raw.sql
	
	@echo "Step 3: Importing legacy data into Staging..."
	@(echo "SET SESSION sql_mode = ''; SET FOREIGN_KEY_CHECKS=0;"; cat staging_raw.sql) | docker compose exec -T db-1 mysql -f -u root -psecret 130_crm_db
	@rm staging_raw.sql

	@echo "Step 4: Mapping categories, offices and converting timestamps (GMT -> PKT)..."
	docker compose exec app-1 php artisan migrate --path=database/migrations/legacy_fixes --force -v
	
	@echo "Step 5: Finalizing system categories..."
	docker compose exec app-1 php artisan db:seed --class=NHMPCategorySeeder --force -v
	
	@echo "Step 6: Clearing caches..."
	docker compose exec app-1 php artisan optimize:clear
	@echo "Migration Process Complete!"

# Optimize caches
optimize:
	docker compose exec app-1 php artisan optimize
	docker compose exec app-1 php artisan view:cache
	docker compose exec app-1 php artisan event:cache


clear_prod:
	docker compose exec app-1 php artisan config:clear
	docker compose exec app-1 php artisan route:clear
	docker compose exec app-1 php artisan view:clear
	docker compose exec app-1 php artisan cache:clear
	docker compose exec app-1 php artisan event:clear
	docker compose exec app-1 php artisan view:cache
	docker compose exec app-1 php artisan event:cache
	docker compose exec app-1 php artisan optimize:clear
	docker compose exec app-1 php artisan optimize
	docker compose exec app-2 php artisan config:clear
	docker compose exec app-2 php artisan route:clear
	docker compose exec app-2 php artisan view:clear
	docker compose exec app-2 php artisan cache:clear
	docker compose exec app-2 php artisan event:clear
	docker compose exec app-2 php artisan view:cache
	docker compose exec app-2 php artisan event:cache
	docker compose exec app-2 php artisan optimize:clear
	docker compose exec app-2 php artisan optimize


# Follow logs
logs:
	docker compose logs -f haproxy

# Exec inside Container
bash:
	docker compose exec app-1 sh
