# 🧪 Testing Guide - CCTV Dashboard

**Complete Testing Guide untuk Development & QA**

---

## 📋 TESTING SCRIPTS

### **1. setup.sh** - Initial Setup Script

**Purpose:** Automated first-time setup

```bash
chmod +x setup.sh
./setup.sh
```

**What it does:**

- ✅ Check system requirements (PHP, Composer, Node.js)
- ✅ Install Composer dependencies
- ✅ Install NPM dependencies
- ✅ Create .env from .env.example
- ✅ Generate APP_KEY
- ✅ Create storage directories
- ✅ Set permissions
- ✅ Run migrations (optional)
- ✅ Seed database (optional)
- ✅ Build frontend assets
- ✅ Create storage link

**Output:** Ready-to-run application

---

### **2. test_detection_api.sh** - Detection API Testing

**Purpose:** Test all Detection API endpoints

```bash
chmod +x test_detection_api.sh
./test_detection_api.sh
```

**Tests:**

1. ✅ POST /api/detection/log - Log new detection
2. ✅ GET /api/detection/status/{jobId} - Check job status
3. ✅ GET /api/detections - List all detections
4. ✅ GET /api/detection/summary - Global summary
5. ✅ GET /api/person/{reId} - Person info
6. ✅ GET /api/person/{reId}/detections - Person history
7. ✅ GET /api/branch/{branchId}/detections - Branch detections

**Prerequisites:**

- Application running (`php artisan serve`)
- Database seeded with API credentials
- Queue worker running (optional for full test)

---

### **3. deploy.sh** - Deployment Script

**Purpose:** Deploy to staging/production

```bash
chmod +x deploy.sh
./deploy.sh staging    # or production
```

**What it does:**

- ✅ Put app in maintenance mode
- ✅ Pull latest code (git)
- ✅ Install dependencies
- ✅ Clear all caches
- ✅ Run migrations
- ✅ Optimize for production
- ✅ Build assets
- ✅ Set permissions
- ✅ Restart queue workers
- ✅ Bring app back online

---

## 🔧 MANUAL TESTING

### **Web Application Testing**

#### **1. Authentication Tests**

```bash
# Test login
URL: http://localhost:8000/login
Email: admin@cctv.com
Password: admin123
Expected: Redirect to dashboard

# Test invalid credentials
Email: wrong@email.com
Password: wrong
Expected: Error message

# Test logout
Click logout button
Expected: Redirect to login
```

#### **2. Dashboard Tests**

```bash
# Access dashboard
URL: http://localhost:8000/dashboard
Expected:
- Statistics cards displayed
- Charts visible
- Recent detections table
- Recent events table
```

#### **3. Company Groups Tests (Admin Only)**

```bash
# List company groups
URL: http://localhost:8000/company-groups
Expected: 5 groups listed

# Create new group
Click "Add Group"
Fill form: Province Code, Name, etc.
Expected: Group created, redirect to list

# Edit group
Click "Edit" on any group
Modify data
Expected: Group updated successfully

# Delete group (with confirmation)
Click "Delete"
Confirm in modal
Expected: Group deactivated
```

#### **4. Company Branches Tests**

```bash
# List branches
URL: http://localhost:8000/company-branches
Expected: 7 branches listed

# Search branches
Type "Jakarta" in search
Expected: Only Jakarta branches shown

# View branch details
Click "View" on any branch
Expected: Branch info + devices + statistics

# Create branch
Click "Add Branch"
Select group, fill form
Expected: Branch created

# Edit branch
Click "Edit", modify data
Expected: Branch updated

# Delete branch
Click "Delete", confirm
Expected: Branch deactivated
```

#### **5. Device Masters Tests**

```bash
# List devices
URL: http://localhost:8000/device-masters
Expected: 9 devices listed with type badges

# Filter by device type
Select "camera" from filter
Expected: Only cameras shown

# View device details
Click "View" on any device
Expected: Device info + credentials + events

# Create device
Click "Add Device"
Select branch, type, fill URL/credentials
Expected: Device created (credentials encrypted)

# Edit device
Modify device configuration
Expected: Device updated

# Delete device
Click "Delete", confirm
Expected: Device deactivated
```

#### **6. Person Tracking (Re-ID) Tests**

```bash
# List persons
URL: http://localhost:8000/re-id-masters
Expected: Statistics cards + persons list

# Search person
Search by Re-ID or name
Expected: Filtered results

# View person details
Click "View" on any person
Expected: Person info + detection history + branch breakdown

# Filter by status
Select "active" or "inactive"
Expected: Filtered list
```

#### **7. CCTV Layouts Tests (Admin Only)**

```bash
# List layouts
URL: http://localhost:8000/cctv-layouts
Expected: 3 layouts in grid view

# View layout details
Click "View" on any layout
Expected: Layout info + positions configuration

# Create layout
Click "Create Layout"
Select type (4/6/8-window)
Configure each position
Expected: Layout created with positions

# Edit layout
Click "Edit" on layout
Modify positions, settings
Expected: Layout updated

# Set as default
Check "Set as default"
Expected: Other layouts unmarked as default
```

#### **8. Event Logs Tests**

```bash
# List events
URL: http://localhost:8000/event-logs
Expected: Events with type badges

# Filter events
Filter by type, branch, date
Expected: Filtered results

# View event details
Click "View" on any event
Expected: Event info + notification status + image (if any)
```

#### **9. Reports Tests**

```bash
# Analytics dashboard
URL: http://localhost:8000/reports/dashboard
Expected: Statistics + charts + top branches

# Daily reports
URL: http://localhost:8000/reports/daily
Select date, branch
Expected: Daily breakdown table

# Monthly reports
URL: http://localhost:8000/reports/monthly
Select month
Expected: Monthly summary + chart + branch comparison + CSV export button

# Test CSV export
Click "Export CSV" button
Expected: CSV file downloaded

# Test print
Click "Print Report" button
Expected: Print-friendly layout
```

---

## 📡 API TESTING

### **Using Postman Collection**

```bash
# Import Postman collection
File: postman_collection.json

# Set environment variables:
- base_url: http://localhost:8000/api
- api_key: cctv_test_dev_key
- api_secret: secret_test_dev_2024
- test_re_id: person_test_001
- test_branch_id: 1
- test_device_id: CAM_JKT001_001

# Run collection
Click "Run Collection"
Expected: All tests pass
```

### **Using cURL (test_detection_api.sh)**

```bash
# Run automated API tests
./test_detection_api.sh

Expected output:
✓ POST detection logged
✓ GET job status checked
✓ GET detections listed
✓ GET summary retrieved
✓ GET person info retrieved
✓ GET person history retrieved
✓ GET branch detections retrieved
```

### **Manual API Tests**

#### **Test 1: Log Detection**

```bash
curl -X POST "http://localhost:8000/api/detection/log" \
  -H "X-API-Key: cctv_test_dev_key" \
  -H "X-API-Secret: secret_test_dev_2024" \
  -H "Content-Type: application/json" \
  -d '{
    "re_id": "person_manual_test",
    "branch_id": 1,
    "device_id": "CAM_JKT001_001",
    "detected_count": 1
  }'

Expected: 202 Accepted with job_id
```

#### **Test 2: Get Summary**

```bash
curl "http://localhost:8000/api/detection/summary" \
  -H "X-API-Key: cctv_test_dev_key" \
  -H "X-API-Secret: secret_test_dev_2024"

Expected: Statistics + top branches + hourly trend
```

#### **Test 3: Unauthorized Access**

```bash
curl "http://localhost:8000/api/detections" \
  -H "X-API-Key: invalid_key" \
  -H "X-API-Secret: invalid_secret"

Expected: 401 Unauthorized
```

---

## 🔐 SECURITY TESTING

### **1. Authentication Tests**

```bash
# Test without login
Access: http://localhost:8000/dashboard
Expected: Redirect to login

# Test with invalid credentials
Login with wrong password
Expected: Error message

# Test session timeout
Wait for SESSION_LIFETIME (120 min)
Expected: Redirect to login
```

### **2. Authorization Tests**

```bash
# Login as regular user
Email: operator.jakarta@cctv.com
Password: password

# Try to access admin-only page
URL: http://localhost:8000/company-groups
Expected: 403 Forbidden

URL: http://localhost:8000/cctv-layouts
Expected: 403 Forbidden

# Login as admin
Email: admin@cctv.com
Password: admin123

# Access admin pages
Expected: All pages accessible
```

### **3. CSRF Tests**

```bash
# Try POST without CSRF token
Remove @csrf from form
Submit form
Expected: 419 Page Expired

# With valid CSRF token
Expected: Success
```

### **4. API Security Tests**

```bash
# Missing API headers
curl "http://localhost:8000/api/detections"
Expected: 401 Unauthorized

# Invalid API key
curl -H "X-API-Key: wrong" -H "X-API-Secret: wrong" \
  "http://localhost:8000/api/detections"
Expected: 401 Unauthorized

# Expired API key (if you have one)
Expected: 401 Unauthorized with expiration message
```

---

## ⚡ PERFORMANCE TESTING

### **1. Page Load Time Tests**

```bash
# Use browser DevTools
Open Network tab
Navigate to various pages
Check:
- Dashboard: < 1.5s
- List pages: < 1s
- Detail pages: < 800ms
- Forms: < 500ms
```

### **2. API Response Time Tests**

```bash
# Check response time in terminal
time curl "http://localhost:8000/api/detection/summary" \
  -H "X-API-Key: cctv_test_dev_key" \
  -H "X-API-Secret: secret_test_dev_2024"

Expected: < 500ms
```

### **3. Database Query Tests**

```bash
# Enable query logging
# In .env: DB_LOG_QUERIES=true

# Check meta in API responses
Look for:
"meta": {
  "query_count": 5,     // Should be < 15
  "memory_usage": "2.5 MB", // Should be < 10 MB
  "execution_time": "0.125s" // Should be < 0.5s
}
```

### **4. Queue Processing Tests**

```bash
# Start queue worker
php artisan queue:work --queue=detections --once

# Log detection via API
# Check processing time
Expected: < 5 seconds for complete processing
```

---

## 🎯 FUNCTIONAL TESTING CHECKLIST

### **User Management:**

- [ ] Create user
- [ ] Edit user
- [ ] Delete user
- [ ] Change role
- [ ] Search users
- [ ] Pagination works

### **Company Groups (Admin):**

- [ ] List groups
- [ ] Create group
- [ ] Edit group
- [ ] Delete group
- [ ] Search groups
- [ ] View group details

### **Company Branches:**

- [ ] List branches
- [ ] Create branch
- [ ] Edit branch
- [ ] Delete branch
- [ ] Search branches
- [ ] View branch details
- [ ] See associated devices

### **Device Masters:**

- [ ] List devices
- [ ] Create device
- [ ] Edit device (credentials encrypted)
- [ ] Delete device
- [ ] Filter by type
- [ ] Search devices
- [ ] View device details

### **Person Tracking:**

- [ ] List persons
- [ ] View person details
- [ ] See detection history
- [ ] Filter by status
- [ ] Search persons
- [ ] View branch breakdown

### **CCTV Layouts (Admin):**

- [ ] List layouts
- [ ] Create layout (4/6/8-window)
- [ ] Edit layout
- [ ] Delete layout (non-default)
- [ ] Set as default
- [ ] View layout positions

### **Event Logs:**

- [ ] List events
- [ ] Filter by type
- [ ] Filter by branch
- [ ] Filter by date
- [ ] View event details
- [ ] See notification status

### **Reports:**

- [ ] Analytics dashboard
- [ ] Daily reports
- [ ] Monthly reports
- [ ] Export CSV
- [ ] Print report
- [ ] Date filters work

### **API Endpoints:**

- [ ] POST /api/detection/log
- [ ] GET /api/detection/status/{jobId}
- [ ] GET /api/detections
- [ ] GET /api/detection/summary
- [ ] GET /api/person/{reId}
- [ ] GET /api/person/{reId}/detections
- [ ] GET /api/branch/{branchId}/detections

---

## 🐛 COMMON ISSUES TO TEST

### **1. File Upload**

```bash
# Test image upload in detection
POST /api/detection/log with image > 10MB
Expected: 422 Validation Error

# Test valid image
POST with < 10MB JPEG
Expected: 202 Accepted
```

### **2. Pagination**

```bash
# Test pagination on detections
Navigate to page 2, 3, etc.
Expected: Different results per page

# Test per_page parameter
Change items per page
Expected: Correct number of items
```

### **3. Search & Filter**

```bash
# Test search on branches
Search: "Jakarta"
Expected: Only Jakarta branches

# Test combined filters
Date range + branch + device
Expected: Correctly filtered results
```

### **4. Validation**

```bash
# Test required fields
Submit form with empty fields
Expected: Validation errors shown

# Test email format
Enter invalid email
Expected: Email validation error

# Test unique constraints
Create duplicate branch_code
Expected: Unique constraint error
```

---

## 📊 PERFORMANCE TESTING

### **Load Testing with Apache Bench**

```bash
# Install apache bench
sudo apt-get install apache2-utils

# Test dashboard
ab -n 100 -c 10 http://localhost:8000/dashboard

# Test API endpoint
ab -n 1000 -c 50 \
  -H "X-API-Key: cctv_test_dev_key" \
  -H "X-API-Secret: secret_test_dev_2024" \
  http://localhost:8000/api/detection/summary

Expected:
- Requests per second: > 100
- Time per request: < 100ms (avg)
- Failed requests: 0
```

### **Database Performance**

```bash
# Enable query logging
DB_LOG_QUERIES=true

# Run operations
# Check storage/logs/laravel.log for slow queries

# Should see queries < 100ms each
```

---

## 🔄 QUEUE TESTING

### **1. Queue Worker Tests**

```bash
# Start worker in foreground
php artisan queue:work --queue=detections --once

# Log detection via API
./test_detection_api.sh

# Check worker processed job
Expected: Job processed successfully in logs
```

### **2. Failed Jobs Tests**

```bash
# Check failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry {job_id}

# Clear failed jobs
php artisan queue:flush
```

### **3. Queue Monitoring**

```bash
# Monitor queue in real-time
php artisan queue:monitor

Expected output:
- Critical queue: 0 jobs
- Detections queue: X jobs
- Notifications queue: Y jobs
```

---

## 📝 TEST DATA

### **Seeded Data Available:**

**Users:**

- admin@cctv.com / admin123 (Admin)
- operator.jakarta@cctv.com / password (User)

**API Credentials:**

- Key: cctv_test_dev_key
- Secret: secret_test_dev_2024

**Branches:**

- JKT001 - Jakarta Central
- JKT002 - Jakarta South
- BDG001 - Bandung City
- SBY001 - Surabaya Central

**Devices:**

- CAM_JKT001_001 (Camera)
- NODE_JKT001_001 (Node AI)
- MIKROTIK_SBY001 (Mikrotik)

---

## ✅ TESTING CHECKLIST

### **Before Deployment:**

- [ ] All manual tests passed
- [ ] All API tests passed
- [ ] No linter errors
- [ ] No console errors (browser)
- [ ] Performance benchmarks met
- [ ] Security tests passed
- [ ] Queue workers functioning
- [ ] File uploads working
- [ ] Exports working (CSV)
- [ ] Print layouts correct
- [ ] Mobile responsive tested
- [ ] Cross-browser tested
- [ ] Database migrations work
- [ ] Seeders populate correctly
- [ ] Backups tested
- [ ] Error handling works
- [ ] Logs are readable
- [ ] Documentation reviewed

---

## 🆘 TROUBLESHOOTING

### **Tests Failing?**

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild assets
npm run build

# Restart server
# Ctrl+C then
php artisan serve

# Restart queue
php artisan queue:restart
```

### **Database Issues?**

```bash
# Reset database
php artisan migrate:fresh --seed

# Check connection
php artisan db:show
```

### **API Not Working?**

```bash
# Check routes
php artisan route:list --path=api

# Check API credentials
php artisan tinker
>>> \App\Models\ApiCredential::all()

# Test with correct credentials from seeders
```

---

## 📖 DOCUMENTATION

- **API Docs:** `API_DETECTION_DOCUMENTATION.md`
- **Setup Guide:** `SETUP_GUIDE.md`
- **Seeder Guide:** `SEEDER_GUIDE.md`
- **Comprehensive Summary:** `COMPREHENSIVE_SUMMARY.md`

---

**Testing Guide Version:** 1.0  
**Last Updated:** October 7, 2025

_End of Testing Guide_
