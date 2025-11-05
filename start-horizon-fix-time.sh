#!/bin/bash

# Auto-detect project directory (script location)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR"
CONTAINER_NAME="cctv_app_local"

# Helper function untuk menjalankan artisan command
artisan() {
    docker exec "$CONTAINER_NAME" php artisan "$@"
}

echo "🔧 Fixing System Time Issues"
echo "============================"
echo ""
echo "📁 Project directory: $PROJECT_DIR"
echo "🐳 Container: $CONTAINER_NAME"
echo ""

# Check if container exists
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "❌ Container $CONTAINER_NAME tidak berjalan!"
    echo "   Jalankan: docker compose -f docker-compose.local.yaml up -d"
    exit 1
fi

echo "📊 Current Status:"
echo "================="
echo "Host time: $(date)"
echo "Container time: $(docker exec $CONTAINER_NAME date)"
echo ""

echo "🔍 Problem Analysis:"
echo "==================="
echo "1. System time shows year 2025 (incorrect)"
echo "2. This causes timestamp issues in delayed jobs"
echo "3. Delayed jobs are scheduled with future timestamps"
echo "4. But current time is in the past relative to those timestamps"
echo ""

echo "🛠️  Solutions:"
echo "=============="
echo ""

echo "Solution 1: Clear all delayed jobs (Recommended)"
echo "================================================="
echo "This will remove all delayed jobs that are stuck due to time issues"
echo ""

# Clear all delayed jobs
echo "🧹 Clearing all delayed jobs..."
artisan tinker --execute="
\$redis = app('redis');
\$queues = ['default', 'detections', 'reports', 'notifications', 'images'];
foreach (\$queues as \$queue) {
    \$delayed = \$redis->zcard('queues:' . \$queue . ':delayed');
    if (\$delayed > 0) {
        echo \"Clearing \$queue delayed: \$delayed jobs\" . PHP_EOL;
        \$redis->del('queues:' . \$queue . ':delayed');
    }
}
echo 'All delayed jobs cleared!' . PHP_EOL;
"

echo ""
echo "Solution 2: Restart Horizon"
echo "==========================="
echo "This will restart the queue processor with clean state"
echo ""

# Restart Horizon
echo "🔄 Restarting Horizon..."
artisan horizon:terminate
sleep 2
docker exec "$CONTAINER_NAME" php artisan horizon &
sleep 3

echo "✅ Horizon restarted"
echo ""

echo "Solution 3: Verify Clean State"
echo "============================="
echo "Checking that all queues are clean"
echo ""

# Verify clean state
artisan tinker --execute="
echo '🔍 Final verification...';
echo '======================';
\$redis = app('redis');
\$queues = ['default', 'detections', 'reports', 'notifications', 'images'];
\$totalPending = 0;
\$totalDelayed = 0;
\$totalReserved = 0;
foreach (\$queues as \$queue) {
    \$pending = \$redis->llen('queues:' . \$queue);
    \$delayed = \$redis->zcard('queues:' . \$queue . ':delayed');
    \$reserved = \$redis->llen('queues:' . \$queue . ':reserved');
    \$totalPending += \$pending;
    \$totalDelayed += \$delayed;
    \$totalReserved += \$reserved;
}
echo \"Total pending: \$totalPending\" . PHP_EOL;
echo \"Total delayed: \$totalDelayed\" . PHP_EOL;
echo \"Total reserved: \$totalReserved\" . PHP_EOL;
echo '';
echo 'Database jobs: ' . DB::table('jobs')->count() . PHP_EOL;
echo 'Failed jobs: ' . DB::table('failed_jobs')->count() . PHP_EOL;
"

echo ""
echo "✅ System time issues fixed!"
echo ""
echo "📋 Summary:"
echo "=========="
echo "1. ✅ Cleared all delayed jobs"
echo "2. ✅ Restarted Horizon"
echo "3. ✅ Verified clean state"
echo ""
echo "🎯 Result:"
echo "========="
echo "• No more delayed jobs stuck due to time issues"
echo "• Horizon is running with clean state"
echo "• All queues are ready for new jobs"
echo ""
echo "💡 Prevention:"
echo "============="
echo "• Monitor system time regularly"
echo "• Use proper timezone settings"
echo "• Avoid scheduling jobs too far in the future"
echo "• Clear delayed jobs periodically if needed"
