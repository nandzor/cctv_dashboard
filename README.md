# 🎥 CCTV Dashboard - Complete Person Re-ID Tracking System

**A comprehensive Laravel application for CCTV monitoring with Person Re-Identification (Re-ID) tracking, multi-branch management, and real-time event notifications.**

---

## ✨ Overview

CCTV Dashboard adalah sistem monitoring lengkap dengan fitur:

- 🎯 **Person Re-Identification (Re-ID)** - Track individuals across multiple branches
- 📹 **Multi-Device Support** - Camera, Node AI, Mikrotik, CCTV devices
- 🏢 **Multi-tenant Architecture** - Province → City → Branch hierarchy
- 📊 **Real-time Analytics** - Detection trends, branch performance
- 🔔 **WhatsApp Notifications** - Async notification delivery
- 🎛️ **Flexible CCTV Layouts** - 4/6/8-window grid configurations
- 📡 **RESTful API** - Complete API for external integrations
- 🔐 **Role-based Access Control** - Admin and operator roles

---

## 🚀 Quick Start

### **Option 1: Quick Setup (5 minutes)**

```bash
# 1. Install & Configure
composer install
cp .env.example .env
php artisan key:generate

# 2. Setup Database (edit .env first)
php artisan migrate:fresh --seed

# 3. Build & Run
npm install && npm run build
php artisan serve

# 4. Login
# URL: http://localhost:8000/login
# Email: admin@cctv.com
# Password: admin123
```

### **Option 2: Using Start Scripts**

```bash
# Linux/Mac
./START.sh

# Windows
START.bat
```

📖 **Complete Guide:** See [SETUP_GUIDE.md](SETUP_GUIDE.md)

---

## 🎯 Key Features

### **Core Modules (100% Complete)**

- ✅ **Dashboard** - Overview statistics & analytics
- ✅ **Company Groups** - Province-level management (Admin only)
- ✅ **Company Branches** - City-level branch management
- ✅ **Device Masters** - CCTV devices & sensors management
- ✅ **Person Tracking (Re-ID)** - Person re-identification across branches
- ✅ **CCTV Layouts** - Dynamic 4/6/8-window grid layouts (Admin only)
- ✅ **Event Logs** - Real-time event monitoring
- ✅ **Reports** - Daily & monthly analytics with charts
- ✅ **User Management** - Role-based user administration

### **Advanced Features**

- ✅ **Async Processing** - Queue-based background jobs
- ✅ **WhatsApp Integration** - Automated notifications
- ✅ **Image Processing** - Auto-resize, watermark, thumbnails
- ✅ **API Integration** - Complete RESTful API (7 detection endpoints)
- ✅ **Performance Monitoring** - Query count, memory, execution time
- ✅ **File Storage** - Centralized storage with registry
- ✅ **Search & Filter** - All list views with pagination
- ✅ **Export Functionality** - CSV export, print layouts
- ✅ **Charts & Visualization** - Trend analysis & statistics

## 📊 Project Statistics

| Metric                  | Count | Status  |
| ----------------------- | ----- | ------- |
| **Blade Views**         | 56    | ✅ 100% |
| **Components**          | 24    | ✅ 100% |
| **Controllers**         | 11    | ✅ 100% |
| **Models**              | 17    | ✅ 100% |
| **Services**            | 7     | ✅ 100% |
| **Queue Jobs**          | 7     | ✅ 100% |
| **API Endpoints**       | 20+   | ✅ 100% |
| **Database Tables**     | 17    | ✅ 100% |
| **Seeders**             | 6     | ✅ 100% |
| **Documentation Files** | 20+   | ✅ 100% |

---

## 🏗️ Architecture (MVCS Pattern)

```
app/
├── Models/ (17)              # Eloquent models
│   ├── CompanyGroup, CompanyBranch
│   ├── DeviceMaster, ReIdMaster
│   ├── ReIdBranchDetection, EventLog
│   ├── BranchEventSetting, ApiCredential
│   ├── CctvLayoutSetting, CctvPositionSetting
│   └── + 7 more...
│
├── Http/Controllers/
│   ├── Web/ (7)              # Web controllers
│   │   ├── CompanyGroupController
│   │   ├── CompanyBranchController
│   │   ├── DeviceMasterController
│   │   ├── ReIdMasterController
│   │   ├── CctvLayoutController
│   │   ├── EventLogController
│   │   └── ReportController
│   │
│   └── Api/ (4)              # API controllers
│       ├── AuthController
│       ├── UserController
│       └── DetectionController (7 endpoints)
│
├── Services/ (7)             # Business logic layer
│   ├── CompanyGroupService
│   ├── CompanyBranchService
│   ├── DeviceMasterService
│   ├── ReIdMasterService
│   ├── CctvLayoutService
│   ├── LoggingService
│   └── BaseService
│
├── Jobs/ (7)                 # Queue jobs
│   ├── ProcessDetectionJob
│   ├── SendWhatsAppNotificationJob
│   ├── ProcessDetectionImageJob
│   ├── UpdateDailyReportJob
│   └── + 3 more...
│
└── Helpers/ (5)              # Helper functions
    ├── ApiResponseHelper
    ├── StorageHelper
    ├── EncryptionHelper
    ├── WhatsAppHelper
    └── helpers.php

resources/views/ (56 blade files)
├── auth/ (2)
├── dashboard/ (1)
├── company-groups/ (4)
├── company-branches/ (4)
├── device-masters/ (4)
├── re-id-masters/ (2)
├── cctv-layouts/ (4)
├── event-logs/ (2)
├── reports/ (3)
├── users/ (4)
├── layouts/ (2)
└── components/ (24)
```

## 💻 System Requirements

- **PHP:** 8.2 or higher
- **Database:** PostgreSQL 15 or higher
- **Composer:** Latest version
- **Node.js:** 18 or higher
- **NPM:** Latest version
- **Extensions:** pdo_pgsql, mbstring, openssl, curl, gd, fileinfo

### **Recommended:**

- **Supervisor:** For queue workers
- **Redis:** For caching (optional)
- **Nginx/Apache:** Web server
- **SSL Certificate:** For HTTPS

## Quick Start (Assets Already Built! ✅)

Assets sudah di-compile dan siap digunakan. **Anda tidak perlu menjalankan `npm run dev`** untuk menjalankan aplikasi.

---

## 🔐 Default Credentials (After Seeding)

### **Admin Account:**

```
Email: admin@cctv.com
Password: admin123
Role: Admin (Full Access)
```

### **Operator Account:**

```
Email: operator.jakarta@cctv.com
Password: password
Role: User (Limited Access)
```

**⚠️ Change these passwords in production!**

---

## 📡 API Usage

### **Authentication:**

```http
X-API-Key: your_api_key
X-API-Secret: your_api_secret
Content-Type: application/json
```

### **Detection Logging:**

```bash
curl -X POST "http://localhost:8000/api/detection/log" \
  -H "X-API-Key: your_key" \
  -H "X-API-Secret: your_secret" \
  -H "Content-Type: application/json" \
  -d '{
    "re_id": "person_001",
    "branch_id": 1,
    "device_id": "CAM_JKT001_001",
    "detected_count": 1
  }'
```

### **Get Detection Summary:**

```bash
curl "http://localhost:8000/api/detection/summary" \
  -H "X-API-Key: your_key" \
  -H "X-API-Secret: your_secret"
```

📖 **Complete API Docs:** See [API_DETECTION_DOCUMENTATION.md](API_DETECTION_DOCUMENTATION.md)

---

## 📚 Documentation

### **Main Guides:**

- 📖 **[SETUP_GUIDE.md](SETUP_GUIDE.md)** - Complete installation guide
- 📖 **[API_DETECTION_DOCUMENTATION.md](API_DETECTION_DOCUMENTATION.md)** - API reference
- 📖 **[API_QUICK_REFERENCE.md](API_QUICK_REFERENCE.md)** - API quick reference
- 📖 **[SEEDER_GUIDE.md](SEEDER_GUIDE.md)** - Database seeding guide
- 📖 **[DATABASE_PLAN_EN.md](DATABASE_PLAN_EN.md)** - Database design
- 📖 **[APPLICATION_PLAN.md](APPLICATION_PLAN.md)** - Architecture overview
- 📖 **[BLADE_VIEWS_IMPLEMENTATION_GUIDE.md](BLADE_VIEWS_IMPLEMENTATION_GUIDE.md)** - Frontend patterns
- 📖 **[COMPREHENSIVE_SUMMARY.md](COMPREHENSIVE_SUMMARY.md)** - Project overview

### **Technical Docs:**

- 🔧 **[MIDDLEWARE_MIGRATION_SUMMARY.md](MIDDLEWARE_MIGRATION_SUMMARY.md)** - Middleware patterns
- 🔧 **[FRONTEND_COMPLETION_SUMMARY.md](FRONTEND_COMPLETION_SUMMARY.md)** - Frontend details
- 🔧 **[BACKEND_COMPLETION_SUMMARY.md](BACKEND_COMPLETION_SUMMARY.md)** - Backend details

---

## Installation

### 1. Install Composer Dependencies

```bash
composer install
```

**Note:** npm packages sudah di-build, tidak perlu `npm install` kecuali Anda ingin development/modifikasi CSS/JS.

### 2. Configure Database

Edit `.env` file and configure PostgreSQL connection:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cctv_dashboard
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Create the database:

```bash
createdb cctv_dashboard
# or via psql
psql -U postgres -c "CREATE DATABASE cctv_dashboard;"
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Run Migrations & Seeders

```bash
php artisan migrate --seed
```

This will create:

- Admin user: `admin@example.com` / `password`
- Regular user: `user@example.com` / `password`

### 5. Start Server (Assets Already Built!)

Assets production sudah tersedia di `public/build/`. Langsung jalankan:

```bash
php artisan serve
```

**Optional - Hanya untuk Development CSS/JS:**
Jika ingin modifikasi styling/JavaScript:

```bash
npm install  # sekali saja
npm run dev  # untuk hot reload
```

### 6. Access Application

Visit: http://localhost:8000

**Default Login:**

- Admin: admin@example.com / password
- User: user@example.com / password

## Routes

### Web Routes (routes/web.php)

- `GET /login` - Login page
- `POST /login` - Login action
- `GET /register` - Register page
- `POST /register` - Register action
- `POST /logout` - Logout action
- `GET /dashboard` - Dashboard (authenticated)
- Resource routes for `/users` - Full CRUD operations

### API Routes

Aplikasi memiliki **2 jenis API authentication**:

#### 1. Sanctum API (`/api/*`) - CRUD Users

**Public:**

- `POST /api/register` - Register & get token
- `POST /api/login` - Login & get token

**Protected (requires Sanctum Bearer token):**

- `POST /api/logout` - Logout
- `GET /api/me` - Get authenticated user
- `GET /api/users` - List users
- `POST /api/users` - Create user
- `GET /api/users/{id}` - Show user
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user

#### 2. Static Token API (`/api/static/*`) - Testing

**Public:**

- `GET /api/static/info` - API info

**Protected (requires Static Bearer token):**

- `GET /api/static/validate` - Validate token
- `GET /api/static/test` - Main test endpoint
- `GET /api/static/test/ping` - Ping test
- `POST /api/static/test/echo` - Echo test
- `GET /api/static/test/{id}` - Get test by ID
- `POST /api/static/test` - Create test data

## API Authentication

### A. Sanctum Authentication (Dynamic Token)

**1. Login and get token:**

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'
```

Response:

```json
{
  "success": true,
  "data": {
    "user": {...},
    "token": "1|xxxxxxxxxxxxx"
  }
}
```

**2. Use token for authenticated requests:**

```bash
curl -X GET http://localhost:8000/api/users \
  -H "Authorization: Bearer 1|xxxxxxxxxxxxx"
```

### B. Static Token Authentication

**1. Set token in `.env`:**

```env
API_STATIC_TOKEN=your-secret-static-token-here
```

**2. Use static token:**

```bash
curl -X GET http://localhost:8000/api/static/test \
  -H "Authorization: Bearer your-secret-static-token-here"
```

**3. Run automated tests:**

```bash
./test_api.sh
```

📖 **Lihat [API_USAGE.txt](API_USAGE.txt) untuk dokumentasi lengkap!**

## User Roles

- **Admin**: Full access to all features
- **User**: Standard user access

## Development Commands

```bash
# Run development server
php artisan serve

# Watch and compile assets
npm run dev

# Build for production
npm run build

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh migration with seed
php artisan migrate:fresh --seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Architecture & Best Practices

### MVCS Pattern

1. **Models** (`app/Models/`)

   - Data structure and database relationships
   - Business rules related to data

2. **Views** (`resources/views/`)

   - Blade templates for UI
   - Presentation logic only

3. **Controllers** (`app/Http/Controllers/`)

   - Handle HTTP requests
   - Validate input
   - Delegate business logic to Services
   - Return responses

4. **Services** (`app/Services/`)
   - Business logic layer
   - Reusable operations
   - Data manipulation
   - Keep controllers thin

### Code Quality

- **DRY Principle**: Service layer prevents code duplication
- **Single Responsibility**: Each class has one clear purpose
- **Dependency Injection**: Services injected into controllers
- **Type Hints**: Full PHP type declarations
- **Clean Code**: Descriptive names, proper formatting

## Security Features

- CSRF protection on all forms
- Password hashing with bcrypt
- Sanctum token authentication for API
- Session-based authentication for web
- SQL injection protection via Eloquent ORM
- XSS protection in Blade templates

## UI Components

- Modern, responsive design with Tailwind CSS v4.1
- Sidebar navigation
- Data tables with pagination
- Form validation with error messages
- Success/error notifications
- Modal-ready architecture

## License

This project is open-sourced software.
