#!/bin/bash
# coolify-deploy.sh - Deploy script that works around Coolify quirks

# Set Coolify-friendly environment variables
export COOLIFY_APP_NAME="ptkatana"
export COOLIFY_DOMAIN="ptkatana.com"
export COOLIFY_BRANCH="main"
export COOLIFY_BUILD_PACK="docker"

# Force Coolify to use our custom build context
export DOCKER_BUILDKIT=1
export BUILDKIT_PROGRESS=plain

# Deploy with environment overrides
curl -X POST "https://your-coolify-instance.com/api/deploy" \
  -H "Authorization: Bearer $COOLIFY_TOKEN" \
  -d '{
    "project": "ptkatana",
    "environment": {
      "WORDPRESS_DB_HOST": "db",
      "WORDPRESS_DB_NAME": "ptka_tana",
      "WP_MEMORY_LIMIT": "256M",
      "WP_MAX_EXECUTION_TIME": "120"
    },
    "build_args": {
      "GITHUB_REPO": "https://github.com/x1nx3r/katana-main.git"
    }
  }'