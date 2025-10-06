# CCTV Dashboard - Complete Documentation

## 📋 Table of Contents

1. [Project Overview](#project-overview)
2. [Architecture](#architecture)
3. [Backend Documentation](#backend-documentation)
4. [Frontend Documentation](#frontend-documentation)
5. [API Documentation](#api-documentation)
6. [Component Library](#component-library)
7. [Database Schema](#database-schema)
8. [Authentication & Authorization](#authentication--authorization)
9. [Best Practices](#best-practices)
10. [Deployment](#deployment)

---

## 🎯 Project Overview

**CCTV Dashboard** is a modern web application built with Laravel 12 and Tailwind CSS v4.1, featuring a comprehensive admin dashboard with user management, authentication, and API endpoints.

### Key Features

- 🔐 **Dual Authentication**: Laravel Sanctum (API) + Session (Web)
- 👥 **User Management**: Full CRUD operations with role-based access
- 🎨 **Modern UI**: Tailwind CSS with custom component library
- 📱 **Responsive Design**: Mobile-first approach
- 🔌 **RESTful APIs**: Both static token and dynamic token authentication
- 🧩 **Component-Based**: Reusable Blade components
- ⚡ **Alpine.js Integration**: Interactive frontend without heavy frameworks

### Tech Stack

- **Backend**: Laravel 12, PHP 8.2+
- **Database**: PostgreSQL
- **Frontend**: Blade Templates, Tailwind CSS v4.1, Alpine.js
- **Authentication**: Laravel Sanctum
- **Build Tool**: Vite
- **Styling**: Tailwind CSS with custom design system

---

## 🏗️ Architecture

### MVCS Pattern (Model-View-Controller-Service)

```
app/
├── Models/           # Eloquent Models
├── Views/            # Blade Templates
├── Controllers/      # Web Controllers
│   └── Api/         # API Controllers
├── Services/         # Business Logic Layer
├── Http/
│   ├── Middleware/   # Custom Middleware
│   └── Requests/     # Form Requests (if needed)
└── Helpers/          # Utility Functions
```

### Directory Structure

```
cctv_dashboard/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/              # API Controllers
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   └── UserController.php
│   ├── Http/Middleware/
│   │   └── ValidateStaticToken.php
│   ├── Models/
│   │   └── User.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   └── UserService.php
│   └── Helpers/
│       └── helpers.php
├── resources/
│   ├── views/
│   │   ├── components/       # Blade Components
│   │   ├── layouts/
│   │   ├── auth/
│   │   ├── users/
│   │   └── dashboard/
│   └── js/
│       ├── app.js
│       ├── bootstrap.js
│       └── helpers.js
├── routes/
│   ├── web.php
│   ├── api.php
│   └── api-static.php
└── database/
    ├── migrations/
    └── seeders/
```

---

## 🔧 Backend Documentation

### Models

#### User Model (`app/Models/User.php`)

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
```

### Services Layer

#### AuthService (`app/Services/AuthService.php`)

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'user',
        ]);
    }

    /**
     * Create API token for user
     */
    public function createToken(User $user): string
    {
        return $user->createToken('api-token')->plainTextToken;
    }

    /**
     * Authenticate user credentials
     */
    public function authenticate(array $credentials): ?User
    {
        $user = User::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            return $user;
        }

        return null;
    }
}
```

#### UserService (`app/Services/UserService.php`)

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    /**
     * Get paginated users
     */
    public function getPaginatedUsers(int $perPage = 10): LengthAwarePaginator
    {
        return User::paginate($perPage);
    }

    /**
     * Search users by name or email
     */
    public function searchUsers(string $search, int $perPage = 10): LengthAwarePaginator
    {
        return User::where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%")
                   ->paginate($perPage);
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        return User::create($data);
    }

    /**
     * Update user
     */
    public function updateUser(User $user, array $data): User
    {
        $user->update($data);
        return $user->fresh();
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }
}
```

### Controllers

#### Base API Controller (`app/Http/Controllers/Api/BaseController.php`)

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BaseController extends Controller
{
    /**
     * Return a standard success response
     */
    public function successResponse(mixed $data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return a standard error response
     */
    public function errorResponse(string $message = 'Error', mixed $errors = null, int $code = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a paginated response
     */
    public function paginatedResponse(LengthAwarePaginator $paginator, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ], $code);
    }
}
```

### Middleware

#### Static Token Validation (`app/Http/Middleware/ValidateStaticToken.php`)

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateStaticToken
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $staticToken = config('api.static_token');

        if (!$staticToken) {
            return response()->json(['message' => 'Static API token not configured.'], 500);
        }

        $token = $request->bearerToken();

        if (!$token || $token !== $staticToken) {
            return response()->json(['message' => 'Unauthorized: Invalid or missing static token.'], 401);
        }

        return $next($request);
    }
}
```

### Helper Functions (`app/Helpers/helpers.php`)

```php
<?php

if (!function_exists('formatDate')) {
    /**
     * Format date with custom format
     */
    function formatDate($date, $format = 'Y-m-d H:i:s')
    {
        return $date ? date($format, strtotime($date)) : null;
    }
}

if (!function_exists('generateRandomString')) {
    /**
     * Generate random string
     */
    function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }
}

if (!function_exists('isValidEmail')) {
    /**
     * Validate email format
     */
    function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
```

---

## 🎨 Frontend Documentation

### Alpine.js Integration

The frontend uses Alpine.js for interactive components without heavy JavaScript frameworks.

#### Global Alpine.js Setup

```html
<!-- In layouts/app.blade.php -->
<script
  defer
  src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"
></script>
```

#### Alpine.js Components

##### Modal Component

```html
<div
  x-data="{ show: false, userId: null }"
  x-show="show"
  x-on:open-modal-confirm-delete.window="show = true; userId = $event.detail.userId"
  x-on:close-modal-confirm-delete.window="show = false"
  x-on:keydown.escape.window="show = false"
  x-cloak
  class="fixed inset-0 z-50 overflow-y-auto"
  style="display: none;"
>
  <!-- Modal content -->
</div>
```

##### Dropdown Component

```html
<div
  x-data="{ open: false }"
  @click.away="open = false"
  class="relative inline-block text-left"
>
  <div @click="open = !open">
    <!-- Trigger button -->
  </div>

  <div
    x-show="open"
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="transform opacity-0 scale-95"
    x-transition:enter-end="transform opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="transform opacity-100 scale-100"
    x-transition:leave-end="transform opacity-0 scale-95"
    class="absolute z-50 mt-2 w-48 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5"
    style="display: none;"
  >
    <!-- Dropdown content -->
  </div>
</div>
```

### JavaScript Helpers (`resources/js/helpers.js`)

```javascript
/**
 * Format date to Indonesian format
 */
function formatDate(date, format = "DD/MM/YYYY") {
  if (!date) return "";

  const d = new Date(date);
  const day = String(d.getDate()).padStart(2, "0");
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const year = d.getFullYear();

  return format.replace("DD", day).replace("MM", month).replace("YYYY", year);
}

/**
 * Format currency to Rupiah
 */
function formatRupiah(amount) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(amount);
}

/**
 * Show toast notification
 */
function showToast(message, type = "success") {
  const toast = document.createElement("div");
  toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300 ${
    type === "success"
      ? "bg-green-500 text-white"
      : type === "error"
      ? "bg-red-500 text-white"
      : "bg-blue-500 text-white"
  }`;
  toast.textContent = message;

  document.body.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

/**
 * Copy text to clipboard
 */
async function copyToClipboard(text) {
  try {
    await navigator.clipboard.writeText(text);
    showToast("Copied to clipboard!");
  } catch (err) {
    console.error("Failed to copy: ", err);
  }
}

/**
 * Debounce function
 */
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}
```

### Tailwind CSS Configuration

#### Custom Design System

```javascript
// tailwind.config.js
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: "#eff6ff",
          500: "#3b82f6",
          600: "#2563eb",
          700: "#1d4ed8",
        },
        success: {
          50: "#f0fdf4",
          500: "#22c55e",
          600: "#16a34a",
        },
        danger: {
          50: "#fef2f2",
          500: "#ef4444",
          600: "#dc2626",
        },
      },
      fontFamily: {
        sans: ["Inter", "system-ui", "sans-serif"],
      },
      spacing: {
        18: "4.5rem",
        88: "22rem",
      },
    },
  },
  plugins: [require("@tailwindcss/forms")],
};
```

---

## 🔌 API Documentation

### Authentication Methods

#### 1. Static Token Authentication

Used for system-to-system communication.

**Header:**

```
Authorization: Bearer your-static-token
```

**Endpoints:**

- `GET /api/static/info` - API information
- `GET /api/static/validate` - Validate token
- `GET /api/static/test` - Test endpoint

#### 2. Sanctum Token Authentication

Used for user-based API access.

**Header:**

```
Authorization: Bearer user-generated-token
```

**Endpoints:**

- `POST /api/register` - User registration
- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/me` - Get current user
- `GET /api/users` - List users
- `POST /api/users` - Create user
- `GET /api/users/{id}` - Get user
- `PUT /api/users/{id}` - Update user
- `DELETE /api/users/{id}` - Delete user

### API Response Format

#### Success Response

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  }
}
```

#### Error Response

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

#### Paginated Response

```json
{
  "success": true,
  "message": "Data retrieved successfully",
  "data": [
    // Array of items
  ],
  "pagination": {
    "total": 100,
    "per_page": 10,
    "current_page": 1,
    "last_page": 10,
    "from": 1,
    "to": 10
  }
}
```

---

## 🧩 Component Library

### Form Components

#### Input Component (`resources/views/components/input.blade.php`)

```blade
@props([
  'label' => null,
  'name',
  'type' => 'text',
  'value' => '',
  'placeholder' => '',
  'required' => false,
  'disabled' => false,
  'error' => null,
  'hint' => null,
  'icon' => null,
])

<div class="space-y-1.5">
  @if ($label)
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1.5">
      {{ $label }}
      @if ($required)
        <span class="text-red-500 ml-0.5">*</span>
      @endif
    </label>
  @endif

  <div class="relative">
    @if ($icon)
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          {!! $icon !!}
        </svg>
      </div>
    @endif

    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
      value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}
      {{ $disabled ? 'disabled' : '' }}
      {{ $attributes->merge(['class' => 'block w-full px-3 py-2 text-sm rounded-lg border-gray-300 shadow-sm transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-500 focus:ring-opacity-20 ' . ($icon ? 'pl-10' : '') . ($error ? ' border-red-500 focus:border-red-500 focus:ring-red-500' : '') . ($disabled ? ' bg-gray-100 cursor-not-allowed' : '')]) }}>
  </div>

  @if ($hint && !$error)
    <p class="text-sm text-gray-600 mt-1">{{ $hint }}</p>
  @endif

  @if ($error)
    <p class="text-sm text-red-600 flex items-center mt-1">
      <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      {{ $error }}
    </p>
  @endif

  @error($name)
    <p class="text-sm text-red-600 flex items-center mt-1">
      <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      {{ $message }}
    </p>
  @enderror
</div>
```

#### Button Component (`resources/views/components/button.blade.php`)

```blade
@props([
  'variant' => 'primary',
  'size' => 'md',
  'type' => 'button',
  'href' => null,
  'icon' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$variants = [
  'primary' => 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white shadow-lg shadow-blue-500/50 focus:ring-blue-500',
  'secondary' => 'bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white shadow-lg shadow-gray-500/50 focus:ring-gray-500',
  'success' => 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white shadow-lg shadow-green-500/50 focus:ring-green-500',
  'danger' => 'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white shadow-lg shadow-red-500/50 focus:ring-red-500',
  'warning' => 'bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white shadow-lg shadow-yellow-500/50 focus:ring-yellow-500',
  'outline' => 'border-2 border-gray-300 hover:border-gray-400 text-gray-700 hover:bg-gray-50 focus:ring-gray-500',
];

$sizes = [
  'sm' => 'px-3 py-1.5 text-sm',
  'md' => 'px-4 py-2 text-sm',
  'lg' => 'px-5 py-2.5 text-base',
];

$classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

@if ($href)
  <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {!! $icon !!}
      </svg>
    @endif
    {{ $slot }}
  </a>
@else
  <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {!! $icon !!}
      </svg>
    @endif
    {{ $slot }}
  </button>
@endif
```

### UI Components

#### Card Component

```blade
@props(['padding' => true, 'shadow' => true])

<div
  {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-gray-200 overflow-hidden ' . ($shadow ? 'shadow-sm hover:shadow-md transition-shadow duration-300' : '') . ($padding ? ' p-6' : '')]) }}>
  {{ $slot }}
</div>
```

#### Badge Component

```blade
@props([
  'variant' => 'primary',
  'size' => 'md',
  'rounded' => true,
])

@php
$variants = [
  'primary' => 'bg-blue-100 text-blue-800',
  'success' => 'bg-green-100 text-green-800',
  'danger' => 'bg-red-100 text-red-800',
  'warning' => 'bg-yellow-100 text-yellow-800',
  'info' => 'bg-cyan-100 text-cyan-800',
  'gray' => 'bg-gray-100 text-gray-800',
  'purple' => 'bg-purple-100 text-purple-800',
  'pink' => 'bg-pink-100 text-pink-800',
];

$sizes = [
  'sm' => 'px-2.5 py-1 text-xs',
  'md' => 'px-3 py-1.5 text-sm',
  'lg' => 'px-4 py-2 text-base',
];
@endphp

<span
  {{ $attributes->merge(['class' => 'inline-flex items-center font-semibold ' . $variants[$variant] . ' ' . $sizes[$size] . ($rounded ? ' rounded-full' : ' rounded')]) }}>
  {{ $slot }}
</span>
```

---

## 🗄️ Database Schema

### Users Table

```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Personal Access Tokens Table (Sanctum)

```sql
CREATE TABLE personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Database Seeder

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin users
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Admin User',
            'email' => 'admin2@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Regular users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Generate additional random users
        User::factory(20)->create();
    }
}
```

---

## 🔐 Authentication & Authorization

### Web Authentication (Session-based)

```php
// Login
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
}

// Logout
public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
}
```

### API Authentication (Sanctum)

```php
// Register
public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
    ]);

    if ($validator->fails()) {
        return $this->validationErrorResponse($validator->errors());
    }

    $user = $this->authService->register($request->all());
    $token = $this->authService->createToken($user);

    return $this->createdResponse([
        'user' => $user,
        'token' => $token
    ], 'User registered successfully');
}

// Login
public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if ($validator->fails()) {
        return $this->validationErrorResponse($validator->errors());
    }

    $user = $this->authService->authenticate($request->only('email', 'password'));

    if (!$user) {
        return $this->errorResponse('Invalid credentials', null, 401);
    }

    $token = $this->authService->createToken($user);

    return $this->successResponse([
        'user' => $user,
        'token' => $token
    ], 'Login successful');
}
```

### Middleware Configuration

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
    ]);

    $middleware->alias([
        'auth.sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        'static.token' => \App\Http\Middleware\ValidateStaticToken::class,
    ]);
})
```

---

## 🎯 Best Practices

### Code Organization

#### 1. Service Layer Pattern

- **Controllers**: Handle HTTP requests/responses only
- **Services**: Contain business logic
- **Models**: Handle data relationships and accessors
- **Helpers**: Utility functions

#### 2. Consistent Naming Conventions

```php
// Controllers: PascalCase
UserController.php
AuthController.php

// Methods: camelCase
public function createUser()
public function updateUser()

// Variables: camelCase
$userService
$authService

// Database: snake_case
user_id
created_at
```

#### 3. Error Handling

```php
// API Controllers
try {
    $user = $this->userService->createUser($request->validated());
    return $this->createdResponse($user, 'User created successfully');
} catch (\Exception $e) {
    return $this->errorResponse('Failed to create user', $e->getMessage(), 500);
}

// Web Controllers
try {
    $user = $this->userService->createUser($request->validated());
    return redirect()->route('users.index')->with('success', 'User created successfully');
} catch (\Exception $e) {
    return back()->withInput()->with('error', 'Failed to create user');
}
```

### Frontend Best Practices

#### 1. Component Reusability

```blade
{{-- Use components consistently --}}
<x-input name="email" label="Email" type="email" required />
<x-button variant="primary" type="submit">Save</x-button>
<x-badge variant="success">Active</x-badge>
```

#### 2. Alpine.js Best Practices

```html
{{-- Use x-data for component state --}}
<div x-data="{ open: false, selectedItem: null }">
  {{-- Use x-show for conditional display --}}
  <div x-show="open" x-transition>{{-- Content --}}</div>

  {{-- Use @click for event handling --}}
  <button @click="open = !open">Toggle</button>
</div>
```

#### 3. Responsive Design

```html
{{-- Mobile-first approach --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  {{-- Content --}}
</div>

{{-- Responsive text --}}
<h1 class="text-xl md:text-2xl lg:text-3xl font-bold">{{-- Title --}}</h1>
```

### Security Best Practices

#### 1. Input Validation

```php
// Form Requests
class CreateUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:user,admin',
        ];
    }
}
```

#### 2. CSRF Protection

```blade
{{-- Always include CSRF token --}}
@csrf

{{-- For API calls --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
```

#### 3. Authorization

```php
// Check permissions
if (!$user->isAdmin()) {
    abort(403, 'Unauthorized');
}

// Use middleware
Route::middleware('auth')->group(function () {
    Route::resource('users', UserController::class);
});
```

---

## 🚀 Deployment

### Environment Configuration

```env
APP_NAME="CCTV Dashboard"
APP_ENV=production
APP_KEY=base64:your-app-key
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cctv_dashboard
DB_USERNAME=your-username
DB_PASSWORD=your-password

API_STATIC_TOKEN=your-static-token-here

SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

### Production Build

```bash
# Install dependencies
composer install --optimize-autoloader --no-dev

# Build assets
npm install
npm run build

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Seed database (optional)
php artisan db:seed --force
```

### Server Requirements

- **PHP**: 8.2 or higher
- **PostgreSQL**: 12 or higher
- **Node.js**: 18 or higher
- **Web Server**: Nginx or Apache
- **SSL Certificate**: Required for production

### Performance Optimization

```php
// config/database.php
'pgsql' => [
    'driver' => 'pgsql',
    'url' => env('DATABASE_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => 'prefer',
    'options' => [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
],
```

---

## 📚 Additional Resources

### Useful Commands

```bash
# Create new component
php artisan make:component Button

# Create new service
php artisan make:service UserService

# Clear all caches
php artisan optimize:clear

# Generate API documentation
php artisan route:list --path=api

# Run tests
php artisan test
```

### Development Tools

- **Laravel Debugbar**: For debugging
- **Laravel Telescope**: For monitoring
- **PHP CS Fixer**: For code formatting
- **Laravel Pint**: Built-in code formatter

### Monitoring & Logging

```php
// Log important events
Log::info('User created', ['user_id' => $user->id]);
Log::warning('Failed login attempt', ['email' => $request->email]);
Log::error('Database error', ['error' => $e->getMessage()]);
```

---

## 🤝 Contributing

### Code Style

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add comments for complex logic
- Write unit tests for new features

### Git Workflow

```bash
# Create feature branch
git checkout -b feature/new-feature

# Commit changes
git add .
git commit -m "feat: add new feature"

# Push to remote
git push origin feature/new-feature

# Create pull request
```

---

This documentation provides a comprehensive guide to the CCTV Dashboard codebase. For specific implementation details, refer to the actual source code files mentioned throughout this document.
