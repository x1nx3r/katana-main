# PTKATANA WordPress Deployment Guide

## 🐳 Local Development with Docker

### Prerequisites
- Docker and Docker Compose installed on your system
- Git (for version control)

### Quick Start - Local Development

1. **Clone/Navigate to the project directory:**
   ```bash
   cd /home/x1nx3r/temp/ptkatana
   ```

2. **Create environment file:**
   ```bash
   cp .env.example .env
   # Edit .env file with your preferred passwords
   ```

3. **Start the development environment:**
   ```bash
   docker-compose up -d
   ```

4. **Access your WordPress site:**
   - **Website:** http://localhost:8080

5. **Stop the environment:**
   ```bash
   docker-compose down
   ```

### Services Included

- **WordPress 6.8.2** (PHP 8.0 + Apache)
- **MariaDB 10.11** (MySQL-compatible database)

## 🚀 Coolify Deployment

### Step 1: Prepare Your Repository

1. **Push to Git repository:**
   ```bash
   git init
   git add .
   git commit -m "Initial WordPress setup for Coolify"
   git remote add origin YOUR_REPO_URL
   git push -u origin main
   ```

### Step 2: Configure Coolify Application

1. **Create New Application in Coolify:**
   - Choose "Docker Compose" application type
   - Connect your Git repository
   - Set branch to `main`

2. **Environment Variables in Coolify:**
   ```
   DB_NAME=ptka_tana_prod
   DB_USER=ptka_user_prod
   DB_PASSWORD=GENERATE_SECURE_PASSWORD
   DB_ROOT_PASSWORD=GENERATE_SECURE_ROOT_PASSWORD
   WORDPRESS_DEBUG=false
   FORCE_SSL_ADMIN=true
   ```

3. **Domain Configuration:**
   - Add your domain in Coolify's Domains tab
   - Enable SSL/TLS (Let's Encrypt)
   - Coolify automatically handles reverse proxy (no port configuration needed)

### Step 3: Deploy

1. **Deploy from Coolify dashboard:**
   - Click "Deploy" button
   - Monitor deployment logs
   - Wait for all containers to be healthy

2. **First-time setup:**
   - Visit your domain
   - WordPress should load with existing data
   - Login with existing admin credentials from your database

## 📁 Project Structure

```
ptkatana/
├── docker-compose.yml          # Docker services configuration
├── Dockerfile                  # Custom WordPress image
├── docker-wp-config.php        # Docker-optimized WordPress config
├── .env.example                # Environment variables template
├── .gitignore                  # Git ignore file
├── ptka_tana.sql              # Database dump (auto-imported)
├── public_html/               # WordPress files
│   ├── wp-content/           # Themes, plugins, uploads
│   ├── wp-config.php         # Original WordPress config
│   └── ...                   # WordPress core files
└── scripts/
    └── init-db.sh            # Database initialization script
```

## 🔧 Configuration Details

### WordPress Configuration
- **Version:** 6.8.2
- **PHP:** 8.0 with necessary extensions
- **Web Server:** Apache with mod_rewrite enabled
- **Table Prefix:** `wphc_`

### Active Plugins
- Auxin Elements & Portfolio
- Akismet Anti-spam
- Contact Form 7
- Depicter Slider
- Elementor Page Builder
- LiteSpeed Cache
- WP ULike
- WP File Manager

### Database
- **Engine:** MariaDB 10.11
- **Charset:** utf8mb4_unicode_520_ci
- **Auto-import:** SQL file imported on first container start

## 🛠️ Troubleshooting

### Local Development Issues

1. **Port conflicts:**
   ```bash
   # Change ports in docker-compose.yml if 8080/8081 are in use
   ports:
     - "8090:80"  # Change from 8080 to 8090
   ```

2. **Permission issues:**
   ```bash
   # Fix file permissions
   docker-compose exec wordpress chown -R www-data:www-data /var/www/html
   ```

3. **Database connection issues:**
   ```bash
   # Check database logs
   docker-compose logs db
   ```

### Coolify Deployment Issues

1. **Environment variables not loading:**
   - Verify variables are set in Coolify dashboard
   - Check container logs for error messages

2. **SSL/Domain issues:**
   - Ensure domain DNS points to Coolify server
   - Check Coolify proxy configuration

3. **Database import issues:**
   - Monitor database container logs during first deployment
   - SQL file should auto-import on first container start

## 📊 Performance Optimization

### For Production (Coolify)
- Enable LiteSpeed Cache plugin
- Configure object caching if available
- Use CDN for static assets
- Monitor resource usage in Coolify

### Resource Requirements
- **CPU:** 1-2 cores minimum
- **RAM:** 2GB minimum (4GB recommended)
- **Storage:** 10GB minimum for WordPress + Database

## 🔐 Security Considerations

1. **Update default passwords** in `.env` file
2. **Enable HTTPS** in production (Coolify handles this)
3. **Keep WordPress and plugins updated**
4. **Regular database backups** (Coolify can automate this)
5. **Monitor security logs**

## 📝 Maintenance

### Regular Tasks
- Update WordPress core and plugins
- Monitor database size and optimize
- Check security logs
- Backup database and uploads
- Monitor performance metrics

### Coolify-Specific
- Monitor deployment logs
- Check resource usage dashboards
- Update environment variables as needed
- Scale resources based on traffic