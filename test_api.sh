#!/bin/bash

# API Testing Script untuk Static Token
# Usage: ./test_api.sh

BASE_URL="http://localhost:8000"
STATIC_TOKEN=$(grep API_STATIC_TOKEN .env | cut -d '=' -f2)

echo "=========================================="
echo "API Testing - Static Token"
echo "=========================================="
echo ""
echo "Base URL: $BASE_URL"
echo "Static Token: $STATIC_TOKEN"
echo ""

# Test 1: Public Info (No Auth)
echo "1. Testing Public Info (no auth)..."
curl -s -X GET "$BASE_URL/api/static/info" | python3 -m json.tool 2>/dev/null || curl -s -X GET "$BASE_URL/api/static/info"
echo ""
echo ""

# Test 2: Validate Token
echo "2. Testing Token Validation..."
curl -s -X GET "$BASE_URL/api/static/validate" \
  -H "Authorization: Bearer $STATIC_TOKEN" | python3 -m json.tool 2>/dev/null || curl -s -X GET "$BASE_URL/api/static/validate" -H "Authorization: Bearer $STATIC_TOKEN"
echo ""
echo ""

# Test 3: Invalid Token
echo "3. Testing Invalid Token (should fail)..."
curl -s -X GET "$BASE_URL/api/static/validate" \
  -H "Authorization: Bearer invalid-token" | python3 -m json.tool 2>/dev/null || curl -s -X GET "$BASE_URL/api/static/validate" -H "Authorization: Bearer invalid-token"
echo ""
echo ""

# Test 4: Test Endpoint
echo "4. Testing Main Test Endpoint..."
curl -s -X GET "$BASE_URL/api/static/test" \
  -H "Authorization: Bearer $STATIC_TOKEN" | python3 -m json.tool 2>/dev/null || curl -s -X GET "$BASE_URL/api/static/test" -H "Authorization: Bearer $STATIC_TOKEN"
echo ""
echo ""

# Test 5: Ping
echo "5. Testing Ping Endpoint..."
curl -s -X GET "$BASE_URL/api/static/test/ping" \
  -H "Authorization: Bearer $STATIC_TOKEN" | python3 -m json.tool 2>/dev/null || curl -s -X GET "$BASE_URL/api/static/test/ping" -H "Authorization: Bearer $STATIC_TOKEN"
echo ""
echo ""

# Test 6: Echo
echo "6. Testing Echo Endpoint..."
curl -s -X POST "$BASE_URL/api/static/test/echo" \
  -H "Authorization: Bearer $STATIC_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"message":"Hello World","timestamp":"'$(date +%s)'"}' | python3 -m json.tool 2>/dev/null || curl -s -X POST "$BASE_URL/api/static/test/echo" -H "Authorization: Bearer $STATIC_TOKEN" -H "Content-Type: application/json" -d '{"message":"Hello World"}'
echo ""
echo ""

# Test 7: Get Test Data by ID
echo "7. Testing Get Test Data by ID..."
curl -s -X GET "$BASE_URL/api/static/test/123" \
  -H "Authorization: Bearer $STATIC_TOKEN" | python3 -m json.tool 2>/dev/null || curl -s -X GET "$BASE_URL/api/static/test/123" -H "Authorization: Bearer $STATIC_TOKEN"
echo ""
echo ""

# Test 8: Create Test Data
echo "8. Testing Create Test Data..."
curl -s -X POST "$BASE_URL/api/static/test" \
  -H "Authorization: Bearer $STATIC_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Item","description":"Created from test script"}' | python3 -m json.tool 2>/dev/null || curl -s -X POST "$BASE_URL/api/static/test" -H "Authorization: Bearer $STATIC_TOKEN" -H "Content-Type: application/json" -d '{"name":"Test Item","description":"Created from test script"}'
echo ""
echo ""

echo "=========================================="
echo "Testing Complete!"
echo "=========================================="

