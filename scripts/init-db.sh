#!/bin/bash

# Database initialization script for Docker
# This script runs when the MariaDB container starts for the first time

echo "Starting database initialization..."

# Wait for MySQL to be ready
until mysql -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" -e "SELECT 1" >/dev/null 2>&1; do
    echo "Waiting for MySQL to be ready..."
    sleep 2
done

echo "MySQL is ready. Database initialization complete."