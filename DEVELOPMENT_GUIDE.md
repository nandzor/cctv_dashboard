# Development Guide - CCTV Dashboard

## 🚀 Getting Started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18 or higher
- PostgreSQL 12 or higher
- Git

### Installation

1. **Clone the repository**

```bash
git clone <repository-url>
cd cctv_dashboard
```

2. **Install PHP dependencies**

```bash
composer install
```

3. **Install Node.js dependencies**

```bash
npm install
```

4. **Environment setup**

```bash
cp .env.example .env
php artisan key:generate
```

5. **Database setup**

```bash
# Configure database in .env file
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cctv_dashboard
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed
```

6. **Build assets**

```bash
npm run build
```

7. **Start development server**

```bash
php artisan serve
```

---

## 🏗️ Project Structure

### Backend Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/              # API Controllers
│   │   │   ├── BaseController.php
│   │   │   ├── AuthController.php
│   │   │   ├── UserController.php
│   │   │   ├── StaticAuthController.php
│   │   │   └── TestController.php
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   └── UserController.php
│   └── Middleware/
│       ├── ValidateStaticToken.php
│       └── HandleInertiaRequests.php
├── Models/
│   └── User.php
├── Services/
│   ├── AuthService.php
│   └── UserService.php
└── Helpers/
    └── helpers.php
```

### Frontend Structure

```
resources/
├── views/
│   ├── components/           # Blade Components
│   │   ├── form/            # Form Components
│   │   ├── ui/              # UI Components
│   │   └── layout/          # Layout Components
│   ├── layouts/
│   │   ├── app.blade.php
│   │   └── guest.blade.php
│   ├── auth/
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   ├── dashboard/
│   │   └── index.blade.php
│   └── users/
│       ├── index.blade.php
│       ├── create.blade.php
│       ├── edit.blade.php
│       └── show.blade.php
└── js/
    ├── app.js
    ├── bootstrap.js
    ├── helpers.js
    └── toast.js
```

---

## 🔧 Development Workflow

### 1. Feature Development

#### Creating a New Feature

```bash
# Create controller
php artisan make:controller FeatureController

# Create service
php artisan make:service FeatureService

# Create model (if needed)
php artisan make:model Feature

# Create migration
php artisan make:migration create_features_table

# Create component
php artisan make:component FeatureCard
```

#### File Naming Conventions

- **Controllers**: `PascalCaseController.php`
- **Models**: `PascalCase.php`
- **Services**: `PascalCaseService.php`
- **Components**: `kebab-case.blade.php`
- **Migrations**: `YYYY_MM_DD_HHMMSS_descriptive_name.php`

### 2. Code Organization

#### Service Layer Pattern

```php
// Controller (thin)
class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function store(CreateUserRequest $request)
    {
        $user = $this->userService->createUser($request->validated());
        return redirect()->route('users.index')->with('success', 'User created');
    }
}

// Service (business logic)
class UserService
{
    public function createUser(array $data): User
    {
        // Business logic here
        return User::create($data);
    }
}
```

#### Component Structure

```blade
{{-- resources/views/components/feature-card.blade.php --}}
@props(['feature', 'variant' => 'default'])

@php
    $variants = [
        'default' => 'bg-white',
        'highlighted' => 'bg-blue-50 border-blue-200',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'p-4 rounded-lg border ' . $variants[$variant]]) }}>
    <h3 class="font-semibold">{{ $feature->title }}</h3>
    <p class="text-gray-600">{{ $feature->description }}</p>
</div>
```

### 3. Database Development

#### Migration Best Practices

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
```

#### Model Relationships

```php
class User extends Model
{
    protected $fillable = ['name', 'email', 'role'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function features()
    {
        return $this->hasMany(Feature::class);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

---

## 🎨 Frontend Development

### 1. Component Development

#### Creating Custom Components

```blade
{{-- resources/views/components/custom-button.blade.php --}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'loading' => false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
        'secondary' => 'bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-500',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-6 py-3 text-lg',
    ];

    $classes = $baseClasses . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

<button {{ $attributes->merge(['class' => $classes, 'disabled' => $loading]) }}>
    @if ($loading)
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @endif
    {{ $slot }}
</button>
```

### 2. Alpine.js Integration

#### Component State Management

```html
<div x-data="userManager()" class="space-y-4">
  <div class="flex items-center space-x-4">
    <input
      x-model="searchQuery"
      @input.debounce.300ms="searchUsers()"
      type="text"
      placeholder="Search users..."
      class="flex-1 px-3 py-2 border border-gray-300 rounded-lg"
    />
    <button
      @click="showCreateModal = true"
      class="px-4 py-2 bg-blue-600 text-white rounded-lg"
    >
      Add User
    </button>
  </div>

  <div x-show="loading" class="text-center py-4">
    <div
      class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"
    ></div>
  </div>

  <div
    x-show="!loading"
    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
  >
    <template x-for="user in users" :key="user.id">
      <div class="p-4 bg-white rounded-lg shadow">
        <h3 x-text="user.name" class="font-semibold"></h3>
        <p x-text="user.email" class="text-gray-600"></p>
        <div class="mt-2 flex space-x-2">
          <button
            @click="editUser(user)"
            class="text-blue-600 hover:text-blue-800"
          >
            Edit
          </button>
          <button
            @click="deleteUser(user.id)"
            class="text-red-600 hover:text-red-800"
          >
            Delete
          </button>
        </div>
      </div>
    </template>
  </div>
</div>

<script>
  function userManager() {
    return {
      users: [],
      searchQuery: "",
      loading: false,
      showCreateModal: false,

      async init() {
        await this.loadUsers();
      },

      async loadUsers() {
        this.loading = true;
        try {
          const response = await fetch("/api/users");
          const data = await response.json();
          this.users = data.data;
        } catch (error) {
          console.error("Error loading users:", error);
        } finally {
          this.loading = false;
        }
      },

      async searchUsers() {
        if (this.searchQuery.length < 2) {
          await this.loadUsers();
          return;
        }

        this.loading = true;
        try {
          const response = await fetch(`/api/users?search=${this.searchQuery}`);
          const data = await response.json();
          this.users = data.data;
        } catch (error) {
          console.error("Error searching users:", error);
        } finally {
          this.loading = false;
        }
      },

      editUser(user) {
        // Handle edit logic
        window.location.href = `/users/${user.id}/edit`;
      },

      async deleteUser(userId) {
        if (!confirm("Are you sure you want to delete this user?")) {
          return;
        }

        try {
          const response = await fetch(`/api/users/${userId}`, {
            method: "DELETE",
            headers: {
              "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            },
          });

          if (response.ok) {
            await this.loadUsers();
            this.showToast("User deleted successfully", "success");
          }
        } catch (error) {
          console.error("Error deleting user:", error);
          this.showToast("Error deleting user", "error");
        }
      },

      showToast(message, type = "info") {
        // Toast notification logic
        const toast = document.createElement("div");
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
          type === "success"
            ? "bg-green-500 text-white"
            : type === "error"
            ? "bg-red-500 text-white"
            : "bg-blue-500 text-white"
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
          toast.remove();
        }, 3000);
      },
    };
  }
</script>
```

### 3. JavaScript Helpers

#### API Helper Functions

```javascript
// resources/js/api.js
class ApiClient {
  constructor(baseURL = "/api") {
    this.baseURL = baseURL;
    this.token = localStorage.getItem("token");
  }

  setToken(token) {
    this.token = token;
    localStorage.setItem("token", token);
  }

  async request(endpoint, options = {}) {
    const url = `${this.baseURL}${endpoint}`;
    const config = {
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        ...options.headers,
      },
      ...options,
    };

    if (this.token) {
      config.headers.Authorization = `Bearer ${this.token}`;
    }

    try {
      const response = await fetch(url, config);
      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || "Request failed");
      }

      return data;
    } catch (error) {
      console.error("API Error:", error);
      throw error;
    }
  }

  async get(endpoint, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const url = queryString ? `${endpoint}?${queryString}` : endpoint;
    return this.request(url);
  }

  async post(endpoint, data) {
    return this.request(endpoint, {
      method: "POST",
      body: JSON.stringify(data),
    });
  }

  async put(endpoint, data) {
    return this.request(endpoint, {
      method: "PUT",
      body: JSON.stringify(data),
    });
  }

  async delete(endpoint) {
    return this.request(endpoint, {
      method: "DELETE",
    });
  }
}

// Global API instance
window.api = new ApiClient();
```

#### Form Helper Functions

```javascript
// resources/js/forms.js
class FormHelper {
  static serialize(form) {
    const formData = new FormData(form);
    const data = {};

    for (let [key, value] of formData.entries()) {
      data[key] = value;
    }

    return data;
  }

  static validate(form, rules) {
    const errors = {};
    const formData = new FormData(form);

    for (const [field, rule] of Object.entries(rules)) {
      const value = formData.get(field);

      if (rule.required && !value) {
        errors[field] = [`The ${field} field is required.`];
      }

      if (rule.email && value && !this.isValidEmail(value)) {
        errors[field] = [`The ${field} must be a valid email address.`];
      }

      if (rule.min && value && value.length < rule.min) {
        errors[field] = [
          `The ${field} must be at least ${rule.min} characters.`,
        ];
      }
    }

    return Object.keys(errors).length > 0 ? errors : null;
  }

  static isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  static showErrors(form, errors) {
    // Clear previous errors
    form.querySelectorAll(".error-message").forEach((el) => el.remove());
    form.querySelectorAll(".border-red-500").forEach((el) => {
      el.classList.remove("border-red-500");
      el.classList.add("border-gray-300");
    });

    // Show new errors
    for (const [field, messages] of Object.entries(errors)) {
      const input = form.querySelector(`[name="${field}"]`);
      if (input) {
        input.classList.add("border-red-500");
        input.classList.remove("border-gray-300");

        const errorDiv = document.createElement("div");
        errorDiv.className = "error-message text-sm text-red-600 mt-1";
        errorDiv.textContent = messages[0];

        input.parentNode.appendChild(errorDiv);
      }
    }
  }
}

// Make available globally
window.FormHelper = FormHelper;
```

---

## 🧪 Testing

### 1. Backend Testing

#### Feature Tests

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)
            ->post('/users', [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'user'
            ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    public function test_can_update_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->put("/users/{$user->id}", [
                'name' => 'Updated Name',
                'email' => $user->email,
                'role' => 'admin'
            ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'role' => 'admin'
        ]);
    }
}
```

#### API Tests

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_users_with_authentication()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json'
        ])->get('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'email', 'role']
                ],
                'pagination'
            ]);
    }

    public function test_cannot_access_users_without_authentication()
    {
        $response = $this->get('/api/users');

        $response->assertStatus(401);
    }
}
```

### 2. Frontend Testing

#### Component Testing (with Livewire or similar)

```php
<?php

namespace Tests\Feature\Components;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_card_displays_correctly()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'admin'
        ]);

        $response = $this->get('/users');

        $response->assertSee('John Doe')
            ->assertSee('john@example.com')
            ->assertSee('Admin');
    }
}
```

---

## 🔧 Development Tools

### 1. Code Quality

#### PHP CS Fixer

```bash
# Install PHP CS Fixer
composer require --dev friendsofphp/php-cs-fixer

# Run code style fix
./vendor/bin/php-cs-fixer fix

# Check code style
./vendor/bin/php-cs-fixer fix --dry-run --diff
```

#### Laravel Pint (Built-in)

```bash
# Run Pint
./vendor/bin/pint

# Check without fixing
./vendor/bin/pint --test
```

### 2. Static Analysis

#### PHPStan

```bash
# Install PHPStan
composer require --dev phpstan/phpstan

# Run analysis
./vendor/bin/phpstan analyse
```

### 3. Development Scripts

#### Package.json Scripts

```json
{
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "watch": "vite build --watch",
    "hot": "vite --host",
    "lint": "eslint resources/js --ext .js,.vue",
    "lint:fix": "eslint resources/js --ext .js,.vue --fix"
  }
}
```

---

## 🚀 Deployment

### 1. Production Build

#### Asset Compilation

```bash
# Install production dependencies
npm ci

# Build assets for production
npm run build

# Optimize Composer autoloader
composer install --optimize-autoloader --no-dev
```

#### Laravel Optimization

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Clear and rebuild caches
php artisan optimize:clear
php artisan optimize
```

### 2. Environment Configuration

#### Production .env

```env
APP_NAME="CCTV Dashboard"
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

API_STATIC_TOKEN=your-secure-static-token

SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

### 3. Server Configuration

#### Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /path/to/cctv_dashboard/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 📚 Best Practices

### 1. Code Organization

#### Service Layer

- Keep controllers thin
- Move business logic to services
- Use dependency injection
- Follow single responsibility principle

#### Component Design

- Make components reusable
- Use consistent props interface
- Follow naming conventions
- Document component usage

### 2. Security

#### Input Validation

```php
// Use Form Requests
class CreateUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];
    }
}
```

#### CSRF Protection

```blade
{{-- Always include CSRF token --}}
@csrf

{{-- For AJAX requests --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### 3. Performance

#### Database Optimization

```php
// Use eager loading
$users = User::with('features')->get();

// Use database indexes
Schema::table('users', function (Blueprint $table) {
    $table->index('email');
    $table->index('role');
});

// Use pagination
$users = User::paginate(15);
```

#### Frontend Optimization

```javascript
// Debounce search input
const debouncedSearch = debounce(searchUsers, 300);

// Lazy load components
const LazyComponent = lazy(() => import("./LazyComponent"));

// Use virtual scrolling for large lists
import { FixedSizeList as List } from "react-window";
```

---

## 🐛 Debugging

### 1. Laravel Debugging

#### Debug Bar

```bash
# Install Laravel Debugbar
composer require barryvdh/laravel-debugbar --dev
```

#### Logging

```php
// Log important events
Log::info('User created', ['user_id' => $user->id]);
Log::warning('Failed login attempt', ['email' => $request->email]);
Log::error('Database error', ['error' => $e->getMessage()]);
```

### 2. Frontend Debugging

#### Browser DevTools

- Use console.log for debugging
- Check network tab for API calls
- Use React DevTools for component inspection

#### Error Handling

```javascript
// Global error handler
window.addEventListener("error", (event) => {
  console.error("Global error:", event.error);
  // Send to error tracking service
});

// API error handling
try {
  const response = await api.get("/users");
  // Handle success
} catch (error) {
  console.error("API Error:", error);
  // Show user-friendly error message
}
```

---

## 📖 Additional Resources

### Documentation

- [Laravel Documentation](https://laravel.com/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Alpine.js Documentation](https://alpinejs.dev/)
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)

### Tools

- [Laravel Telescope](https://laravel.com/docs/telescope) - Debug and monitor
- [Laravel Horizon](https://laravel.com/docs/horizon) - Queue monitoring
- [Laravel Nova](https://nova.laravel.com/) - Admin panel
- [Laravel Breeze](https://laravel.com/docs/starter-kits#laravel-breeze) - Authentication scaffolding

### Community

- [Laravel Discord](https://discord.gg/laravel)
- [Laravel News](https://laravel-news.com/)
- [Laracasts](https://laracasts.com/)

---

This development guide provides comprehensive information for developing and maintaining the CCTV Dashboard application. Follow these guidelines to ensure code quality, security, and maintainability.
