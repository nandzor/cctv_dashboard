#!/bin/bash

# Generate Multiple Reports Script
# This script generates reports for multiple days

# Auto-detect project directory (script location)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$SCRIPT_DIR"
CONTAINER_NAME="cctv_app_local"

# Helper function untuk menjalankan artisan command
artisan() {
    docker exec "$CONTAINER_NAME" php artisan "$@"
}

DAYS=${1:-7}  # Default to 7 days if no argument provided

echo "🔄 Generating reports for last $DAYS days..."
echo "📁 Project directory: $PROJECT_DIR"
echo "🐳 Container: $CONTAINER_NAME"
echo ""

# Check if container exists
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "❌ Container $CONTAINER_NAME tidak berjalan!"
    echo "   Jalankan: docker compose -f docker-compose.local.yaml up -d"
    exit 1
fi

for i in $(seq 1 $DAYS); do
    date=$(date -d "$i days ago" +%Y-%m-%d)
    echo "📅 Generating reports for: $date"

    artisan reports:generate-counting --date=$date

    if [ $? -eq 0 ]; then
        echo "✅ Reports generated for $date"
    else
        echo "❌ Failed to generate reports for $date"
    fi
done

echo "🎉 Multiple reports generation completed!"
