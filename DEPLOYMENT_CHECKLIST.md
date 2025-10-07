# 🚀 Deployment Checklist

**Environment-specific deployment checklist for CCTV Dashboard**

---

## 📋 PRE-DEPLOYMENT

### **Code Review:**

- [ ] All features tested locally
- [ ] No linter errors
- [ ] Code reviewed by team
- [ ] Git commits clean and descriptive
- [ ] No sensitive data in code
- [ ] .env.example updated with all variables
- [ ] Documentation up to date

### **Database:**

- [ ] Migrations tested
- [ ] Seeders work correctly
- [ ] Backup strategy planned
- [ ] Database credentials secure
- [ ] Connection pooling configured (PgBouncer)
- [ ] Indexes optimized

### **Security:**

- [ ] APP_DEBUG=false in production
- [ ] APP_ENV=production
- [ ] Strong APP_KEY generated
- [ ] Default passwords changed
- [ ] API secrets regenerated for production
- [ ] SSL certificate installed
- [ ] HTTPS forced
- [ ] Security headers configured
- [ ] CORS configured properly
- [ ] Rate limiting tested

---

## 🔧 STAGING DEPLOYMENT

### **Server Setup:**

- [ ] Server meets requirements
  - [ ] PHP 8.2+
  - [ ] PostgreSQL 15+
  - [ ] Nginx/Apache configured
  - [ ] Supervisor installed
  - [ ] Cron configured

### **Application Setup:**

```bash
# 1. Clone repository
git clone <repository> /var/www/cctv_dashboard
cd /var/www/cctv_dashboard

# 2. Run setup script
./setup.sh

# 3. Configure environment
cp .env.example .env
nano .env  # Edit with staging credentials

# 4. Build and deploy
./deploy.sh staging
```

### **Environment Configuration:**

```env
APP_ENV=staging
APP_DEBUG=false  # or true for debugging
APP_URL=https://staging.your-domain.com

DB_CONNECTION=pgsql
DB_HOST=staging-db-host
DB_DATABASE=cctv_dashboard_staging

WHATSAPP_API_URL=https://staging-whatsapp-api
# ... other staging configs
```

### **Testing on Staging:**

- [ ] Application accessible via URL
- [ ] Login works
- [ ] All modules functional
- [ ] API endpoints responding
- [ ] Queue workers running
- [ ] Cron jobs executing
- [ ] Logs being written
- [ ] Backups running
- [ ] Performance acceptable
- [ ] Mobile responsive

---

## 🎯 PRODUCTION DEPLOYMENT

### **Pre-Production Checklist:**

- [ ] Staging tests all passed
- [ ] Client approval received
- [ ] Deployment window scheduled
- [ ] Team notified
- [ ] Rollback plan prepared
- [ ] Database backup created
- [ ] Current version tagged in git

### **Production Server Setup:**

```bash
# 1. Clone to production server
git clone <repository> /var/www/cctv_dashboard
cd /var/www/cctv_dashboard

# 2. Checkout production branch/tag
git checkout tags/v1.0.0  # or production branch

# 3. Configure environment
cp .env.example .env
nano .env  # Edit with PRODUCTION credentials
```

### **Production .env Configuration:**

```env
APP_NAME="CCTV Dashboard"
APP_ENV=production
APP_DEBUG=false  # MUST be false!
APP_URL=https://cctv.your-domain.com

# Database (Production)
DB_CONNECTION=pgsql
DB_HOST=production-db-host.com
DB_PORT=5432
DB_DATABASE=cctv_dashboard_prod
DB_USERNAME=cctv_prod_user
DB_PASSWORD=STRONG_PASSWORD_HERE

# Disable query logging in production!
DB_LOG_QUERIES=false

# Queue
QUEUE_CONNECTION=database

# Encryption (MUST be true!)
ENCRYPT_DEVICE_CREDENTIALS=true
ENCRYPT_STREAM_CREDENTIALS=true

# WhatsApp (Production API)
WHATSAPP_PROVIDER=waha
WHATSAPP_API_URL=https://whatsapp-api.your-domain.com
WHATSAPP_API_KEY=PRODUCTION_KEY_HERE
WHATSAPP_SESSION_NAME=production

# Storage (Consider S3 for production)
FILESYSTEM_DISK=s3  # or local
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=

# Performance
PERFORMANCE_MONITORING=true
SLOW_QUERY_THRESHOLD=1000
HIGH_MEMORY_THRESHOLD=128

# Session
SESSION_DRIVER=database
SESSION_LIFETIME=120

# Mail (Production SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-domain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@your-domain.com
MAIL_PASSWORD=SMTP_PASSWORD
MAIL_ENCRYPTION=tls

# Sanctum
SANCTUM_STATEFUL_DOMAINS=cctv.your-domain.com
```

### **Deploy to Production:**

```bash
# Run deployment script
./deploy.sh production

# Or manual steps:
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
chmod -R 755 storage bootstrap/cache
```

### **Supervisor Configuration:**

Create `/etc/supervisor/conf.d/cctv-workers.conf`:

```ini
[program:cctv-worker-detections]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/cctv_dashboard/artisan queue:work --queue=detections --tries=3 --timeout=120
autostart=true
autorestart=true
user=www-data
numprocs=5
redirect_stderr=true
stdout_logfile=/var/www/cctv_dashboard/storage/logs/worker-detections.log

[program:cctv-worker-notifications]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/cctv_dashboard/artisan queue:work --queue=notifications --tries=5 --timeout=60
autostart=true
autorestart=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/cctv_dashboard/storage/logs/worker-notifications.log
```

```bash
# Reload supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

### **Cron Jobs:**

```bash
# Edit crontab
sudo crontab -e

# Add Laravel scheduler
* * * * * cd /var/www/cctv_dashboard && php artisan schedule:run >> /dev/null 2>&1
```

### **Nginx Configuration:**

```nginx
server {
    listen 80;
    server_name cctv.your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name cctv.your-domain.com;

    ssl_certificate /etc/ssl/certs/your-cert.pem;
    ssl_certificate_key /etc/ssl/private/your-key.pem;

    root /var/www/cctv_dashboard/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

---

## ✅ POST-DEPLOYMENT CHECKLIST

### **Immediate Checks (0-30 min):**

- [ ] Application accessible via URL
- [ ] SSL certificate valid
- [ ] Login works
- [ ] Dashboard loads
- [ ] No 500 errors in logs
- [ ] Queue workers running (`supervisorctl status`)
- [ ] Cron jobs scheduled (`crontab -l`)
- [ ] Database connection working

### **Functional Tests (30-60 min):**

- [ ] Create test detection via API
- [ ] Verify detection appears in dashboard
- [ ] Test all CRUD operations
- [ ] Test role-based access
- [ ] Test report generation
- [ ] Test file uploads
- [ ] Test exports (CSV)
- [ ] Test search & filter

### **Performance Checks (1-2 hours):**

- [ ] Page load times acceptable
- [ ] API response times < 500ms
- [ ] Database queries optimized
- [ ] No memory leaks
- [ ] Queue processing fast
- [ ] Image processing working

### **Security Verification:**

- [ ] HTTPS working
- [ ] HTTP redirects to HTTPS
- [ ] CSRF protection active
- [ ] API authentication working
- [ ] Unauthorized access blocked
- [ ] File uploads validated
- [ ] SQL injection prevention tested

### **Monitoring Setup:**

- [ ] Application logs monitoring
- [ ] Queue worker logs monitoring
- [ ] Database performance monitoring
- [ ] Error tracking configured
- [ ] Uptime monitoring active
- [ ] Backup verification

---

## 🔄 ROLLBACK PLAN

### **If Deployment Fails:**

```bash
# 1. Put app in maintenance
php artisan down

# 2. Rollback code
git checkout previous_tag
# or
git revert HEAD

# 3. Rollback database (if needed)
php artisan migrate:rollback

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 5. Rebuild
composer install --no-dev
npm run build

# 6. Bring back online
php artisan up
```

### **Database Rollback:**

```bash
# Restore from backup
pg_restore -U postgres -d cctv_dashboard backup.dump

# Or use migration rollback
php artisan migrate:rollback --step=1
```

---

## 📊 HEALTH MONITORING

### **Daily Checks:**

```bash
# Check application status
php artisan about

# Check failed jobs
php artisan queue:failed

# Check disk space
df -h

# Check logs for errors
tail -100 storage/logs/laravel.log | grep ERROR

# Check database size
psql -U postgres -c "SELECT pg_size_pretty(pg_database_size('cctv_dashboard'));"
```

### **Weekly Checks:**

- [ ] Review error logs
- [ ] Check database performance
- [ ] Review API usage
- [ ] Check storage usage
- [ ] Review failed jobs
- [ ] Test backup restoration
- [ ] Update dependencies (if needed)

---

## 🎯 SUCCESS CRITERIA

### **Deployment Successful If:**

- ✅ Application accessible
- ✅ All features working
- ✅ No critical errors
- ✅ Performance acceptable
- ✅ Security verified
- ✅ Monitoring active
- ✅ Backups running
- ✅ Client satisfied

---

**Deployment Checklist Version:** 1.0  
**Last Updated:** October 7, 2025

_End of Deployment Checklist_
