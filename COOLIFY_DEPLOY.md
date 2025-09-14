# Coolify Deployment Guide

## Environment Variables to Set in Coolify

When deploying to Coolify, set these environment variables in your application settings:

### Required Variables:
```
DB_NAME=ptka_tana_prod
DB_USER=ptka_user_prod  
DB_PASSWORD=GENERATE_SECURE_PASSWORD_HERE
DB_ROOT_PASSWORD=GENERATE_SECURE_ROOT_PASSWORD_HERE
```

### Optional Variables:
```
WORDPRESS_DEBUG=false
FORCE_SSL_ADMIN=true
```

## Coolify Configuration Steps:

1. **Create New Application:**
   - Type: Docker Compose
   - Repository: Your Git repo with this code
   - Branch: main

2. **Set Environment Variables:**
   - Go to Environment tab
   - Add the variables listed above
   - Generate strong passwords for production

3. **Domain Setup:**
   - Add your domain in Domains tab
   - Enable SSL/TLS (Let's Encrypt)
   - Coolify will automatically handle reverse proxy

4. **Deploy:**
   - Click Deploy button
   - Monitor logs for successful deployment
   - Database will auto-import on first run

## Notes:
- No manual port configuration needed (Coolify handles this)
- No container names needed (Coolify manages them)
- Database is internal only (secure by default)
- WordPress files persist in named volumes
- SSL termination handled by Coolify's Traefik proxy