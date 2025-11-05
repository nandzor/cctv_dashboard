#!/bin/bash

# Setup Crontab for Materialized Views Refresh (No sudo required)
# This script sets up automated refresh of materialized views

# Auto-detect project directory (script location)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR"
CONTAINER_NAME="cctv_app_local"

echo "🔄 Setting up Materialized Views Crontab..."
echo "📁 Project directory: $PROJECT_DIR"
echo "🐳 Container: $CONTAINER_NAME"
echo ""

# Check if container exists
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "❌ Container $CONTAINER_NAME tidak berjalan!"
    echo "   Jalankan: docker compose -f docker-compose.local.yaml up -d"
    exit 1
fi

# Create log directory in project
mkdir -p "$PROJECT_DIR/logs"
touch "$PROJECT_DIR/logs/materialized-views-refresh.log"
touch "$PROJECT_DIR/logs/cache-clear.log"
touch "$PROJECT_DIR/logs/db-maintenance.log"

# Create crontab entries
CRON_ENTRIES="
# Materialized Views Refresh - Every 30 minutes
*/30 * * * * docker exec $CONTAINER_NAME php artisan materialized-views:refresh >> $PROJECT_DIR/logs/materialized-views-refresh.log 2>&1

# Cache Clear - Every 6 hours
0 */6 * * * docker exec $CONTAINER_NAME php artisan cache:clear >> $PROJECT_DIR/logs/cache-clear.log 2>&1

# Database Maintenance - Weekly on Sunday at 3 AM
0 3 * * 0 docker exec $CONTAINER_NAME php artisan migrate:status >> $PROJECT_DIR/logs/db-maintenance.log 2>&1
"

# Add crontab entries
echo "$CRON_ENTRIES" | crontab -

echo "✅ Crontab setup completed!"
echo ""
echo "📋 Scheduled Jobs:"
echo "  - Materialized Views Refresh: Every 30 minutes"
echo "  - Cache Clear: Every 6 hours"
echo "  - Database Maintenance: Weekly (Sunday 3 AM)"
echo ""
echo "📁 Log Files:"
echo "  - $PROJECT_DIR/logs/materialized-views-refresh.log"
echo "  - $PROJECT_DIR/logs/cache-clear.log"
echo "  - $PROJECT_DIR/logs/db-maintenance.log"
echo ""
echo "🔍 To view current crontab: crontab -l"
echo "🔍 To view logs: tail -f $PROJECT_DIR/logs/materialized-views-refresh.log"
echo ""
echo "⚙️  To modify schedule, edit crontab: crontab -e"
echo ""
echo "🧪 Test the command manually:"
echo "  docker exec $CONTAINER_NAME php artisan materialized-views:refresh"
