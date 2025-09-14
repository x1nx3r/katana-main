#!/bin/bash

# Database diagnostic script for Coolify deployment
# Run this in Coolify's container terminal or locally

echo "=== WordPress Database Diagnostic ==="
echo ""

# Check if tables exist
echo "1. Checking WordPress tables:"
mysql -u ${DB_USER:-ptka_tana} -p${DB_PASSWORD:-ptka_tana} ${DB_NAME:-ptka_tana} -e "SHOW TABLES LIKE 'wphc_%';" 2>/dev/null | wc -l

echo ""
echo "2. Checking site URL in database:"
mysql -u ${DB_USER:-ptka_tana} -p${DB_PASSWORD:-ptka_tana} ${DB_NAME:-ptka_tana} -e "SELECT option_name, option_value FROM wphc_options WHERE option_name IN ('home', 'siteurl');" 2>/dev/null

echo ""
echo "3. Checking table prefix in use:"
mysql -u ${DB_USER:-ptka_tana} -p${DB_PASSWORD:-ptka_tana} ${DB_NAME:-ptka_tana} -e "SHOW TABLES;" 2>/dev/null | head -5

echo ""
echo "=== End Diagnostic ==="