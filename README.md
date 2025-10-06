# CCTV Dashboard - Laravel 12 Application

A modern Laravel 12 application with PostgreSQL, Tailwind CSS v4.1, Laravel Sanctum authentication, and MVCS pattern.

## 🚀 Quick Start (Assets Already Built!)

**Assets sudah compiled! Langsung jalankan tanpa `npm run dev`:**

```bash
# Linux/Mac
./START.sh

# Windows  
START.bat
```

📖 **Lihat [QUICK_START.md](QUICK_START.md) untuk panduan super cepat!**

## Features

- ✅ Laravel 12 with PostgreSQL
- ✅ Tailwind CSS v4.1 (latest)
- ✅ Laravel Sanctum for API & Web authentication
- ✅ MVCS (Model-View-Controller-Service) architecture
- ✅ Admin dashboard with sidebar navigation
- ✅ User authentication (Login & Register)
- ✅ Complete CRUD operations for Users
- ✅ Responsive design with modern UI
- ✅ Clean code and DRY principles

## Project Structure (MVCS Pattern)

```
app/
├── Models/              # Data models (User)
├── Views/               # Blade templates (resources/views)
├── Http/Controllers/    # Request handling
│   ├── AuthController.php
│   ├── DashboardController.php
│   ├── UserController.php
│   └── Api/            # API Controllers
│       ├── AuthController.php
│       └── UserController.php
└── Services/           # Business logic
    ├── AuthService.php
    └── UserService.php
```

## Requirements

- PHP >= 8.2
- Composer
- Node.js & NPM
- PostgreSQL
- Git

## Quick Start (Assets Already Built! ✅)

Assets sudah di-compile dan siap digunakan. **Anda tidak perlu menjalankan `npm run dev`** untuk menjalankan aplikasi.

Lihat **[SETUP.md](SETUP.md)** untuk panduan setup cepat 5 menit.

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

