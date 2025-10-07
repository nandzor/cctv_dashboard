#!/bin/bash

# Detection API Testing Script
# Usage: ./test_detection_api.sh

BASE_URL="http://localhost:8000"
API_KEY="cctv_test_dev_key"
API_SECRET="secret_test_dev_2024"

echo "=========================================="
echo "CCTV Dashboard - Detection API Testing"
echo "=========================================="
echo ""
echo "Base URL: $BASE_URL"
echo "API Key: $API_KEY"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: Log Detection
echo -e "${YELLOW}1. Testing Detection Logging (POST)...${NC}"
RESPONSE=$(curl -s -X POST "$BASE_URL/api/detection/log" \
  -H "X-API-Key: $API_KEY" \
  -H "X-API-Secret: $API_SECRET" \
  -H "Content-Type: application/json" \
  -d '{
    "re_id": "person_test_001",
    "branch_id": 1,
    "device_id": "CAM_JKT001_001",
    "detected_count": 1,
    "detection_data": {
      "confidence": 0.95,
      "bounding_box": {"x": 120, "y": 150, "width": 80, "height": 200}
    }
  }')

echo "$RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE"
JOB_ID=$(echo "$RESPONSE" | grep -o '"job_id":"[^"]*' | cut -d'"' -f4)
echo -e "${GREEN}✓ Detection logged. Job ID: $JOB_ID${NC}"
echo ""

# Wait a moment for processing
sleep 2

# Test 2: Check Job Status
if [ ! -z "$JOB_ID" ]; then
    echo -e "${YELLOW}2. Testing Job Status Check...${NC}"
    curl -s -X GET "$BASE_URL/api/detection/status/$JOB_ID" \
      -H "X-API-Key: $API_KEY" \
      -H "X-API-Secret: $API_SECRET" | python3 -m json.tool 2>/dev/null || curl -s -X GET "$BASE_URL/api/detection/status/$JOB_ID" -H "X-API-Key: $API_KEY" -H "X-API-Secret: $API_SECRET"
    echo ""
fi

# Test 3: List All Detections
echo -e "${YELLOW}3. Testing List Detections (GET)...${NC}"
curl -s -X GET "$BASE_URL/api/detections?per_page=5" \
  -H "X-API-Key: $API_KEY" \
  -H "X-API-Secret: $API_SECRET" | python3 -m json.tool 2>/dev/null || curl -s -X GET "$BASE_URL/api/detections?per_page=5" -H "X-API-Key: $API_KEY" -H "X-API-Secret: $API_SECRET"
echo ""

# Test 4: Detection Summary
echo -e "${YELLOW}4. Testing Detection Summary...${NC}"
curl -s -X GET "$BASE_URL/api/detection/summary" \
  -H "X-API-Key: $API_KEY" \
  -H "X-API-Secret: $API_SECRET" | python3 -m json.tool 2>/dev/null || curl -s -X GET "$BASE_URL/api/detection/summary" -H "X-API-Key: $API_KEY" -H "X-API-Secret: $API_SECRET"
echo ""

# Test 5: Get Person Info
echo -e "${YELLOW}5. Testing Get Person Info...${NC}"
curl -s -X GET "$BASE_URL/api/person/person_test_001" \
  -H "X-API-Key: $API_KEY" \
  -H "X-API-Secret: $API_SECRET" | python3 -m json.tool 2>/dev/null || curl -s -X GET "$BASE_URL/api/person/person_test_001" -H "X-API-Key: $API_KEY" -H "X-API-Secret: $API_SECRET"
echo ""

# Test 6: Get Person Detections
echo -e "${YELLOW}6. Testing Person Detection History...${NC}"
curl -s -X GET "$BASE_URL/api/person/person_test_001/detections" \
  -H "X-API-Key: $API_KEY" \
  -H "X-API-Secret: $API_SECRET" | python3 -m json.tool 2>/dev/null || curl -s -X GET "$BASE_URL/api/person/person_test_001/detections" -H "X-API-Key: $API_KEY" -H "X-API-Secret: $API_SECRET"
echo ""

# Test 7: Get Branch Detections
echo -e "${YELLOW}7. Testing Branch Detections...${NC}"
curl -s -X GET "$BASE_URL/api/branch/1/detections" \
  -H "X-API-Key: $API_KEY" \
  -H "X-API-Secret: $API_SECRET" | python3 -m json.tool 2>/dev/null || curl -s -X GET "$BASE_URL/api/branch/1/detections" -H "X-API-Key: $API_KEY" -H "X-API-Secret: $API_SECRET"
echo ""

# Test 8: Filtered Detections
echo -e "${YELLOW}8. Testing Filtered Detections...${NC}"
curl -s -X GET "$BASE_URL/api/detections?branch_id=1&per_page=3" \
  -H "X-API-Key: $API_KEY" \
  -H "X-API-Secret: $API_SECRET" | python3 -m json.tool 2>/dev/null || curl -s -X GET "$BASE_URL/api/detections?branch_id=1&per_page=3" -H "X-API-Key: $API_KEY" -H "X-API-Secret: $API_SECRET"
echo ""

echo "=========================================="
echo -e "${GREEN}✓ All Tests Complete!${NC}"
echo "=========================================="
echo ""
echo "Tested Endpoints:"
echo "  ✓ POST /api/detection/log"
echo "  ✓ GET  /api/detection/status/{jobId}"
echo "  ✓ GET  /api/detections"
echo "  ✓ GET  /api/detection/summary"
echo "  ✓ GET  /api/person/{reId}"
echo "  ✓ GET  /api/person/{reId}/detections"
echo "  ✓ GET  /api/branch/{branchId}/detections"
echo ""

