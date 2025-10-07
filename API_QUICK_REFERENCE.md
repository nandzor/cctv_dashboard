# 🚀 API Quick Reference Card

**Base URL:** `https://your-domain.com/api`  
**Auth:** API Key via headers

---

## 🔑 Authentication

```http
X-API-Key: your_api_key
X-API-Secret: your_api_secret
Content-Type: application/json
```

---

## 📡 Detection API Endpoints

### **Write Operations**

| Method | Endpoint         | Description       | Response |
| ------ | ---------------- | ----------------- | -------- |
| POST   | `/detection/log` | Log new detection | 202      |

### **Read Operations**

| Method | Endpoint                        | Description         | Response |
| ------ | ------------------------------- | ------------------- | -------- |
| GET    | `/detection/status/{jobId}`     | Check job status    | 200      |
| GET    | `/detections`                   | List all detections | 200      |
| GET    | `/detection/summary`            | Global summary      | 200      |
| GET    | `/person/{reId}`                | Person info         | 200      |
| GET    | `/person/{reId}/detections`     | Person history      | 200      |
| GET    | `/branch/{branchId}/detections` | Branch detections   | 200      |

---

## 📝 Quick Examples

### **1. Log Detection**

```bash
curl -X POST "https://api.com/api/detection/log" \
  -H "X-API-Key: key" \
  -H "X-API-Secret: secret" \
  -H "Content-Type: application/json" \
  -d '{
    "re_id": "person_001",
    "branch_id": 1,
    "device_id": "CAM_001",
    "detected_count": 1
  }'
```

**Response:** `202 Accepted` with `job_id`

---

### **2. Get Person Info**

```bash
curl "https://api.com/api/person/person_001" \
  -H "X-API-Key: key" \
  -H "X-API-Secret: secret"
```

**Response:** Person details + branches detected

---

### **3. Get Summary**

```bash
curl "https://api.com/api/detection/summary" \
  -H "X-API-Key: key" \
  -H "X-API-Secret: secret"
```

**Response:** Statistics + top branches + hourly trend

---

### **4. Get Branch Detections**

```bash
curl "https://api.com/api/branch/1/detections?date=2025-10-07" \
  -H "X-API-Key: key" \
  -H "X-API-Secret: secret"
```

**Response:** Detections + branch statistics

---

## 🔍 Common Query Parameters

| Parameter | Type       | Endpoints                  | Description      |
| --------- | ---------- | -------------------------- | ---------------- |
| date      | YYYY-MM-DD | person, branch, summary    | Specific date    |
| date_from | YYYY-MM-DD | detections, person/history | Start date       |
| date_to   | YYYY-MM-DD | detections, person/history | End date         |
| branch_id | integer    | detections, person/history | Filter by branch |
| device_id | string     | detections, branch         | Filter by device |
| re_id     | string     | detections                 | Filter by person |
| per_page  | integer    | All paginated endpoints    | Items per page   |

---

## ✅ Response Format

```json
{
  "success": true|false,
  "message": "Operation description",
  "data": { /* Response data */ },
  "error": { /* If failed */ },
  "pagination": { /* If paginated */ },
  "statistics": { /* If applicable */ },
  "meta": {
    "timestamp": "ISO8601",
    "version": "1.0",
    "request_id": "UUID",
    "query_count": 5,
    "memory_usage": "2.5 MB",
    "execution_time": "0.125s"
  }
}
```

---

## ❌ Common Error Codes

| HTTP | Code                | Meaning                 |
| ---- | ------------------- | ----------------------- |
| 401  | UNAUTHORIZED        | Invalid/missing API key |
| 403  | FORBIDDEN           | Access denied           |
| 404  | NOT_FOUND           | Resource not found      |
| 422  | VALIDATION_ERROR    | Invalid input           |
| 429  | RATE_LIMIT_EXCEEDED | Too many requests       |
| 500  | SERVER_ERROR        | Internal error          |

---

## 🎯 Use Case Examples

### **Scenario 1: Log Detection from AI Device**

```python
import requests

response = requests.post(
    "https://api.com/api/detection/log",
    headers={
        "X-API-Key": "key",
        "X-API-Secret": "secret"
    },
    json={
        "re_id": "person_12345",
        "branch_id": 1,
        "device_id": "NODE_AI_001",
        "detected_count": 1,
        "detection_data": {
            "confidence": 0.95
        }
    }
)

job_id = response.json()['data']['job_id']
```

### **Scenario 2: Dashboard Real-time Stats**

```javascript
// Get today's summary
const summary = await fetch("/api/detection/summary", {
  headers: { "X-API-Key": key, "X-API-Secret": secret },
});

const data = await summary.json();
console.log("Total detections:", data.data.summary.total_detections);
console.log("Unique persons:", data.data.summary.unique_persons);
```

### **Scenario 3: Person Tracking Timeline**

```javascript
// Get person info
const person = await fetch("/api/person/person_001", {
  headers: { "X-API-Key": key, "X-API-Secret": secret },
});

// Get detection history
const history = await fetch(
  "/api/person/person_001/detections?date_from=2025-10-01",
  {
    headers: { "X-API-Key": key, "X-API-Secret": secret },
  }
);
```

### **Scenario 4: Branch Monitoring**

```bash
# Get today's detections for branch 1
curl "https://api.com/api/branch/1/detections" \
  -H "X-API-Key: key" \
  -H "X-API-Secret: secret"

# Response includes:
# - Detections list
# - Branch statistics (total, unique persons, devices)
```

---

## 📊 Performance Metrics

All responses include performance metrics:

```json
"meta": {
  "query_count": 5,        // Database queries executed
  "memory_usage": "2.5 MB", // Memory consumed
  "execution_time": "0.125s" // Total processing time
}
```

**Use these to monitor API health!**

---

## 🔗 Full Documentation

📖 **Complete API Docs:** `API_DETECTION_DOCUMENTATION.md`

---

**Quick Ref Version:** 1.0  
**Last Updated:** October 7, 2025
