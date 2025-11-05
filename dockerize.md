# 🐳 Docker Setup Guide - CCTV Dashboard

Dokumentasi lengkap untuk menjalankan aplikasi CCTV Dashboard menggunakan Docker.

---

## 📋 Daftar Isi

- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Struktur Docker](#struktur-docker)
- [Setup Environment](#setup-environment)
- [Build dan Run](#build-dan-run)
- [Makefile Commands](#makefile-commands)
- [Docker Compose Files](#docker-compose-files)
- [Troubleshooting](#troubleshooting)
- [Best Practices](#best-practices)

---

## 🎯 Overview

Aplikasi ini menggunakan:
- **FrankenPHP** sebagai web server (menggabungkan PHP dan Caddy)
- **Laravel Horizon** untuk queue processing
- **PostgreSQL** sebagai database (external)
- **Redis** untuk queue dan cache (external)

Container Docker akan menjalankan:
- FrankenPHP web server
- Laravel Horizon queue worker

---

## ✅ Prerequisites

Sebelum menggunakan Docker, pastikan sudah terinstall:

- **Docker** (versi 20.10 atau lebih baru)
- **Docker Compose** (versi 2.0 atau lebih baru)
- **Make** (optional, untuk menggunakan Makefile commands)

Cek versi:
```bash
docker --version
docker compose version
make --version
```

---

## 📁 Struktur Docker

```
docker/
└── frankenphp/
    ├── Dockerfile          # Docker image definition
    ├── Caddyfile           # Caddy web server configuration
    └── start.sh            # Startup script untuk FrankenPHP + Horizon

docker-compose.local.yaml   # Local development
docker-compose.dev.yaml     # Development environment
docker-compose.staging.yaml # Staging environment
docker-compose.yaml         # Production environment

Makefile                    # Build dan deployment commands
```

---

## 🔧 Setup Environment

### 1. Buat File `.env`

Copy dari `.env.example` dan sesuaikan konfigurasi:

```bash
cp .env.example .env
```

### 2. Konfigurasi Database dan Redis

Pastikan `.env` sudah dikonfigurasi dengan benar:

```env
# Database (external)
DB_CONNECTION=pgsql
DB_HOST=your_postgres_host
DB_PORT=5432
DB_DATABASE=cctv_dashboard
DB_USERNAME=postgres
DB_PASSWORD=your_password

# Redis (external)
REDIS_HOST=your_redis_host
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# App Configuration
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:9001
```

**Catatan:** Docker Compose tidak include PostgreSQL dan Redis. Pastikan database dan Redis sudah tersedia secara external.

---

## 🚀 Build dan Run

### Menggunakan Makefile (Recommended)

```bash
# Local development
make local

# Development environment
make dev

# Staging environment
make staging

# Production environment
make production

# Deploy (menggunakan docker-compose.yaml default)
make deploy
```

### Manual Build dan Run

```bash
# Build image
docker build . -t cctv/dashboard:local -f docker/frankenphp/Dockerfile

# Run dengan docker-compose
docker compose -f docker-compose.local.yaml up -d

# Lihat logs
docker compose -f docker-compose.local.yaml logs -f

# Stop container
docker compose -f docker-compose.local.yaml down
```

---

## 📝 Makefile Commands

### `make deploy`
Deploy menggunakan `docker-compose.yaml` (production default)

```bash
make deploy
```

### `make local`
Build dan run untuk local development

```bash
make local
```

**Image:** `cctv/dashboard:local`  
**Container:** `cctv_app_local`  
**Port:** `9001:80`

### `make dev`
Build dan run untuk development environment

```bash
make dev
```

**Image:** `cctv/dashboard:dev`  
**Container:** `cctv_app_dev`  
**Port:** `9001:80`

### `make staging`
Build dan run untuk staging environment

```bash
make staging
```

**Image:** `cctv/dashboard:staging`  
**Container:** `cctv_app_staging`  
**Port:** `9001:80`

### `make production`
Build dan run untuk production environment

```bash
make production
```

**Image:** `cctv/dashboard:latest`  
**Container:** `cctv_app_production`  
**Port:** `9001:80`

---

## 🐳 Docker Compose Files

### docker-compose.local.yaml

Konfigurasi untuk local development (menggunakan `network_mode: host`):

```yaml
services:
  cctv_app:
    build:
      context: .
      dockerfile: docker/frankenphp/Dockerfile
    image: cctv/dashboard:local
    container_name: cctv_app_local
    network_mode: host
    env_file:
      - .env
    restart: unless-stopped
```

**Catatan:** Dengan `network_mode: host`, container menggunakan network host secara langsung. Aplikasi akan tersedia di `http://localhost:9001` (sesuai konfigurasi di Caddyfile).

**Penggunaan:**
```bash
docker compose -f docker-compose.local.yaml up -d
docker compose -f docker-compose.local.yaml logs -f
docker compose -f docker-compose.local.yaml down
```

### docker-compose.dev.yaml

Konfigurasi untuk development environment (menggunakan `network_mode: host`):

```yaml
services:
  cctv_app:
    image: cctv/dashboard:dev
    container_name: cctv_app_dev
    network_mode: host
    env_file:
      - .env
    restart: unless-stopped
```

### docker-compose.staging.yaml

Konfigurasi untuk staging environment (menggunakan `network_mode: host`):

```yaml
services:
  cctv_app:
    image: cctv/dashboard:staging
    container_name: cctv_app_staging
    network_mode: host
    env_file:
      - .env
    restart: always
```

### docker-compose.yaml

Konfigurasi untuk production (menggunakan `network_mode: host`):

```yaml
services:
  cctv_app:
    image: cctv/dashboard:latest
    container_name: cctv_app_production
    network_mode: host
    env_file:
      - .env
    restart: always
```

---

## 🔍 Troubleshooting

### 1. Container tidak start

```bash
# Cek logs
docker compose -f docker-compose.local.yaml logs

# Cek status container
docker ps -a | grep cctv_app

# Cek apakah port sudah digunakan
lsof -i :9001
```

### 2. Horizon tidak berjalan

```bash
# Masuk ke container
docker exec -it cctv_app_local bash

# Cek status Horizon
php artisan horizon:status

# Restart Horizon
php artisan horizon:terminate
php artisan horizon
```

### 3. Database connection error

Pastikan:
- Database host sudah benar di `.env`
- Database server accessible dari container
- Credentials sudah benar

```bash
# Test connection dari container
docker exec -it cctv_app_local php artisan tinker
>>> DB::connection()->getPdo();
```

### 4. Redis connection error

Pastikan:
- Redis host sudah benar di `.env`
- Redis server accessible dari container

**Mengakses Redis di Host (127.0.0.1:6379) dari Container:**

Docker Compose sudah dikonfigurasi dengan `extra_hosts` untuk mengakses host. Gunakan salah satu opsi berikut di file `.env`:

**Opsi 1: Menggunakan host.docker.internal (Recommended)**
```env
REDIS_HOST=host.docker.internal
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
```

**Opsi 2: Menggunakan IP Gateway Docker (172.17.0.1)**
```env
REDIS_HOST=172.17.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
REDIS_DB=0
```

**Opsi 3: Menggunakan network_mode: host (Saat ini digunakan)**
Docker Compose sudah dikonfigurasi dengan `network_mode: host`, jadi gunakan:
```env
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

**Catatan tentang network_mode: host:**
- ✅ Container menggunakan network host secara langsung
- ✅ `127.0.0.1` di container = `127.0.0.1` di host
- ✅ Tidak perlu port mapping (container langsung menggunakan port host)
- ⚠️ Container akan langsung menggunakan port 80 di host (tidak ada isolasi port)
- ⚠️ Kurang isolasi network (tapi cocok untuk development)
- ✅ Ideal untuk development karena langsung akses ke service di host

```bash
# Test Redis connection dari container
docker exec -it cctv_app_local php artisan tinker
>>> Redis::ping();

# Atau test langsung dari container
docker exec -it cctv_app_local php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); echo 'Redis: '; try { \Illuminate\Support\Facades\Redis::ping(); echo 'OK'; } catch (Exception \$e) { echo 'ERROR: ' . \$e->getMessage(); }"
```

### 5. Build image gagal

```bash
# Clean build (tanpa cache)
docker build --no-cache . -t cctv/dashboard:local -f docker/frankenphp/Dockerfile

# Cek disk space
df -h

# Remove unused images
docker image prune -a
```

### 6. Permission issues

```bash
# Fix storage permissions
docker exec -it cctv_app_local chmod -R 775 storage bootstrap/cache
docker exec -it cctv_app_local chown -R www-data:www-data storage bootstrap/cache
```

---

## 📊 Monitoring dan Logs

### View Logs

```bash
# All logs
docker compose -f docker-compose.local.yaml logs -f

# App logs only
docker compose -f docker-compose.local.yaml logs -f cctv_app

# Last 100 lines
docker compose -f docker-compose.local.yaml logs --tail=100 cctv_app
```

### View Container Status

```bash
# List running containers
docker ps | grep cctv_app

# Container stats (CPU, Memory)
docker stats cctv_app_local

# Container details
docker inspect cctv_app_local
```

### Horizon Monitoring

```bash
# Access Horizon dashboard
http://localhost:9001/horizon

# Check Horizon status
docker exec -it cctv_app_local php artisan horizon:status

# View Horizon logs
docker exec -it cctv_app_local tail -f storage/logs/horizon.log
```

---

## 🛠️ Best Practices

### 1. Environment Variables

- Jangan commit `.env` ke repository
- Gunakan `.env.example` sebagai template
- Setiap environment harus memiliki `.env` sendiri

### 2. Image Management

```bash
# Tag images dengan version
docker tag cctv/dashboard:latest cctv/dashboard:v1.0.0

# Push ke registry (jika menggunakan)
docker push cctv/dashboard:latest

# Remove unused images
docker image prune -a --filter "until=24h"
```

### 3. Security

- Jangan hardcode credentials di Dockerfile
- Gunakan secrets management untuk production
- Update base images secara berkala
- Scan images untuk vulnerabilities

### 4. Performance

```bash
# Build dengan cache optimization
docker build --cache-from cctv/dashboard:latest . -t cctv/dashboard:new -f docker/frankenphp/Dockerfile

# Use multi-stage build jika image terlalu besar
# (Optional: convert ke multi-stage build)
```

### 5. Backup

```bash
# Backup storage
docker exec cctv_app_local tar -czf /tmp/storage-backup.tar.gz /app/storage

# Backup database (external)
pg_dump -h your_db_host -U postgres cctv_dashboard > backup.sql
```

---

## 🔄 Update dan Deployment

### Update Application

```bash
# 1. Pull latest code
git pull origin main

# 2. Rebuild image
make production  # atau make staging, make dev, dll

# 3. Restart container
docker compose -f docker-compose.yaml restart
```

### Rollback

```bash
# Stop current container
docker compose -f docker-compose.yaml down

# Run previous version
docker run -d --name cctv_app_production \
  -p 9001:80 \
  --env-file .env \
  cctv/dashboard:previous-version
```

---

## 📚 Additional Commands

### Artisan Commands

```bash
# Run migrations
docker exec -it cctv_app_local php artisan migrate

# Clear cache
docker exec -it cctv_app_local php artisan cache:clear
docker exec -it cctv_app_local php artisan config:clear
docker exec -it cctv_app_local php artisan route:clear
docker exec -it cctv_app_local php artisan view:clear

# Optimize
docker exec -it cctv_app_local php artisan optimize

# Queue commands
docker exec -it cctv_app_local php artisan queue:work
docker exec -it cctv_app_local php artisan horizon:purge
```

### Maintenance Mode

```bash
# Enable maintenance mode
docker exec -it cctv_app_local php artisan down

# Disable maintenance mode
docker exec -it cctv_app_local php artisan up
```

---

## 🆘 Support

Jika mengalami masalah:

1. Cek logs: `docker compose logs -f`
2. Cek container status: `docker ps -a`
3. Cek environment: `docker exec -it cctv_app_local env`
4. Review dokumentasi Laravel Horizon
5. Review dokumentasi FrankenPHP

---

## 📖 References

- [FrankenPHP Documentation](https://frankenphp.dev/)
- [Laravel Horizon Documentation](https://laravel.com/docs/horizon)
- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)

---

**Last Updated:** $(date)  
**Version:** 1.0.0

