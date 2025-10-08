# 📮 Postman Collection Guide - CCTV Dashboard API

## 📋 Overview

Complete Postman collection and environments for testing CCTV Dashboard API with proper authentication, rate limiting, and automatic testing.

**Version:** 2.0.0  
**Updated:** October 8, 2025

---

## 📦 Files Included

| File                                  | Purpose                  | Variables                      |
| ------------------------------------- | ------------------------ | ------------------------------ |
| `postman_collection.json`             | API endpoints collection | 7 endpoints, 2 auth methods    |
| `postman_environment_local.json`      | Local development        | localhost:8000                 |
| `postman_environment_staging.json`    | Staging server           | staging-api.cctv-dashboard.com |
| `postman_environment_production.json` | Production server        | api.cctv-dashboard.com         |

---

## 🚀 Quick Start

### **1. Import Collection**

1. Open Postman
2. Click **Import** button
3. Select `postman_collection.json`
4. Collection "CCTV Dashboard API v2" imported ✅

### **2. Import Environment**

Choose your environment:

**For Local Development:**

1. Import `postman_environment_local.json`
2. Select "CCTV Dashboard - Local" environment
3. URL: `http://localhost:8000/api`

**For Staging:**

1. Import `postman_environment_staging.json`
2. Select "CCTV Dashboard - Staging" environment
3. URL: `https://staging-api.cctv-dashboard.com/api`

**For Production:**

1. Import `postman_environment_production.json`
2. Select "CCTV Dashboard - Production" environment
3. URL: `https://api.cctv-dashboard.com/api`

### **3. Configure API Credentials**

**Get API Credentials (Admin Only):**

1. **Login to web interface:**

   ```
   Local: http://localhost:8000/login
   Staging: https://staging.cctv-dashboard.com/login
   Production: https://cctv-dashboard.com/login
   ```

2. **Login as admin:**

   - Email: `admin@cctv.com`
   - Password: `admin123` (local) or your production password

3. **Create API Credential:**

   - Navigate to `/api-credentials`
   - Click "Create New Credential"
   - Enter name (e.g., "Postman Testing")
   - Set expiration (optional)
   - Click "Create"

4. **SAVE THE SECRET:**

   - ⚠️ **API Secret is shown only once!**
   - Copy both API Key and API Secret

5. **Configure Postman Environment:**
   - Click **Environments** (sidebar)
   - Select your environment (Local/Staging/Production)
   - Paste `api_key` value
   - Paste `api_secret` value
   - Click **Save**

### **4. Test API**

1. Select environment (Local/Staging/Production)
2. Open "Detection API" folder
3. Click "Log Detection" request
4. Click **Send**
5. Should receive **202 Accepted** response
6. Check **Test Results** tab (should all pass ✅)
7. Check **Headers** tab for rate limit info

---

## 🔑 Authentication Methods

### **Method 1: API Key Authentication (Detection API)**

**Used For:** Detection endpoints (`/api/detection/*`, `/api/person/*`, `/api/branch/*`)

**Headers Required:**

```http
X-API-Key: cctv_live_abc123xyz789def456ghi789jkl012
X-API-Secret: secret_mno345pqr678stu901vwx234yz567ab
Accept: application/json
```

**Features:**

- ✅ Global access (all branches & devices)
- ✅ Full permissions (read, write, delete)
- ✅ 10,000 requests/hour rate limit
- ✅ Rate limit headers in response

**Environment Variables:**

- `{{api_key}}` - From `/api-credentials` web interface
- `{{api_secret}}` - Shown once during creation

### **Method 2: Sanctum Token (User Management API)**

**Used For:** User endpoints (`/api/users`, `/api/me`, `/api/logout`)

**Header Required:**

```http
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Accept: application/json
```

**How to Get Token:**

1. Send POST request to `/api/login`
2. Use credentials:
   ```json
   {
     "email": "admin@cctv.com",
     "password": "admin123"
   }
   ```
3. Copy `token` from response
4. Set `{{sanctum_token}}` in environment

**Environment Variable:**

- `{{sanctum_token}}` - From login response

---

## 📡 API Endpoints

### **Detection API (7 endpoints) - API Key Auth**

| Method | Endpoint                        | Description            | Auth    |
| ------ | ------------------------------- | ---------------------- | ------- |
| POST   | `/detection/log`                | Log new detection      | API Key |
| GET    | `/detection/status/{jobId}`     | Check detection status | API Key |
| GET    | `/detections`                   | List all detections    | API Key |
| GET    | `/detection/summary`            | Detection summary      | API Key |
| GET    | `/person/{reId}`                | Get person details     | API Key |
| GET    | `/person/{reId}/detections`     | Person history         | API Key |
| GET    | `/branch/{branchId}/detections` | Branch detections      | API Key |

### **User Management API (4 endpoints) - Sanctum Auth**

| Method | Endpoint    | Description       | Auth    |
| ------ | ----------- | ----------------- | ------- |
| POST   | `/login`    | Login & get token | Public  |
| POST   | `/register` | Register user     | Public  |
| GET    | `/me`       | Get current user  | Sanctum |
| GET    | `/users`    | List users        | Sanctum |
| POST   | `/logout`   | Logout            | Sanctum |

---

## 🧪 Automatic Testing

The collection includes automatic tests for all requests:

### **Tests Included:**

1. ✅ **Status Code Check**

   - Verifies 200, 201, or 202 response

2. ✅ **Rate Limit Headers** (API Key auth)

   - Checks `X-RateLimit-Limit` exists
   - Checks `X-RateLimit-Remaining` exists
   - Logs remaining quota in console

3. ✅ **Response Structure**
   - Verifies `success` field exists
   - Validates JSON format

### **View Test Results:**

1. Send a request
2. Click **Test Results** tab (bottom)
3. See pass/fail status for each test
4. Check Console for rate limit info

### **Console Output Example:**

```
Environment: CCTV Dashboard - Local
Base URL: http://localhost:8000/api
Rate Limit Remaining: 9847/10000
```

---

## 📊 Rate Limiting

### **Rate Limit Information**

- **Limit:** 10,000 requests/hour per credential
- **Reset:** Every hour (at start of hour)
- **Tracking:** Per API credential (via Cache)

### **Response Headers:**

Every API response includes:

```http
X-RateLimit-Limit: 10000
X-RateLimit-Remaining: 9847
X-RateLimit-Reset: 1728399600
```

### **Monitor Rate Limit:**

1. Send any API Key authenticated request
2. Check **Headers** tab in response
3. Look for `X-RateLimit-Remaining`
4. Warning when below 1,000 remaining

### **Rate Limit Exceeded (429):**

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

## 🔧 Environment Variables Reference

### **Required Variables**

| Variable     | Type   | Required | Description           |
| ------------ | ------ | -------- | --------------------- |
| `base_url`   | string | ✅ Yes   | API base URL          |
| `api_key`    | secret | ✅ Yes   | API Key (40 chars)    |
| `api_secret` | secret | ✅ Yes   | API Secret (40 chars) |

### **Optional Variables**

| Variable         | Type   | Required | Description             |
| ---------------- | ------ | -------- | ----------------------- |
| `sanctum_token`  | secret | ❌ No    | For user management API |
| `admin_email`    | string | ❌ No    | Admin login email       |
| `admin_password` | secret | ❌ No    | Admin login password    |
| `test_re_id`     | string | ❌ No    | Test person Re-ID       |
| `test_branch_id` | string | ❌ No    | Test branch ID          |
| `test_device_id` | string | ❌ No    | Test device ID          |

---

## 📝 Usage Examples

### **Example 1: Log Detection**

**Request:**

```http
POST {{base_url}}/detection/log
X-API-Key: {{api_key}}
X-API-Secret: {{api_secret}}
Content-Type: application/json

{
  "re_id": "REID_20251008_0001",
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
    }
  }
}
```

**Response (202 Accepted):**

```json
{
  "success": true,
  "message": "Detection submitted successfully",
  "data": {
    "re_id": "REID_20251008_0001",
    "branch_id": 1,
    "device_id": "CAMERA_001",
    "status": "processing",
    "job_id": "uuid-here"
  }
}
```

**Response Headers:**

```http
X-RateLimit-Limit: 10000
X-RateLimit-Remaining: 9847
X-RateLimit-Reset: 1728399600
```

### **Example 2: Get Detections**

**Request:**

```http
GET {{base_url}}/detections?per_page=20
X-API-Key: {{api_key}}
X-API-Secret: {{api_secret}}
Accept: application/json
```

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Detections retrieved successfully",
  "data": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 100
  }
}
```

### **Example 3: Login (Sanctum)**

**Request:**

```http
POST {{base_url}}/login
Content-Type: application/json

{
  "email": "admin@cctv.com",
  "password": "admin123"
}
```

**Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@cctv.com",
      "role": "admin"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**Next Step:** Copy `token` value to `{{sanctum_token}}` variable

---

## 🔒 Security Best Practices

### **For Local Development**

✅ **Allowed:**

- Use default credentials (`admin123`)
- Store API keys in environment
- Commit environment file (with empty secrets)

❌ **Never:**

- Share API secrets publicly

### **For Staging**

✅ **Allowed:**

- Test with staging credentials
- Share staging API keys with team (via secure channel)

❌ **Never:**

- Use production credentials
- Commit staging secrets to git

### **For Production**

✅ **Required:**

- Use strong passwords (change from default)
- Store credentials in password manager/vault
- Rotate API credentials regularly
- Monitor rate limit usage
- Enable SSL certificate verification
- Use HTTPS only

❌ **Never:**

- Commit production credentials
- Share production API keys
- Use default passwords
- Disable SSL verification
- Share Postman environment file with secrets

---

## 🧪 Testing Workflow

### **Complete Test Flow:**

1. **Setup Environment**

   ```
   ✅ Import collection
   ✅ Import environment (local/staging/production)
   ✅ Select active environment
   ```

2. **Get API Credentials**

   ```
   ✅ Login to web interface
   ✅ Navigate to /api-credentials
   ✅ Create new credential
   ✅ Save API key & secret
   ✅ Configure Postman environment
   ```

3. **Test Detection API**

   ```
   ✅ Send "Log Detection" request
   ✅ Verify 202 Accepted response
   ✅ Check rate limit headers
   ✅ Note job_id from response
   ```

4. **Test Other Endpoints**

   ```
   ✅ List Detections
   ✅ Get Detection Summary
   ✅ Get Person Info
   ✅ Get Person History
   ✅ Get Branch Detections
   ```

5. **Monitor Rate Limits**
   ```
   ✅ Check X-RateLimit-Remaining header
   ✅ Log shows: "Rate Limit Remaining: 9847/10000"
   ✅ Warning when below 1000 (staging) or 500 (production)
   ```

---

## 📊 Response Examples

### **Success Response (200 OK)**

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data
  },
  "meta": {
    "timestamp": "2025-10-08T14:30:00Z",
    "version": "1.0",
    "request_id": "uuid-here"
  }
}
```

### **Async Response (202 Accepted)**

```json
{
  "success": true,
  "message": "Detection submitted successfully",
  "data": {
    "re_id": "REID_20251008_0001",
    "status": "processing",
    "job_id": "uuid-here"
  }
}
```

### **Error Response (401 Unauthorized)**

```json
{
  "success": false,
  "message": "Invalid API credentials",
  "error_code": "INVALID_CREDENTIALS"
}
```

### **Rate Limit Exceeded (429)**

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

## 🎯 Collection Structure

```
CCTV Dashboard API v2
├── Authentication (2 requests)
│   ├── Login (POST /api/login)
│   └── Register (POST /api/register)
│
├── Detection API (7 requests) - API Key Auth
│   ├── Log Detection (POST /detection/log)
│   ├── Check Detection Status (GET /detection/status/{jobId})
│   ├── List Detections (GET /detections)
│   ├── Get Detection Summary (GET /detection/summary)
│   ├── Get Person Info (GET /person/{reId})
│   ├── Get Person Detection History (GET /person/{reId}/detections)
│   └── Get Branch Detections (GET /branch/{branchId}/detections)
│
└── User API (3 requests) - Sanctum Auth
    ├── List Users (GET /users)
    ├── Get Current User (GET /me)
    └── Logout (POST /logout)
```

---

## ⚙️ Environment Configuration

### **Local Development**

```json
{
  "base_url": "http://localhost:8000/api",
  "web_url": "http://localhost:8000",
  "api_key": "cctv_live_abc123...",
  "api_secret": "secret_mno345...",
  "admin_email": "admin@cctv.com",
  "admin_password": "admin123",
  "environment": "local"
}
```

**Steps:**

1. Run `php artisan serve`
2. Create credential at `http://localhost:8000/api-credentials`
3. Copy API key & secret to Postman
4. Test endpoints

### **Staging**

```json
{
  "base_url": "https://staging-api.cctv-dashboard.com/api",
  "web_url": "https://staging.cctv-dashboard.com",
  "api_key": "[GET FROM STAGING WEB INTERFACE]",
  "api_secret": "[SAVE FROM CREATION]",
  "admin_email": "admin@cctv.com",
  "admin_password": "[STAGING PASSWORD]",
  "environment": "staging"
}
```

**Steps:**

1. Access staging web interface
2. Login with staging credentials
3. Create API credential
4. Configure Postman with staging values
5. Test with staging data

### **Production** ⚠️

```json
{
  "base_url": "https://api.cctv-dashboard.com/api",
  "web_url": "https://cctv-dashboard.com",
  "api_key": "[STORE IN VAULT - DO NOT COMMIT]",
  "api_secret": "[STORE IN VAULT - DO NOT COMMIT]",
  "admin_email": "admin@cctv.com",
  "admin_password": "[STORE IN VAULT - DO NOT COMMIT]",
  "environment": "production",
  "enable_ssl_verification": "true"
}
```

**Security Requirements:**

- ⚠️ **NEVER commit production secrets**
- ⚠️ Store in password manager or vault
- ⚠️ Use strong passwords (change from default)
- ⚠️ Enable SSL verification
- ⚠️ Rotate credentials regularly
- ⚠️ Monitor for suspicious activity

---

## 🔍 Troubleshooting

### **Issue: "Invalid API credentials"**

**Cause:** Wrong API key or secret

**Solutions:**

1. Verify API key matches (40 characters)
2. Verify API secret matches (40 characters)
3. Check credential status is "active"
4. Verify credential hasn't expired
5. Try creating new credential

### **Issue: "Rate limit exceeded"**

**Cause:** Exceeded 10,000 requests/hour

**Solutions:**

1. Wait until next hour (check `X-RateLimit-Reset` header)
2. Create additional API credential
3. Contact admin to increase rate limit (if needed)

### **Issue: "Sanctum token invalid"**

**Cause:** Token expired or revoked

**Solutions:**

1. Login again to get new token
2. Copy token to `{{sanctum_token}}` variable
3. Verify token format: `1|xxxxx...`

### **Issue: Headers not working**

**Cause:** Environment not selected or variables empty

**Solutions:**

1. Check environment is selected (top right)
2. Verify variables have values
3. Click **Save** after editing environment
4. Restart Postman if needed

---

## 📖 Additional Resources

### **Web Interfaces**

- **Local:** http://localhost:8000/api-credentials
- **Staging:** https://staging.cctv-dashboard.com/api-credentials
- **Production:** https://cctv-dashboard.com/api-credentials

### **Test Interface**

Test API without Postman:

- `/api-credentials/{id}/test` - Built-in web-based API testing

### **Documentation**

- **API Reference:** `docs/API_REFERENCE.md`
- **Integration Guide:** `docs/API_CREDENTIALS_INTEGRATION.md`
- **Route Reference:** `docs/API_CREDENTIALS_ROUTES.md`

### **Commands**

```bash
# Check available routes
php artisan route:list --path=api

# Monitor API logs
tail -f storage/logs/laravel.log | grep "Invalid API"

# Check rate limits (Redis)
redis-cli KEYS "api_rate_limit:*"
redis-cli GET "api_rate_limit:cctv_live_abc123..."
```

---

## ✅ Checklist

### **Before Testing:**

- [ ] Collection imported
- [ ] Environment imported (local/staging/production)
- [ ] Environment selected (top right dropdown)
- [ ] Logged in to web interface as admin
- [ ] Created API credential via web
- [ ] Saved API key to `{{api_key}}`
- [ ] Saved API secret to `{{api_secret}}`
- [ ] Environment saved

### **During Testing:**

- [ ] Verify request has proper headers
- [ ] Check response status (200/201/202)
- [ ] View Test Results tab (should pass)
- [ ] Check rate limit headers
- [ ] Monitor console for rate limit info
- [ ] Save successful responses as examples

### **After Testing:**

- [ ] Document any issues found
- [ ] Note rate limit usage
- [ ] Clear sensitive data from console
- [ ] Do NOT commit secrets to git

---

## 🎉 Summary

**Postman Collection Features:**

- ✅ 12 API endpoints (7 detection + 5 auth/user)
- ✅ 3 environments (local, staging, production)
- ✅ Automatic authentication via variables
- ✅ Built-in testing (rate limit, response structure)
- ✅ Complete documentation
- ✅ Example responses included
- ✅ Security warnings for production

**Ready to use for:**

- ✅ Local development testing
- ✅ Staging environment validation
- ✅ Production API integration
- ✅ Team collaboration (share collection, not secrets!)
- ✅ Automated testing workflows

**Next Steps:**

1. Import collection & environment
2. Create API credentials via web interface
3. Configure Postman variables
4. Start testing APIs!

---

**For complete API documentation, see:** `docs/API_REFERENCE.md`  
**For integration guide, see:** `docs/API_CREDENTIALS_INTEGRATION.md`
