#!/bin/bash

# =============================================================================
# CCTV Dashboard Detection API 400K Data Simulation
# =============================================================================
# Reference: simulate-detection-2000tps.sh
# Enhanced version for large-scale testing (400,000 records)
# Based on the original 2000 TPS simulation script
# =============================================================================

echo "🚀 CCTV Dashboard Detection API 400K Data Simulation"
echo "===================================================="
echo "Simulating 400,000 detection records to /api/v1/detection/log"
echo "Reference: Based on simulate-detection-2000tps.sh"
echo "Target: 400K total records with optimized batch processing"
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

# Use provided API credentials
echo "📋 Using provided API credentials..."
API_KEY="cctv_XQXuszrVkCMIhcYp9BAQm7qbFCxuT1l8"
API_SECRET="67QZAwFXv2VEGNNptY30pqnuuvnR88mjpURjfksaeJdcipdIcMDPprstWYObJNYX"

echo "✅ Using API Key: $API_KEY"
echo ""

# Base URL
BASE_URL="http://localhost:9001"

# Configuration for 400K simulation
TOTAL_RECORDS=400000
BATCH_SIZE=2000
TOTAL_BATCHES=$((TOTAL_RECORDS / BATCH_SIZE))
PARALLEL_WORKERS=100

echo "📊 Simulation Configuration:"
echo "============================="
echo "Total Records: $TOTAL_RECORDS"
echo "Batch Size: $BATCH_SIZE"
echo "Total Batches: $TOTAL_BATCHES"
echo "Parallel Workers: $PARALLEL_WORKERS"
echo ""

# Function to generate random detection data (Enhanced from reference)
generate_detection_data() {
    local id=$1
    # Extended branches and devices for 400K simulation (reference had 4 branches, 4 devices)
    local branches=(1 2 4 6 8 10 12 14 16 18 20 22 24 26 28 30)
    local devices=("NODE_JKT001_001" "NODE_BDG001_001" "CAM_SBY001_001" "MIKROTIK_SBY001" "CAM_JKT002_001" "NODE_BDG002_001" "CAM_SBY002_001" "MIKROTIK_JKT001" "CAM_BDG001_001" "NODE_SBY001_001" "CAM_JKT003_001" "NODE_BDG003_001" "CAM_SBY003_001" "MIKROTIK_BDG001" "CAM_JKT004_001" "NODE_SBY002_001")

    local branch=${branches[$((RANDOM % ${#branches[@]}))]}
    local device=${devices[$((RANDOM % ${#devices[@]}))]}
    local re_id="REID_400K_$(printf "%09d" $id)"

    # Enhanced detection data (reference had minimal data)
    local confidence=$((RANDOM % 30 + 70))
    confidence=$(awk "BEGIN {printf \"%.2f\", $confidence / 100}")

    cat << EOF
{
    "re_id": "$re_id",
    "branch_id": $branch,
    "device_id": "$device",
    "detection_data": {
        "confidence": $confidence,
        "location": "entrance",
        "appearance_features": {
            "color": "blue",
            "style": "casual"
        }
    },
    "person_features": {
        "gender": "male",
        "age_range": "25-35",
        "clothing": ["shirt", "pants"]
    }
}
EOF
}

# Function to make API call (Based on reference with enhanced error handling)
make_api_call() {
    local id=$1

    # Create JSON file to avoid escaping issues (same approach as reference)
    local json_file="/tmp/detection_400k_$id.json"
    generate_detection_data $id > "$json_file"

    local response=$(curl -s -X POST "$BASE_URL/api/v1/detection/log" \
        -H "X-API-Key: $API_KEY" \
        -H "X-API-Secret: $API_SECRET" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d @"$json_file" \
        -w "%{http_code}" \
        --connect-timeout 15 \
        --max-time 45)

    # Clean up
    rm -f "$json_file"

    local http_code="${response: -3}"

    # Write result to temporary file for parallel processing (same pattern as reference)
    echo "$http_code" > "/tmp/result_400k_$id.txt"
}

# Function to process a batch
process_batch() {
    local batch_num=$1
    local start_id=$2
    local end_id=$3

    echo "📦 Processing batch $batch_num/$TOTAL_BATCHES (IDs: $start_id-$end_id)..."

    # Process batch in parallel using background processes
    for i in $(seq $start_id $end_id); do
        make_api_call $i &

        # Limit concurrent processes
        if [ $((i % PARALLEL_WORKERS)) -eq 0 ]; then
            wait
        fi
    done

    # Wait for remaining processes
    wait

    echo "✅ Batch $batch_num completed"
}

# Start simulation
echo "🔄 Starting 400K data simulation..."
echo "⏰ Estimated time: $((TOTAL_BATCHES * 3)) minutes"
echo ""

# Track statistics
SUCCESS_COUNT=0
ERROR_COUNT=0
START_TIME=$(date +%s)

# Process in batches
for batch in $(seq 1 $TOTAL_BATCHES); do
    start_id=$(((batch-1) * BATCH_SIZE + 1))
    end_id=$((batch * BATCH_SIZE))

    process_batch $batch $start_id $end_id

    # Progress update every 5 batches
    if [ $((batch % 5)) -eq 0 ]; then
        current_time=$(date +%s)
        elapsed=$((current_time - START_TIME))
        progress=$((batch * 100 / TOTAL_BATCHES))
        echo "📊 Progress: $progress% ($batch/$TOTAL_BATCHES batches) - Elapsed: ${elapsed}s"
    fi

    # Small delay between batches to prevent overwhelming the system
    sleep 1
done

# Count results from temporary files
echo "📊 Counting results..."
for i in $(seq 1 $TOTAL_RECORDS); do
    if [ -f "/tmp/result_400k_$i.txt" ]; then
        result=$(cat "/tmp/result_400k_$i.txt")
        if [ "$result" = "202" ]; then
            SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
        else
            ERROR_COUNT=$((ERROR_COUNT + 1))
        fi
        rm -f "/tmp/result_400k_$i.txt"
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
echo "Duration: ${DURATION}s ($(awk "BEGIN {printf \"%.2f\", $DURATION / 60}") minutes)"
if [ "$DURATION" -gt 0 ]; then
    echo "Average: $(awk "BEGIN {printf \"%.2f\", ($SUCCESS_COUNT + ERROR_COUNT) / $DURATION}") requests/second"
    echo "Throughput: $(awk "BEGIN {printf \"%.2f\", $SUCCESS_COUNT / $DURATION}") successful requests/second"
else
    echo "Average: N/A (Duration too short)"
fi
echo ""

echo "🔍 Checking active queue status..."
docker exec cctv_app_staging php artisan tinker --execute="
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
docker exec cctv_app_staging php artisan tinker --execute="
echo 'Total detections: ' . App\\Models\\ReIdBranchDetection::count() . PHP_EOL;
echo 'Recent detections (last 15 minutes): ' . App\\Models\\ReIdBranchDetection::where('detection_timestamp', '>=', now()->subMinutes(15))->count() . PHP_EOL;
echo 'Unique persons detected: ' . App\\Models\\ReIdBranchDetection::distinct('re_id')->count('re_id') . PHP_EOL;
echo 'Branches with detections: ' . App\\Models\\ReIdBranchDetection::distinct('branch_id')->count('branch_id') . PHP_EOL;
echo 'Devices with detections: ' . App\\Models\\ReIdBranchDetection::distinct('device_id')->count('device_id') . PHP_EOL;
"

echo ""
echo "📊 Queue Processing Summary:"
echo "============================"
echo "✅ Detections queue: Processing detection data from API calls"
echo "✅ SendWhatsAppJob queue: Sending WhatsApp notifications for detections"
echo "❌ Reports queue: Disabled (not used in this simulation)"
echo "❌ Other queues: Not active in this simulation"
echo ""

echo "✅ 400K data simulation completed!"
echo ""
echo "🎯 Simulation Focus:"
echo "==================="
echo "• Detection processing: ✅ Active"
echo "• WhatsApp notifications: ✅ Active"
echo "• Report generation: ❌ Disabled"
echo "• Other notifications: ❌ Disabled"
echo ""
echo "📋 Next Steps:"
echo "=============="
echo "1. Monitor queue processing: docker exec cctv_app_staging php artisan queue:monitor"
echo "2. Check failed jobs: docker exec cctv_app_staging php artisan queue:failed"
echo "3. View detection reports: http://localhost:9001/reports/dashboard"
echo "4. Monitor system performance: docker stats"
echo ""
echo "🔧 Performance Tips:"
echo "===================="
echo "• Increase queue workers if processing is slow"
echo "• Monitor database performance during processing"
echo "• Check Redis memory usage for queue storage"
echo "• Consider batch processing for large datasets"
echo ""
echo "📋 Differences from Reference (simulate-detection-2000tps.sh):"
echo "=============================================================="
echo "• Scale: 400K records vs 2K records (200x increase)"
echo "• Batch Size: 2000 vs 200 records per batch (10x increase)"
echo "• Parallel Workers: 100 vs unlimited concurrent processes"
echo "• Branches: 16 vs 4 branches (4x increase)"
echo "• Devices: 16 vs 4 device types (4x increase)"
echo "• Data: Enhanced detection_data and person_features"
echo "• Progress: Real-time progress tracking every 5 batches"
echo "• Error Handling: Enhanced timeout and connection limits"
echo "• Analytics: More comprehensive statistics and monitoring"
echo "• Timeout: Extended to 15s connect, 45s max for large scale"
