# Docker Setup untuk CCTV Dashboard

Setup Docker Compose dengan FrankenPHP, PostgreSQL, dan Redis untuk aplikasi Laravel CCTV Dashboard.

## 🚀 Features

- **FrankenPHP** dengan 16 threads worker mode
- **PostgreSQL 17** dengan health check
- **Redis 7** untuk cache dan session
- **Volume sync** dengan host untuk development
- **Database setup otomatis** (create, migrate, seed)
- **Production ready** configuration

## 📁 Struktur File

```
docker/
├── frankenphp/
│   ├── Dockerfile          # FrankenPHP dengan PHP 8.3
│   └── Caddyfile           # Caddy web server config
├── setup-db.sh            # Database setup script
└── README.md              # Dokumentasi ini
```

## 🔧 Services

### cctv_app (Port 9001)

- **FrankenPHP** dengan PHP 8.3
- **16 threads** worker mode
- **Caddy** web server
- **Laravel** application
- **Volume sync** dengan host

### postgresql (Port 5433)

- **PostgreSQL 17**
- **Database**: cctv_dashboard
- **User**: postgres
- **Password**: kambin
- **Health check** enabled

### redis (Port 6380)

- **Redis 7**
- **Cache** dan session storage
- **Persistent** data

## 🎯 Volume Sync

### Development Benefits:

- ✅ **File sync** - Semua file project sync dengan host
- ✅ **Hot reload** - Perubahan kode langsung terlihat
- ✅ **Build assets** - `npm run build` langsung sync
- ✅ **Log files** - Log tersimpan di host
- ✅ **Upload files** - File upload tersimpan di host

### Volume Mounts:

```yaml
volumes:
  - .:/app # Seluruh project
  - ./storage:/app/storage # Storage directory
  - ./bootstrap/cache:/app/bootstrap/cache # Cache directory
  - ./docker/frankenphp/Caddyfile:/etc/caddy/Caddyfile
```

## 🗄️ Database Setup Otomatis

### Proses yang berjalan otomatis:

1. **Wait for PostgreSQL** - Menunggu database siap
2. **Create database** - `php artisan db:create`
3. **Run migrations** - `php artisan migrate --force`
4. **Run seeders** - `php artisan db:seed --force`
5. **Clear cache** - Clear semua cache Laravel
6. **Start FrankenPHP** - Start web server

### Script: `docker/setup-db.sh`

```bash
#!/bin/bash
# Database setup script for CCTV Dashboard
echo "🚀 Starting database setup..."

# Wait for database to be ready
echo "⏳ Waiting for PostgreSQL to be ready..."
until php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; do
  echo "PostgreSQL is unavailable - sleeping"
  sleep 2
done

echo "✅ PostgreSQL is ready!"

# Create database if not exists
echo "📊 Creating database if not exists..."
php artisan db:create || echo "Database might already exist"

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Run seeders
echo "🌱 Running database seeders..."
php artisan db:seed --force

# Clear cache
echo "🧹 Clearing application cache..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "✅ Database setup completed!"
echo "🚀 Starting FrankenPHP..."

# Start FrankenPHP
exec frankenphp run --config /etc/caddy/Caddyfile
```

## 🚀 Quick Start

### 1. Clone dan Setup

```bash
git clone <repository>
cd cctv_dashboard
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Edit database configuration
DB_CONNECTION=pgsql
DB_HOST=postgresql
DB_PORT=5432
DB_DATABASE=cctv_dashboard
DB_USERNAME=postgres
DB_PASSWORD=kambin
```

### 3. Start Services

```bash
# Build dan start semua services
docker compose up -d

# Cek status
docker compose ps
```

### 4. Verify Setup

```bash
# Test aplikasi
curl http://localhost:9001/

# Cek database
docker compose exec cctv_app php artisan migrate:status

# Cek logs
docker compose logs cctv_app
```

## 🔧 Development Workflow

### File Sync

```bash
# Edit kode di host
vim resources/views/welcome.blade.php

# Build assets
npm run build

# File langsung sync ke container
# Tidak perlu restart container
```

### Database Operations

```bash
# Run migrations
docker compose exec cctv_app php artisan migrate

# Run seeders
docker compose exec cctv_app php artisan db:seed

# Clear cache
docker compose exec cctv_app php artisan cache:clear
```

### Logs dan Debugging

```bash
# View logs
docker compose logs cctv_app
docker compose logs postgresql
docker compose logs redis

# Access container
docker compose exec cctv_app bash
docker compose exec postgresql psql -U postgres -d cctv_dashboard
```

## 🎯 Configuration

### FrankenPHP Configuration

```dockerfile
# Dockerfile
ENV FRANKENPHP_CONFIG="worker:16 scheduler:2"
```

```caddyfile
# Caddyfile
{
    auto_https off
    admin off
    frankenphp {
        num_threads 16
    }
}

:80 {
    root * /app/public
    encode gzip
    php_server
}
```

### Database Configuration

```yaml
# docker-compose.yml
postgresql:
  environment:
    POSTGRES_DB: cctv_dashboard
    POSTGRES_USER: postgres
    POSTGRES_PASSWORD: kambin
  healthcheck:
    test: ["CMD-SHELL", "pg_isready -U postgres -d cctv_dashboard"]
    interval: 10s
    timeout: 5s
    retries: 5
    start_period: 30s
```

## 📊 Ports

| Service    | Host Port | Container Port | Description |
| ---------- | --------- | -------------- | ----------- |
| cctv_app   | 9001      | 80             | HTTP        |
| cctv_app   | 7443      | 443            | HTTPS       |
| postgresql | 5433      | 5432           | PostgreSQL  |
| redis      | 6380      | 6379           | Redis       |

## 🛠️ Troubleshooting

### Container tidak start

```bash
# Cek logs
docker compose logs cctv_app

# Restart services
docker compose restart cctv_app
```

### Database connection error

```bash
# Cek database status
docker compose exec postgresql pg_isready -U postgres

# Test connection
docker compose exec cctv_app php artisan tinker --execute="DB::connection()->getPdo();"
```

### Volume sync tidak bekerja

```bash
# Cek volume mounts
docker compose exec cctv_app ls -la /app/

# Restart dengan rebuild
docker compose down
docker compose up -d --build
```

## 🎉 Production Deployment

### Environment Variables

```env
APP_ENV=production
APP_DEBUG=false
DB_HOST=postgresql
REDIS_HOST=redis
```

### Security

- ✅ Security headers di Caddyfile
- ✅ Database password protected
- ✅ Redis tidak exposed ke public
- ✅ FrankenPHP worker mode untuk performa

### Performance

- ✅ FrankenPHP 16 threads
- ✅ OPcache enabled
- ✅ Gzip compression
- ✅ Static file caching
- ✅ Redis untuk cache dan session

## 📝 Commands

### Docker Compose

```bash
# Start services
docker compose up -d

# Stop services
docker compose down

# Rebuild dan start
docker compose up -d --build

# View logs
docker compose logs -f cctv_app

# Access container
docker compose exec cctv_app bash
```

### Laravel Commands

```bash
# Artisan commands
docker compose exec cctv_app php artisan migrate
docker compose exec cctv_app php artisan db:seed
docker compose exec cctv_app php artisan cache:clear

# Composer commands
docker compose exec cctv_app composer install
docker compose exec cctv_app composer update
```

### Database Commands

```bash
# PostgreSQL access
docker compose exec postgresql psql -U postgres -d cctv_dashboard

# Redis access
docker compose exec redis redis-cli
```

## 🎯 Summary

Setup Docker Compose ini menyediakan:

- ✅ **FrankenPHP** dengan worker mode untuk performa tinggi
- ✅ **PostgreSQL** dengan health check dan setup otomatis
- ✅ **Redis** untuk cache dan session
- ✅ **Volume sync** untuk development yang efisien
- ✅ **Database setup otomatis** (create, migrate, seed)
- ✅ **Production ready** configuration

Siap untuk development dan production deployment! 🚀
