#!/bin/bash

# Setup Crontab for CCTV Dashboard
# This script sets up the crontab for Laravel scheduler

echo "🔄 Setting up crontab for CCTV Dashboard..."

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
# Run Laravel scheduler every minute
* * * * * cd /home/nandzo/app/cctv_dashboard && docker exec cctv_app_staging php artisan schedule:run >> /dev/null 2>&1

# Alternative: Run specific commands directly (if scheduler fails)
# Daily reports at 01:00
# 0 1 * * * cd /home/nandzo/app/cctv_dashboard && docker exec cctv_app_staging php artisan reports:generate-counting --date=\$(date -d "yesterday" +\%Y-\%m-\%d) >> /var/log/cctv_reports.log 2>&1

# Monthly reports on 1st of month at 02:00
# 0 2 1 * * cd /home/nandzo/app/cctv_dashboard && docker exec cctv_app_staging php artisan reports:generate-monthly --month=\$(date -d "last month" +\%Y-\%m) >> /var/log/cctv_reports.log 2>&1
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
echo "   docker exec cctv_app_staging php artisan schedule:list"
echo ""
echo "📊 To test report generation manually:"
echo "   docker exec cctv_app_staging php artisan reports:generate-counting --date=\$(date -d 'yesterday' +%Y-%m-%d)"
echo "   docker exec cctv_app_staging php artisan reports:generate-monthly --month=\$(date +%Y-%m)"
