#!/bin/bash

# Setup Crontab for CCTV Dashboard
# This script sets up the crontab for Laravel scheduler

# Auto-detect project directory (script location)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR"
CONTAINER_NAME="cctv_app_local"

# Helper function untuk menjalankan artisan command
artisan() {
    docker exec "$CONTAINER_NAME" php artisan "$@"
}

echo "🔄 Setting up crontab for CCTV Dashboard..."
echo "📁 Project directory: $PROJECT_DIR"
echo "🐳 Container: $CONTAINER_NAME"
echo ""

# Check if container exists
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "❌ Container $CONTAINER_NAME tidak berjalan!"
    echo "   Jalankan: docker compose -f docker-compose.local.yaml up -d"
    exit 1
fi

# Check if crontab already exists
if crontab -l 2>/dev/null | grep -q "cctv_dashboard"; then
    echo "⚠ Crontab entry already exists for cctv_dashboard"
    echo "Current crontab entries:"
    crontab -l | grep cctv_dashboard
    echo ""
    echo "Do you want to update it? (y/n)"
    read -r response
    if [[ "$response" != "y" ]]; then
        echo "❌ Crontab setup cancelled"
        exit 0
    fi
fi

# Create temporary crontab file
TEMP_CRON=$(mktemp)

# Get existing crontab (excluding cctv_dashboard entries)
crontab -l 2>/dev/null | grep -v "cctv_dashboard" > "$TEMP_CRON"

# Add new crontab entries
cat >> "$TEMP_CRON" << EOF

# CCTV Dashboard Laravel Scheduler
# Run Laravel scheduler every minute (primary method)
* * * * * cd "$PROJECT_DIR" && docker exec $CONTAINER_NAME php artisan schedule:run >> /var/log/cctv_scheduler.log 2>&1

# Backup: Direct cron entries for all scheduled tasks (if scheduler fails)
# 1. Update daily reports at 01:00 (UpdateDailyReportJob)
0 1 * * * cd "$PROJECT_DIR" && docker exec $CONTAINER_NAME php artisan tinker --execute="UpdateDailyReportJob::dispatch(now()->yesterday()->toDateString())->onQueue('reports');" >> /var/log/cctv_reports.log 2>&1

# 2. Generate counting reports at 01:15 (reports:generate-counting)
15 1 * * * cd "$PROJECT_DIR" && docker exec $CONTAINER_NAME php artisan reports:generate-counting --days=1 >> /var/log/cctv_reports.log 2>&1

# 3. Aggregate daily logs at 01:30 (AggregateApiUsageJob + AggregateWhatsAppDeliveryJob)
30 1 * * * cd "$PROJECT_DIR" && docker exec $CONTAINER_NAME php artisan tinker --execute="AggregateApiUsageJob::dispatch(now()->yesterday()->toDateString())->onQueue('reports'); AggregateWhatsAppDeliveryJob::dispatch(now()->yesterday()->toDateString())->onQueue('reports');" >> /var/log/cctv_reports.log 2>&1

# 4. Cleanup old files at 02:00 (CleanupOldFilesJob)
0 2 * * * cd "$PROJECT_DIR" && docker exec $CONTAINER_NAME php artisan tinker --execute="CleanupOldFilesJob::dispatch(90)->onQueue('maintenance');" >> /var/log/cctv_reports.log 2>&1

# 5. Update daily reports every 5 minutes (UpdateDailyReportJob for all branches)
*/5 * * * * cd "$PROJECT_DIR" && docker exec $CONTAINER_NAME php artisan tinker --execute="\$branches = \App\Models\CompanyBranch::where('status', 'active')->get(); foreach(\$branches as \$branch) { UpdateDailyReportJob::dispatch(now()->toDateString(), \$branch->id)->onQueue('reports'); }" >> /var/log/cctv_reports.log 2>&1

# 6. Generate monthly reports on 1st of month at 02:00 (UpdateMonthlyReportJob)
0 2 1 * * cd "$PROJECT_DIR" && docker exec $CONTAINER_NAME php artisan tinker --execute="UpdateMonthlyReportJob::dispatch(now()->subMonth()->format('Y-m'))->onQueue('reports');" >> /var/log/cctv_reports.log 2>&1

# 7. Clean failed jobs weekly (queue:prune-failed)
0 0 * * 0 cd "$PROJECT_DIR" && docker exec $CONTAINER_NAME php artisan queue:prune-failed --hours=168 >> /var/log/cctv_reports.log 2>&1

# 8. Inspire command hourly (for testing)
0 * * * * cd "$PROJECT_DIR" && docker exec $CONTAINER_NAME php artisan inspire >> /var/log/cctv_reports.log 2>&1
EOF

# Install the new crontab
crontab "$TEMP_CRON"

# Clean up
rm "$TEMP_CRON"

echo "✅ Crontab setup completed!"
echo ""
echo "📋 Current crontab entries:"
crontab -l | grep -A 10 "CCTV Dashboard"
echo ""
echo "🔍 To verify scheduler is working, check:"
echo "   docker exec $CONTAINER_NAME php artisan schedule:list"
echo ""
echo "📊 To test report generation manually:"
echo "   # Daily reports"
echo "   docker exec $CONTAINER_NAME php artisan reports:generate-counting --date=\$(date -d 'yesterday' +%Y-%m-%d)"
echo "   # Monthly reports"
echo "   docker exec $CONTAINER_NAME php artisan reports:generate-monthly --month=\$(date +%Y-%m)"
echo "   # Update daily reports for all branches"
echo "   docker exec $CONTAINER_NAME php artisan tinker --execute=\"\$branches = \App\Models\CompanyBranch::where('status', 'active')->get(); foreach(\$branches as \$branch) { UpdateDailyReportJob::dispatch(now()->toDateString(), \$branch->id)->onQueue('reports'); }\""
echo ""
echo "📋 Scheduled tasks included:"
echo "   • Laravel scheduler (every minute)"
echo "   • Update daily reports (01:00 daily)"
echo "   • Generate counting reports (01:15 daily)"
echo "   • Aggregate daily logs (01:30 daily)"
echo "   • Cleanup old files (02:00 daily)"
echo "   • Update daily reports every 5 minutes"
echo "   • Generate monthly reports (1st of month at 02:00)"
echo "   • Clean failed jobs (weekly)"
echo "   • Inspire command (hourly for testing)"
echo ""
echo "📝 Log files:"
echo "   • /var/log/cctv_scheduler.log (Laravel scheduler)"
echo "   • /var/log/cctv_reports.log (Direct cron tasks)"
