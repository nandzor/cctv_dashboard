#!/bin/bash

# CCTV Dashboard - Quick Setup Script
# Usage: ./setup.sh

echo "=========================================="
echo "CCTV Dashboard - Quick Setup"
echo "=========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Check requirements
echo -e "${BLUE}Checking requirements...${NC}"

# Check PHP
if ! command -v php &> /dev/null; then
    echo -e "${RED}✗ PHP is not installed${NC}"
    exit 1
fi
echo -e "${GREEN}✓ PHP $(php -v | head -n 1 | cut -d ' ' -f 2)${NC}"

# Check Composer
if ! command -v composer &> /dev/null; then
    echo -e "${RED}✗ Composer is not installed${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Composer installed${NC}"

# Check Node.js
if ! command -v node &> /dev/null; then
    echo -e "${RED}✗ Node.js is not installed${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Node.js $(node -v)${NC}"

# Check PostgreSQL
if ! command -v psql &> /dev/null; then
    echo -e "${YELLOW}⚠ PostgreSQL client not found (install if needed)${NC}"
else
    echo -e "${GREEN}✓ PostgreSQL client installed${NC}"
fi

echo ""

# Install Composer dependencies
echo -e "${YELLOW}→ Installing Composer dependencies...${NC}"
composer install

# Install NPM dependencies
echo -e "${YELLOW}→ Installing NPM dependencies...${NC}"
npm install

# Copy .env.example if .env doesn't exist
if [ ! -f .env ]; then
    echo -e "${YELLOW}→ Creating .env file...${NC}"
    if [ -f .env.example ]; then
        cp .env.example .env
        echo -e "${GREEN}✓ .env created from .env.example${NC}"
    else
        echo -e "${RED}✗ .env.example not found${NC}"
    fi
else
    echo -e "${GREEN}✓ .env file already exists${NC}"
fi

# Generate application key
echo -e "${YELLOW}→ Generating application key...${NC}"
php artisan key:generate

# Create storage directories
echo -e "${YELLOW}→ Creating storage directories...${NC}"
mkdir -p storage/app/logs/api_requests
mkdir -p storage/app/logs/whatsapp_messages
mkdir -p storage/app/events
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Set permissions
echo -e "${YELLOW}→ Setting permissions...${NC}"
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Database setup
echo ""
echo -e "${BLUE}=========================================="
echo "Database Setup"
echo -e "==========================================${NC}"
echo ""

read -p "Have you created the database and configured .env? (y/n) " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}→ Running migrations...${NC}"
    php artisan migrate

    read -p "Do you want to seed the database with test data? (y/n) " -n 1 -r
    echo ""

    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}→ Seeding database...${NC}"
        php artisan db:seed
    fi
else
    echo -e "${YELLOW}⚠ Skipping database setup${NC}"
    echo ""
    echo "To setup database manually:"
    echo "  1. Create database: createdb cctv_dashboard"
    echo "  2. Configure .env with database credentials"
    echo "  3. Run: php artisan migrate"
    echo "  4. Run: php artisan db:seed"
    echo ""
fi

# Build assets
echo ""
echo -e "${YELLOW}→ Building frontend assets...${NC}"
npm run build

# Create storage link
echo -e "${YELLOW}→ Creating storage link...${NC}"
php artisan storage:link

echo ""
echo "=========================================="
echo -e "${GREEN}✓ Setup Complete!${NC}"
echo "=========================================="
echo ""
echo "To start the application:"
echo "  1. Start development server: ${BLUE}php artisan serve${NC}"
echo "  2. Start queue worker: ${BLUE}php artisan queue:work${NC}"
echo "  3. Visit: ${BLUE}http://localhost:8000${NC}"
echo ""
echo "Default credentials (if seeded):"
echo "  Admin: ${GREEN}admin@cctv.com / admin123${NC}"
echo "  User: ${GREEN}operator.jakarta@cctv.com / password${NC}"
echo ""
echo "API Testing:"
echo "  Key: ${GREEN}cctv_test_dev_key${NC}"
echo "  Secret: ${GREEN}secret_test_dev_2024${NC}"
echo "  Script: ${BLUE}./test_detection_api.sh${NC}"
echo ""
echo "Documentation:"
echo "  - SETUP_GUIDE.md - Complete installation guide"
echo "  - API_DETECTION_DOCUMENTATION.md - API reference"
echo "  - README.md - Project overview"
echo ""

