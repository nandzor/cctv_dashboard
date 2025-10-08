# 📡 Detection API Documentation

**Base URL:** `https://your-domain.com/api`  
**Authentication:** API Key (via `X-API-Key` and `X-API-Secret` headers)  
**Content-Type:** `application/json`

---

## 📋 Table of Contents

1. [Authentication](#authentication)
2. [POST /detection/log](#1-post-detectionlog) - Log new detection
3. [GET /detection/status/{jobId}](#2-get-detectionstatusjobid) - Check processing status
4. [GET /detections](#3-get-detections) - List all detections
5. [GET /detection/summary](#4-get-detectionsummary) - Get global summary
6. [GET /person/{reId}](#5-get-personreid) - Get person info
7. [GET /person/{reId}/detections](#6-get-personreiddetections) - Get person detection history
8. [GET /branch/{branchId}/detections](#7-get-branchbranchiddetections) - Get branch detections
9. [Response Format](#response-format)
10. [Error Codes](#error-codes)

---

## 🔐 Authentication

All API requests require authentication via API Key.

### Headers Required:

```http
X-API-Key: your_api_key_here
X-API-Secret: your_api_secret_here
Content-Type: application/json
```

### Example:

```bash
curl -X GET "https://your-domain.com/api/detections" \
  -H "X-API-Key: cctv_live_abc123xyz789" \
  -H "X-API-Secret: secret_abc123xyz" \
  -H "Content-Type: application/json"
```

---

## 1. POST /detection/log

Log a new person detection event (async processing).

### Endpoint

```
POST /api/detection/log
```

### Request Body

```json
{
  "re_id": "person_001_abc123",
  "branch_id": 1,
  "device_id": "CAMERA_001",
  "detected_count": 1,
  "detection_data": {
    "confidence": 0.95,
    "bounding_box": {
      "x": 120,
      "y": 150,
      "width": 80,
      "height": 200
    },
    "appearance_features": {
      "clothing_colors": ["blue", "white"],
      "height": "medium"
    }
  },
  "image": "<file upload>"
}
```

### Parameters

| Field          | Type    | Required | Description                    |
| -------------- | ------- | -------- | ------------------------------ |
| re_id          | string  | Yes      | Re-identification ID (max 100) |
| branch_id      | integer | Yes      | Branch ID (must exist)         |
| device_id      | string  | Yes      | Device ID (must exist)         |
| detected_count | integer | Yes      | Detection count (min: 1)       |
| detection_data | object  | No       | Additional detection metadata  |
| image          | file    | No       | Detection image (max 10MB)     |

### Response (202 Accepted)

```json
{
  "success": true,
  "message": "Detection event received and queued successfully",
  "data": {
    "job_id": "9a7b8c9d-1234-5678-90ab-cdef12345678",
    "status": "processing",
    "message": "Detection queued for processing",
    "re_id": "person_001_abc123",
    "branch_id": 1,
    "device_id": "CAMERA_001"
  },
  "meta": {
    "timestamp": "2025-10-07T10:30:00Z",
    "version": "1.0",
    "request_id": "req_123abc",
    "query_count": 3,
    "memory_usage": "2.5 MB",
    "execution_time": "0.125s"
  }
}
```

### cURL Example

```bash
curl -X POST "https://your-domain.com/api/detection/log" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret" \
  -H "Content-Type: application/json" \
  -d '{
    "re_id": "person_001_abc123",
    "branch_id": 1,
    "device_id": "CAMERA_001",
    "detected_count": 1,
    "detection_data": {
      "confidence": 0.95
    }
  }'
```

### With Image Upload

```bash
curl -X POST "https://your-domain.com/api/detection/log" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret" \
  -F "re_id=person_001_abc123" \
  -F "branch_id=1" \
  -F "device_id=CAMERA_001" \
  -F "detected_count=1" \
  -F "image=@/path/to/image.jpg"
```

---

## 2. GET /detection/status/{jobId}

Check the processing status of a detection job.

### Endpoint

```
GET /api/detection/status/{jobId}
```

### URL Parameters

| Parameter | Type   | Description             |
| --------- | ------ | ----------------------- |
| jobId     | string | Job UUID from POST /log |

### Response - Processing

```json
{
  "success": true,
  "message": "Job is still processing",
  "data": {
    "job_id": "9a7b8c9d-1234-5678-90ab-cdef12345678",
    "status": "processing",
    "attempts": 1
  },
  "meta": { ... }
}
```

### Response - Completed

```json
{
  "success": true,
  "message": "Job completed successfully",
  "data": {
    "job_id": "9a7b8c9d-1234-5678-90ab-cdef12345678",
    "status": "completed"
  },
  "meta": { ... }
}
```

### Response - Failed

```json
{
  "success": false,
  "message": "Job processing failed",
  "error": {
    "code": "JOB_FAILED",
    "details": {
      "error": "Exception message here"
    }
  },
  "meta": { ... }
}
```

### cURL Example

```bash
curl -X GET "https://your-domain.com/api/detection/status/9a7b8c9d-1234-5678-90ab-cdef12345678" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret"
```

---

## 3. GET /detections

Get all detections with filtering and pagination.

### Endpoint

```
GET /api/detections
```

### Query Parameters

| Parameter | Type    | Required | Description                  |
| --------- | ------- | -------- | ---------------------------- |
| date_from | date    | No       | Start date (YYYY-MM-DD)      |
| date_to   | date    | No       | End date (YYYY-MM-DD)        |
| branch_id | integer | No       | Filter by branch             |
| device_id | string  | No       | Filter by device             |
| re_id     | string  | No       | Filter by person             |
| per_page  | integer | No       | Items per page (default: 15) |

### Response

```json
{
  "success": true,
  "message": "Detections retrieved successfully",
  "data": [
    {
      "id": 1,
      "re_id": "person_001_abc123",
      "branch_id": 1,
      "device_id": "CAMERA_001",
      "detected_count": 1,
      "detection_timestamp": "2025-10-07T10:30:00Z",
      "detection_data": {
        "confidence": 0.95,
        "bounding_box": { "x": 120, "y": 150 }
      },
      "status": "active",
      "branch": {
        "id": 1,
        "branch_name": "Jakarta Central",
        "city": "Central Jakarta"
      },
      "device": {
        "device_id": "CAMERA_001",
        "device_name": "Main Entrance Camera"
      },
      "re_id_master": {
        "re_id": "person_001_abc123",
        "person_name": "John Doe",
        "detection_date": "2025-10-07"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  },
  "meta": { ... }
}
```

### cURL Examples

**All detections today:**

```bash
curl -X GET "https://your-domain.com/api/detections" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret"
```

**Filter by date range:**

```bash
curl -X GET "https://your-domain.com/api/detections?date_from=2025-10-01&date_to=2025-10-07" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret"
```

**Filter by branch:**

```bash
curl -X GET "https://your-domain.com/api/detections?branch_id=1&per_page=20" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret"
```

---

## 4. GET /detection/summary

Get global detection summary with statistics and trends.

### Endpoint

```
GET /api/detection/summary
```

### Query Parameters

| Parameter | Type | Required | Description           |
| --------- | ---- | -------- | --------------------- |
| date      | date | No       | Date (default: today) |

### Response

```json
{
  "success": true,
  "message": "Detection summary retrieved successfully",
  "data": {
    "date": "2025-10-07",
    "summary": {
      "total_detections": 1523,
      "unique_persons": 245,
      "unique_branches": 12,
      "unique_devices": 45
    },
    "top_branches": [
      {
        "branch_id": 1,
        "branch_name": "Jakarta Central",
        "city": "Central Jakarta",
        "detection_count": 456
      },
      {
        "branch_id": 2,
        "branch_name": "Jakarta South",
        "city": "South Jakarta",
        "detection_count": 389
      }
    ],
    "top_persons": [
      {
        "re_id": "person_001_abc123",
        "detection_count": 15
      },
      {
        "re_id": "person_002_def456",
        "detection_count": 12
      }
    ],
    "hourly_trend": [
      {
        "hour": 8,
        "count": 45,
        "unique_persons": 23
      },
      {
        "hour": 9,
        "count": 67,
        "unique_persons": 34
      }
    ]
  },
  "meta": { ... }
}
```

### cURL Example

```bash
curl -X GET "https://your-domain.com/api/detection/summary?date=2025-10-07" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret"
```

---

## 5. GET /person/{reId}

Get detailed information about a specific person (Re-ID).

### Endpoint

```
GET /api/person/{reId}
```

### URL Parameters

| Parameter | Type   | Description          |
| --------- | ------ | -------------------- |
| reId      | string | Re-identification ID |

### Query Parameters

| Parameter | Type | Required | Description           |
| --------- | ---- | -------- | --------------------- |
| date      | date | No       | Date (default: today) |

### Response

```json
{
  "success": true,
  "message": "Person information retrieved successfully",
  "data": {
    "re_id": "person_001_abc123",
    "detection_date": "2025-10-07",
    "detection_time": "2025-10-07T08:30:00Z",
    "person_name": "John Doe",
    "appearance_features": {
      "clothing_colors": ["blue", "white"],
      "height": "medium",
      "accessories": ["glasses", "backpack"]
    },
    "total_detection_branch_count": 3,
    "total_actual_count": 15,
    "first_detected_at": "2025-10-07T08:30:00Z",
    "last_detected_at": "2025-10-07T16:45:00Z",
    "status": "active",
    "detected_branches": [
      {
        "branch_id": 1,
        "branch_name": "Jakarta Central",
        "city": "Central Jakarta",
        "detection_count": 8
      },
      {
        "branch_id": 2,
        "branch_name": "Jakarta South",
        "city": "South Jakarta",
        "detection_count": 5
      },
      {
        "branch_id": 3,
        "branch_name": "Bandung City",
        "city": "Bandung",
        "detection_count": 2
      }
    ]
  },
  "meta": { ... }
}
```

### cURL Examples

**Get person info for today:**

```bash
curl -X GET "https://your-domain.com/api/person/person_001_abc123" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret"
```

**Get person info for specific date:**

```bash
curl -X GET "https://your-domain.com/api/person/person_001_abc123?date=2025-10-06" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret"
```

---

## 6. GET /person/{reId}/detections

Get detection history for a specific person.

### Endpoint

```
GET /api/person/{reId}/detections
```

### URL Parameters

| Parameter | Type   | Description          |
| --------- | ------ | -------------------- |
| reId      | string | Re-identification ID |

### Query Parameters

| Parameter | Type    | Required | Description              |
| --------- | ------- | -------- | ------------------------ |
| date_from | date    | No       | Start date               |
| date_to   | date    | No       | End date                 |
| branch_id | integer | No       | Filter by branch         |
| per_page  | integer | No       | Items per page (def: 20) |

### Response

```json
{
  "success": true,
  "message": "Detection history for re_id 'person_001_abc123' retrieved successfully",
  "data": [
    {
      "id": 1,
      "re_id": "person_001_abc123",
      "branch_id": 1,
      "device_id": "CAMERA_001",
      "detected_count": 1,
      "detection_timestamp": "2025-10-07T08:30:15Z",
      "detection_data": {
        "confidence": 0.95
      },
      "branch": {
        "id": 1,
        "branch_name": "Jakarta Central",
        "city": "Central Jakarta"
      },
      "device": {
        "device_id": "CAMERA_001",
        "device_name": "Main Entrance Camera"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 45,
    "last_page": 3
  },
  "meta": { ... }
}
```

### cURL Examples

**All detections for person:**

```bash
curl -X GET "https://your-domain.com/api/person/person_001_abc123/detections" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret"
```

**Filter by date range:**

```bash
curl -X GET "https://your-domain.com/api/person/person_001_abc123/detections?date_from=2025-10-01&date_to=2025-10-07" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret"
```

**Filter by branch:**

```bash
curl -X GET "https://your-domain.com/api/person/person_001_abc123/detections?branch_id=1" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret"
```

---

## 7. GET /branch/{branchId}/detections

Get all detections for a specific branch with statistics.

### Endpoint

```
GET /api/branch/{branchId}/detections
```

### URL Parameters

| Parameter | Type    | Description |
| --------- | ------- | ----------- |
| branchId  | integer | Branch ID   |

### Query Parameters

| Parameter | Type    | Required | Description              |
| --------- | ------- | -------- | ------------------------ |
| date      | date    | No       | Date (default: today)    |
| device_id | string  | No       | Filter by device         |
| per_page  | integer | No       | Items per page (def: 20) |

### Response

```json
{
  "success": true,
  "message": "Branch detections retrieved successfully",
  "data": [
    {
      "id": 1,
      "re_id": "person_001_abc123",
      "branch_id": 1,
      "device_id": "CAMERA_001",
      "detected_count": 1,
      "detection_timestamp": "2025-10-07T08:30:15Z",
      "device": {
        "device_id": "CAMERA_001",
        "device_name": "Main Entrance Camera"
      },
      "re_id_master": {
        "re_id": "person_001_abc123",
        "person_name": "John Doe"
      }
    }
  ],
  "statistics": {
    "branch_id": 1,
    "branch_name": "Jakarta Central",
    "city": "Central Jakarta",
    "date": "2025-10-07",
    "total_detections": 156,
    "unique_persons": 45,
    "unique_devices": 8
  },
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 156
  },
  "meta": { ... }
}
```

### cURL Examples

**Today's detections:**

```bash
curl -X GET "https://your-domain.com/api/branch/1/detections" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret"
```

**Specific date:**

```bash
curl -X GET "https://your-domain.com/api/branch/1/detections?date=2025-10-06" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret"
```

**Filter by device:**

```bash
curl -X GET "https://your-domain.com/api/branch/1/detections?device_id=CAMERA_001" \
  -H "X-API-Key: your_api_key" \
  -H "X-API-Secret: your_api_secret"
```

---

## 📊 Response Format

All API responses follow a standardized format:

### Success Response

```json
{
  "success": true,
  "message": "Operation description",
  "data": {
    /* Response data */
  },
  "meta": {
    "timestamp": "2025-10-07T10:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here",
    "query_count": 5,
    "memory_usage": "2.5 MB",
    "execution_time": "0.125s"
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Error description",
  "error": {
    "code": "ERROR_CODE",
    "details": {
      /* Error details */
    }
  },
  "meta": {
    "timestamp": "2025-10-07T10:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here"
  }
}
```

### Paginated Response

```json
{
  "success": true,
  "message": "Data retrieved",
  "data": [ /* Array of items */ ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7,
    "from": 1,
    "to": 15
  },
  "meta": { ... }
}
```

---

## ❌ Error Codes

### HTTP Status Codes

| Code | Name                  | Description                 |
| ---- | --------------------- | --------------------------- |
| 200  | OK                    | Successful request          |
| 201  | Created               | Resource created            |
| 202  | Accepted              | Async processing started    |
| 400  | Bad Request           | Invalid request format      |
| 401  | Unauthorized          | Invalid/missing credentials |
| 403  | Forbidden             | Insufficient permissions    |
| 404  | Not Found             | Resource not found          |
| 422  | Unprocessable Entity  | Validation failed           |
| 429  | Too Many Requests     | Rate limit exceeded         |
| 500  | Internal Server Error | Server error                |

### Application Error Codes

| Error Code          | HTTP | Description              |
| ------------------- | ---- | ------------------------ |
| VALIDATION_ERROR    | 422  | Input validation failed  |
| NOT_FOUND           | 404  | Resource not found       |
| UNAUTHORIZED        | 401  | Invalid credentials      |
| FORBIDDEN           | 403  | Access denied            |
| JOB_FAILED          | 500  | Background job failed    |
| SERVER_ERROR        | 500  | Internal error           |
| TRACKING_DISABLED   | 403  | Person tracking disabled |
| DUPLICATE_ENTRY     | 400  | Duplicate detection      |
| INVALID_CREDENTIALS | 401  | Invalid API key          |
| RATE_LIMIT_EXCEEDED | 429  | Too many requests        |

---

## 📝 Usage Examples

### Python Example

```python
import requests
import json

# Configuration
API_BASE_URL = "https://your-domain.com/api"
API_KEY = "your_api_key"
API_SECRET = "your_api_secret"

headers = {
    "X-API-Key": API_KEY,
    "X-API-Secret": API_SECRET,
    "Content-Type": "application/json"
}

# 1. Log detection
def log_detection(re_id, branch_id, device_id):
    url = f"{API_BASE_URL}/detection/log"
    payload = {
        "re_id": re_id,
        "branch_id": branch_id,
        "device_id": device_id,
        "detected_count": 1,
        "detection_data": {
            "confidence": 0.95
        }
    }

    response = requests.post(url, headers=headers, json=payload)
    return response.json()

# 2. Check job status
def check_status(job_id):
    url = f"{API_BASE_URL}/detection/status/{job_id}"
    response = requests.get(url, headers=headers)
    return response.json()

# 3. Get person info
def get_person(re_id, date=None):
    url = f"{API_BASE_URL}/person/{re_id}"
    params = {"date": date} if date else {}
    response = requests.get(url, headers=headers, params=params)
    return response.json()

# 4. Get branch detections
def get_branch_detections(branch_id, date=None):
    url = f"{API_BASE_URL}/branch/{branch_id}/detections"
    params = {"date": date} if date else {}
    response = requests.get(url, headers=headers, params=params)
    return response.json()

# 5. Get summary
def get_summary(date=None):
    url = f"{API_BASE_URL}/detection/summary"
    params = {"date": date} if date else {}
    response = requests.get(url, headers=headers, params=params)
    return response.json()

# Usage
result = log_detection("person_001_abc123", 1, "CAMERA_001")
print(f"Job ID: {result['data']['job_id']}")

# Check status
status = check_status(result['data']['job_id'])
print(f"Status: {status['data']['status']}")

# Get person info
person = get_person("person_001_abc123")
print(f"Person: {person['data']['person_name']}")
print(f"Total detections: {person['data']['total_actual_count']}")
print(f"Branches: {person['data']['total_detection_branch_count']}")
```

### JavaScript Example

```javascript
// Configuration
const API_BASE_URL = "https://your-domain.com/api";
const API_KEY = "your_api_key";
const API_SECRET = "your_api_secret";

const headers = {
  "X-API-Key": API_KEY,
  "X-API-Secret": API_SECRET,
  "Content-Type": "application/json",
};

// 1. Log detection
async function logDetection(reId, branchId, deviceId) {
  const response = await fetch(`${API_BASE_URL}/detection/log`, {
    method: "POST",
    headers: headers,
    body: JSON.stringify({
      re_id: reId,
      branch_id: branchId,
      device_id: deviceId,
      detected_count: 1,
      detection_data: {
        confidence: 0.95,
      },
    }),
  });

  return await response.json();
}

// 2. Check status
async function checkStatus(jobId) {
  const response = await fetch(`${API_BASE_URL}/detection/status/${jobId}`, {
    method: "GET",
    headers: headers,
  });

  return await response.json();
}

// 3. Get person info
async function getPerson(reId, date = null) {
  const url = new URL(`${API_BASE_URL}/person/${reId}`);
  if (date) url.searchParams.append("date", date);

  const response = await fetch(url, {
    method: "GET",
    headers: headers,
  });

  return await response.json();
}

// 4. Get detections with filters
async function getDetections(filters = {}) {
  const url = new URL(`${API_BASE_URL}/detections`);
  Object.keys(filters).forEach((key) =>
    url.searchParams.append(key, filters[key])
  );

  const response = await fetch(url, {
    method: "GET",
    headers: headers,
  });

  return await response.json();
}

// 5. Get summary
async function getSummary(date = null) {
  const url = new URL(`${API_BASE_URL}/detection/summary`);
  if (date) url.searchParams.append("date", date);

  const response = await fetch(url, {
    method: "GET",
    headers: headers,
  });

  return await response.json();
}

// Usage example
(async () => {
  // Log detection
  const result = await logDetection("person_001_abc123", 1, "CAMERA_001");
  console.log("Job ID:", result.data.job_id);

  // Wait a moment
  await new Promise((resolve) => setTimeout(resolve, 2000));

  // Check status
  const status = await checkStatus(result.data.job_id);
  console.log("Status:", status.data.status);

  // Get person info
  const person = await getPerson("person_001_abc123");
  console.log("Person:", person.data.person_name);
  console.log("Total detections:", person.data.total_actual_count);

  // Get today's summary
  const summary = await getSummary();
  console.log("Total detections today:", summary.data.summary.total_detections);
  console.log("Unique persons:", summary.data.summary.unique_persons);
})();
```

### PHP Example

```php
<?php

// Configuration
$apiBaseUrl = 'https://your-domain.com/api';
$apiKey = 'your_api_key';
$apiSecret = 'your_api_secret';

function makeApiRequest($method, $endpoint, $data = null) {
    global $apiBaseUrl, $apiKey, $apiSecret;

    $url = $apiBaseUrl . $endpoint;

    $headers = [
        'X-API-Key: ' . $apiKey,
        'X-API-Secret: ' . $apiSecret,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// 1. Log detection
$result = makeApiRequest('POST', '/detection/log', [
    're_id' => 'person_001_abc123',
    'branch_id' => 1,
    'device_id' => 'CAMERA_001',
    'detected_count' => 1,
    'detection_data' => [
        'confidence' => 0.95
    ]
]);

echo "Job ID: " . $result['data']['data']['job_id'] . "\n";

// 2. Get person info
$person = makeApiRequest('GET', '/person/person_001_abc123');
echo "Person: " . $person['data']['data']['person_name'] . "\n";
echo "Detections: " . $person['data']['data']['total_actual_count'] . "\n";

// 3. Get branch detections
$branch = makeApiRequest('GET', '/branch/1/detections');
echo "Branch: " . $branch['data']['statistics']['branch_name'] . "\n";
echo "Total: " . $branch['data']['statistics']['total_detections'] . "\n";
```

---

## 🔄 Workflow Examples

### Complete Detection Flow

```
1. Device detects person
   ↓
2. POST /api/detection/log
   ← 202 Accepted (job_id returned)
   ↓
3. [Background] Job processes detection
   ├── Create/update re_id_masters
   ├── Log detection to re_id_branch_detections
   ├── Create event_log
   └── Send WhatsApp notifications (if enabled)
   ↓
4. GET /api/detection/status/{job_id}
   ← Check if completed
   ↓
5. GET /api/person/{re_id}
   ← Get updated person info
```

### Monitoring Flow

```
Dashboard App
   ↓
1. GET /api/detection/summary
   ← Get today's statistics
   ↓
2. Display on dashboard
   ├── Total detections
   ├── Unique persons
   ├── Top branches
   └── Hourly trend chart
   ↓
3. User clicks on branch
   ↓
4. GET /api/branch/{branchId}/detections
   ← Get branch-specific data
   ↓
5. User clicks on person
   ↓
6. GET /api/person/{reId}
   ← Get person details
   ↓
7. GET /api/person/{reId}/detections
   ← Get detection history
```

---

## 🎯 Best Practices

### 1. Error Handling

Always check the `success` field in the response:

```javascript
const response = await fetch(url);
const data = await response.json();

if (!data.success) {
  console.error("API Error:", data.error.code, data.error.details);
  // Handle error
} else {
  // Process data
  console.log("Success:", data.data);
}
```

### 2. Async Detection Processing

The `/detection/log` endpoint returns `202 Accepted` immediately. The actual processing happens in the background:

```javascript
// Submit detection
const result = await logDetection(reId, branchId, deviceId);
const jobId = result.data.job_id;

// Poll for completion (optional)
const pollInterval = setInterval(async () => {
  const status = await checkStatus(jobId);

  if (status.data.status === "completed") {
    clearInterval(pollInterval);
    console.log("Detection processed successfully");
  } else if (status.data.status === "failed") {
    clearInterval(pollInterval);
    console.error("Detection processing failed");
  }
}, 2000); // Check every 2 seconds
```

### 3. Pagination

Handle pagination for large datasets:

```javascript
async function getAllDetections(filters = {}) {
  let allData = [];
  let page = 1;
  let hasMore = true;

  while (hasMore) {
    const response = await getDetections({ ...filters, page });
    allData = [...allData, ...response.data];

    hasMore = response.pagination.current_page < response.pagination.last_page;
    page++;
  }

  return allData;
}
```

### 4. Rate Limiting

Implement exponential backoff for rate limit errors:

```javascript
async function apiRequestWithRetry(requestFn, maxRetries = 3) {
  for (let i = 0; i < maxRetries; i++) {
    try {
      const response = await requestFn();

      if (response.error && response.error.code === "RATE_LIMIT_EXCEEDED") {
        const waitTime = Math.pow(2, i) * 1000; // Exponential backoff
        await new Promise((resolve) => setTimeout(resolve, waitTime));
        continue;
      }

      return response;
    } catch (error) {
      if (i === maxRetries - 1) throw error;
    }
  }
}
```

### 5. Performance Monitoring

Use the `meta` object to monitor API performance:

```javascript
const response = await fetch(url);
const data = await response.json();

console.log("Performance Metrics:");
console.log("- Query Count:", data.meta.query_count);
console.log("- Memory Usage:", data.meta.memory_usage);
console.log("- Execution Time:", data.meta.execution_time);

// Alert if slow
if (parseFloat(data.meta.execution_time) > 1.0) {
  console.warn("Slow API response detected!");
}
```

---

## 🔧 Testing

### Postman Collection Variables

```json
{
  "api_base_url": "https://your-domain.com/api",
  "api_key": "your_api_key",
  "api_secret": "your_api_secret",
  "test_re_id": "person_001_test",
  "test_branch_id": 1,
  "test_device_id": "CAMERA_001"
}
```

### Test Scenarios

1. **Successful Detection Logging**

   - POST /detection/log with valid data
   - Expect: 202 Accepted with job_id

2. **Invalid Branch ID**

   - POST /detection/log with non-existent branch_id
   - Expect: 422 Validation Error

3. **Missing API Credentials**

   - Request without X-API-Key header
   - Expect: 401 Unauthorized

4. **Get Non-existent Person**

   - GET /person/invalid_re_id
   - Expect: 404 Not Found

5. **Rate Limit Test**
   - Send 1000+ requests rapidly
   - Expect: 429 Rate Limit Exceeded

---

## 📞 Support

For API issues or questions:

- **Email:** api-support@your-domain.com
- **Documentation:** https://docs.your-domain.com/api
- **Status Page:** https://status.your-domain.com

---

**Last Updated:** October 7, 2025  
**API Version:** 1.0  
**Documentation Version:** 1.0

_End of Detection API Documentation_
