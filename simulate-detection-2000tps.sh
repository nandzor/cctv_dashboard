#!/bin/bash

# Auto-detect project directory (script location)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR"
CONTAINER_NAME="cctv_app_local"

# Helper function untuk menjalankan artisan command
artisan() {
    docker exec "$CONTAINER_NAME" php artisan "$@"
}

echo "🚀 CCTV Dashboard Detection API 2000 TPS Simulation"
echo "=================================================="
echo "Simulating 2000 TPS (Transactions Per Second) to /api/v1/detection/log"
echo ""
echo "📁 Project directory: $PROJECT_DIR"
echo "🐳 Container: $CONTAINER_NAME"
echo ""
echo "📋 Active Queues in this Simulation:"
echo "===================================="
echo "✅ Detections queue - Processing detection data"
echo "✅ SendWhatsAppJob queue - Sending WhatsApp notifications"
echo ""
echo "❌ Inactive Queues (not used in this simulation):"
echo "================================================="
echo "• Reports queue - Disabled for this simulation"
echo "• Notifications queue - Not used in this simulation"
echo "• Images queue - Not used in this simulation"
echo "• Default queue - Not used in this simulation"
echo ""

# Check if container exists
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "❌ Container $CONTAINER_NAME tidak berjalan!"
    echo "   Jalankan: docker compose -f docker-compose.local.yaml up -d"
    exit 1
fi

# Use provided API credentials
echo "📋 Using provided API credentials..."
API_KEY="cctv_XQXuszrVkCMIhcYp9BAQm7qbFCxuT1l8"
API_SECRET="67QZAwFXv2VEGNNptY30pqnuuvnR88mjpURjfksaeJdcipdIcMDPprstWYObJNYX"

echo "✅ Using API Key: $API_KEY"
echo ""

# Base URL - detect port from docker-compose or use default
BASE_URL="http://localhost:9001"

# Function to generate random detection data
generate_detection_data() {
    local id=$1
    local branches=(1 2 4 6)
    local devices=("NODE_JKT001_001" "NODE_BDG001_001" "CAM_SBY001_001" "MIKROTIK_SBY001")

    local branch=${branches[$((RANDOM % ${#branches[@]}))]}
    local device=${devices[$((RANDOM % ${#devices[@]}))]}
    local re_id="REID_2000TPS_$(printf "%07d" $id)"

    cat << EOF
{
    "re_id": "$re_id",
    "branch_id": $branch,
    "device_id": "$device"
}
EOF
}

# Function to make API call
make_api_call() {
    local id=$1

    # Create JSON file to avoid escaping issues
    local json_file="/tmp/detection_2000tps_$id.json"
    generate_detection_data $id > "$json_file"

    local response=$(curl -s -X POST "$BASE_URL/api/v1/detection/log" \
        -H "X-API-Key: $API_KEY" \
        -H "X-API-Secret: $API_SECRET" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d @"$json_file" \
        -w "%{http_code}")

    # Clean up
    rm -f "$json_file"

    local http_code="${response: -3}"

    # Write result to temporary file for parallel processing
    echo "$http_code" > "/tmp/result_$id.txt"
}

# Start simulation
echo "🔄 Starting 2000 TPS simulation..."
echo ""

# Track statistics
SUCCESS_COUNT=0
ERROR_COUNT=0
START_TIME=$(date +%s)

# Process in batches of 200 for better performance (10 batches of 200 = 2000 total)
for batch in {1..10}; do
    echo "📦 Processing batch $batch/10 (200 requests each)..."

    # Process 200 requests in parallel using background processes
    for i in {1..200}; do
        id=$(((batch-1)*200 + i))
        make_api_call $id &
    done

    # Wait for all background processes to complete
    wait

    # Small delay between batches to prevent overwhelming the system
    sleep 0.1
done

# Count results from temporary files
echo "📊 Counting results..."
for i in {1..2000}; do
    if [ -f "/tmp/result_$i.txt" ]; then
        result=$(cat "/tmp/result_$i.txt")
        if [ "$result" = "202" ]; then
            SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
        else
            ERROR_COUNT=$((ERROR_COUNT + 1))
        fi
        rm -f "/tmp/result_$i.txt"
    fi
done

END_TIME=$(date +%s)
DURATION=$((END_TIME - START_TIME))

echo ""
echo "📊 Simulation Results:"
echo "====================="
echo "Total requests: $((SUCCESS_COUNT + ERROR_COUNT))"
echo "Successful: $SUCCESS_COUNT"
echo "Failed: $ERROR_COUNT"
echo "Duration: ${DURATION}s"
if [ "$DURATION" -gt 0 ]; then
    echo "Average: $(awk "BEGIN {printf \"%.2f\", ($SUCCESS_COUNT + $ERROR_COUNT) / $DURATION}") requests/second"
else
    echo "Average: N/A (Duration too short)"
fi
echo ""

echo "🔍 Checking active queue status..."
artisan tinker --execute="
\$redis = app('redis');
echo 'Active Queues in Simulation:';
echo '============================';
echo 'Detections queue: ' . \$redis->llen('queues:detections') . PHP_EOL;
echo 'SendWhatsAppJob queue: ' . \$redis->llen('queues:sendwhatsappjob') . PHP_EOL;
echo '';
echo 'Other Queues (not active in simulation):';
echo '========================================';
echo 'Default queue: ' . \$redis->llen('queues:default') . PHP_EOL;
echo 'Reports queue: ' . \$redis->llen('queues:reports') . PHP_EOL;
echo 'Notifications queue: ' . \$redis->llen('queues:notifications') . PHP_EOL;
echo 'Images queue: ' . \$redis->llen('queues:images') . PHP_EOL;
"

echo ""
echo "📈 Detection data:"
artisan tinker --execute="
echo 'Total detections: ' . App\\Models\\ReIdBranchDetection::count() . PHP_EOL;
echo 'Recent detections (last 5 minutes): ' . App\\Models\\ReIdBranchDetection::where('detection_timestamp', '>=', now()->subMinutes(5))->count() . PHP_EOL;
"

echo ""
echo "📊 Queue Processing Summary:"
echo "============================"
echo "✅ Detections queue: Processing detection data from API calls"
echo "✅ SendWhatsAppJob queue: Sending WhatsApp notifications for detections"
echo "❌ Reports queue: Disabled (not used in this simulation)"
echo "❌ Other queues: Not active in this simulation"
echo ""

echo "✅ 2000 TPS simulation completed!"
echo ""
echo "🎯 Simulation Focus:"
echo "==================="
echo "• Detection processing: ✅ Active"
echo "• WhatsApp notifications: ✅ Active"
echo "• Report generation: ❌ Disabled"
echo "• Other notifications: ❌ Disabled"
