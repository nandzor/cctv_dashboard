# API Credentials Integration Documentation

## 🔐 Overview

API Credentials are now fully integrated with the API middleware for secure authentication and rate limiting.

**Current Status:** ✅ **Production Ready**

### 📋 Summary of Implementation

**Simplified Design:**

- ✅ All credentials have **global access** (all branches & devices)
- ✅ Full permissions by default (read, write, delete)
- ✅ High rate limit: 10,000 requests/hour
- ✅ Simple creation: Only 3 fields (name, expiry, status)
- ✅ One-time secret display for security
- ✅ Built-in test interface for API validation

**Security & Performance:**

- ✅ Timing-safe secret comparison (`hash_equals`)
- ✅ Request logging for failed attempts (with IP tracking)
- ✅ Credential caching (5 minutes) reduces DB load
- ✅ Rate limiting with automatic hourly reset
- ✅ Async `last_used_at` updates (non-blocking)
- ✅ Rate limit headers in all responses

**Integration Points:**

- ✅ Middleware: `ApiKeyAuth` registered as `api.key`
- ✅ Routes: Applied to all `/api/detection/*` endpoints
- ✅ Admin: Middleware in routes file, not controller
- ✅ Web Interface: `/api-credentials` for management
- ✅ Test Interface: `/api-credentials/{id}/test` for testing

---

## ✅ Features Implemented

### 1. **Middleware Integration**

- ✅ `ApiKeyAuth` middleware for API authentication
- ✅ Registered as `api.key` alias in `bootstrap/app.php`
- ✅ Applied to all `/api/detection/*` routes

### 2. **Security Features**

#### **Timing-Safe Secret Comparison**

```php
// Uses hash_equals() to prevent timing attacks
if (!hash_equals($credential->api_secret, $apiSecret)) {
    // Invalid credentials
}
```

#### **Request Logging**

```php
// Logs failed authentication attempts with IP
Log::warning('Invalid API key attempt', [
    'api_key' => $apiKey,
    'ip' => $request->ip()
]);
```

### 3. **Rate Limiting**

#### **Per-Credential Rate Limiting**

- Each credential has `rate_limit` (default: 10,000 requests/hour)
- Tracks requests using Redis/Cache
- Returns HTTP 429 when limit exceeded

#### **Rate Limit Response**

```json
{
  "success": false,
  "message": "Rate limit exceeded. Try again later.",
  "error_code": "RATE_LIMIT_EXCEEDED",
  "data": {
    "limit": 10000,
    "period": "hour",
    "reset_at": "2025-10-08T15:00:00+00:00"
  }
}
```

#### **Rate Limit Headers**

```http
X-RateLimit-Limit: 10000
X-RateLimit-Remaining: 9847
X-RateLimit-Reset: 1728399600
```

### 4. **Performance Optimizations**

#### **Credential Caching**

```php
// Cache credentials for 5 minutes to reduce DB queries
$credential = Cache::remember("api_credential:{$apiKey}", 300, function () use ($apiKey) {
    return ApiCredential::where('api_key', $apiKey)->first();
});
```

#### **Async Last Used Update**

```php
// Update last_used_at after response to not slow down request
dispatch(function () use ($credential) {
    $credential->update(['last_used_at' => now()]);
})->afterResponse();
```

### 5. **Validation Checks**

The middleware validates:

1. ✅ API Key and Secret are provided
2. ✅ Credential exists and is active
3. ✅ Secret matches (timing-safe)
4. ✅ Credential is not expired
5. ✅ Rate limit not exceeded

---

## 📡 API Usage

### **Authentication Headers**

All API requests to protected endpoints must include:

```http
X-API-Key: cctv_live_abc123xyz789def456ghi789jkl012
X-API-Secret: secret_mno345pqr678stu901vwx234yz567ab
Accept: application/json
```

### **Example Request (cURL)**

```bash
curl -X GET "https://your-domain.com/api/detections" \
  -H "X-API-Key: cctv_live_abc123xyz789def456ghi789jkl012" \
  -H "X-API-Secret: secret_mno345pqr678stu901vwx234yz567ab" \
  -H "Accept: application/json"
```

### **Example Request (JavaScript)**

```javascript
const response = await fetch("https://your-domain.com/api/detections", {
  method: "GET",
  headers: {
    "X-API-Key": "cctv_live_abc123xyz789def456ghi789jkl012",
    "X-API-Secret": "secret_mno345pqr678stu901vwx234yz567ab",
    Accept: "application/json",
  },
});

const data = await response.json();
console.log("Rate Limit:", response.headers.get("X-RateLimit-Remaining"));
```

### **Example Request (PHP/Laravel)**

```php
use Illuminate\Support\Facades\Http;

$response = Http::withHeaders([
    'X-API-Key' => 'cctv_live_abc123xyz789def456ghi789jkl012',
    'X-API-Secret' => 'secret_mno345pqr678stu901vwx234yz567ab',
])->get('https://your-domain.com/api/detections');

$data = $response->json();
```

---

## 🛣️ Protected Routes

The following routes are protected by `api.key` middleware:

| Method | Endpoint                            | Description              |
| ------ | ----------------------------------- | ------------------------ |
| POST   | `/api/detection/log`                | Log new detection        |
| GET    | `/api/detection/status/{jobId}`     | Check detection status   |
| GET    | `/api/detections`                   | List all detections      |
| GET    | `/api/detection/summary`            | Detection summary        |
| GET    | `/api/person/{reId}`                | Get person details       |
| GET    | `/api/person/{reId}/detections`     | Person detection history |
| GET    | `/api/branch/{branchId}/detections` | Branch detections        |

---

## 🧪 Testing API Credentials

### **Web Interface**

1. Go to `/api-credentials`
2. Click on a credential
3. Click **"Test API"** button
4. Enter your API secret
5. Select endpoint
6. Click **"Send Test Request"**

The test page shows:

- ✅ Response status and time
- ✅ Rate limit headers
- ✅ Response body (formatted JSON)
- ✅ cURL example for command line

### **Command Line Testing**

```bash
# Test detections endpoint
curl -X GET "http://localhost:8000/api/detections" \
  -H "X-API-Key: YOUR_API_KEY" \
  -H "X-API-Secret: YOUR_API_SECRET" \
  -H "Accept: application/json"

# Check rate limit headers
curl -I "http://localhost:8000/api/detections" \
  -H "X-API-Key: YOUR_API_KEY" \
  -H "X-API-Secret: YOUR_API_SECRET"
```

---

## 🔒 Security Best Practices

### **For Administrators**

1. ✅ **Keep secrets confidential** - Never share or commit API secrets
2. ✅ **Set expiration dates** - Use temporary credentials when possible
3. ✅ **Monitor usage** - Check `last_used_at` regularly
4. ✅ **Rotate secrets** - Use "Regenerate Secret" for compromised credentials
5. ✅ **Review logs** - Check for suspicious authentication attempts

### **For Developers**

1. ✅ **Use environment variables** - Never hardcode API keys

   ```env
   CCTV_API_KEY=cctv_live_abc123...
   CCTV_API_SECRET=secret_mno345...
   ```

2. ✅ **Store securely** - Use secret management services

   - AWS Secrets Manager
   - HashiCorp Vault
   - Azure Key Vault

3. ✅ **Handle rate limits** - Implement exponential backoff

   ```javascript
   if (response.status === 429) {
     const resetTime = response.headers.get("X-RateLimit-Reset");
     // Wait and retry
   }
   ```

4. ✅ **Monitor rate limits** - Check remaining quota

   ```javascript
   const remaining = response.headers.get("X-RateLimit-Remaining");
   if (remaining < 100) {
     console.warn("Approaching rate limit");
   }
   ```

---

## 🎯 Response Format

### **Success Response**

```json
{
  "success": true,
  "message": "Detections retrieved successfully",
  "data": {
    "detections": [...]
  },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  }
}
```

### **Error Responses**

#### **401 - Missing Credentials**

```json
{
  "success": false,
  "message": "API key and secret are required",
  "error_code": "UNAUTHORIZED"
}
```

#### **401 - Invalid Credentials**

```json
{
  "success": false,
  "message": "Invalid API credentials",
  "error_code": "INVALID_CREDENTIALS"
}
```

#### **401 - Expired Credentials**

```json
{
  "success": false,
  "message": "API credentials expired",
  "error_code": "EXPIRED_CREDENTIALS"
}
```

#### **429 - Rate Limit Exceeded**

```json
{
  "success": false,
  "message": "Rate limit exceeded. Try again later.",
  "error_code": "RATE_LIMIT_EXCEEDED",
  "data": {
    "limit": 10000,
    "period": "hour",
    "reset_at": "2025-10-08T15:00:00+00:00"
  }
}
```

---

## 📊 Monitoring

### **Check Last Used**

```sql
SELECT
  credential_name,
  api_key,
  last_used_at,
  EXTRACT(EPOCH FROM (NOW() - last_used_at)) / 3600 as hours_since_last_use,
  status,
  expires_at
FROM api_credentials
WHERE status = 'active'
ORDER BY last_used_at DESC;
```

### **Monitor Failed Attempts**

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log | grep "Invalid API"
```

### **Rate Limit Usage**

Check Redis/Cache for keys:

```bash
# Redis CLI
redis-cli KEYS "api_rate_limit:*"
redis-cli GET "api_rate_limit:cctv_live_abc123..."

# Get remaining requests
redis-cli GET "api_rate_limit:cctv_live_abc123..."
# Output: "153" (means 153 requests used, 9847 remaining of 10000)
```

---

## 🚀 Quick Start

### **1. Create Credential**

1. Go to `/api-credentials`
2. Click **"Create New Credential"**
3. Enter name and optional expiration
4. Click **"Create"**
5. **SAVE THE SECRET** (shown only once!)

### **2. Test API**

1. Click **"Test API"** on credential details
2. Enter your saved secret
3. Select endpoint
4. Click **"Send Test Request"**
5. Verify 200 OK response

### **3. Use in Your App**

```javascript
// Save credentials securely
const API_KEY = process.env.CCTV_API_KEY;
const API_SECRET = process.env.CCTV_API_SECRET;

// Make authenticated request
const response = await fetch("/api/detections", {
  headers: {
    "X-API-Key": API_KEY,
    "X-API-Secret": API_SECRET,
  },
});
```

---

## ✅ Integration Checklist

- [x] Middleware created (`ApiKeyAuth`)
- [x] Middleware registered (`api.key` alias)
- [x] Applied to API routes
- [x] Rate limiting implemented
- [x] Security hardening (timing-safe comparison)
- [x] Performance optimization (caching)
- [x] Logging for failed attempts
- [x] Rate limit headers added
- [x] Test interface created
- [x] Documentation completed
- [x] Badge component fixed (secondary variant)
- [x] Admin middleware in routes file
- [x] Controller cleaned (no middleware)
- [x] Forms simplified (3 fields only)
- [x] Auto-defaults configured

---

## 🎉 Summary

**API Credentials are now production-ready!**

✅ **Secure** - Timing-safe secret verification, request logging  
✅ **Fast** - Credential caching, async updates  
✅ **Robust** - Rate limiting, expiration checks  
✅ **Developer-Friendly** - Clear responses, rate limit headers  
✅ **Testable** - Web-based test interface, cURL examples  
✅ **Simple** - 3 fields to create, auto-generated keys

**All credentials have:**

- 🌐 Global access (all branches & devices)
- 🔑 Full permissions (read, write, delete)
- ⚡ 10,000 requests/hour rate limit
- 🔒 Secure authentication via headers

**Ready for production deployment!** 🚀

---

**For complete route reference, see:** `API_CREDENTIALS_ROUTES.md`  
**For API documentation, see:** `API_REFERENCE.md`  
**For database schema, see:** `database_plan_en.md`
