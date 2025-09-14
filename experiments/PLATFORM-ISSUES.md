# COOLIFY PLATFORM ISSUES & WORKAROUNDS

## Problems Encountered
1. **Volume mounts don't work as expected** - Coolify handles volumes differently than standard Docker
2. **Build context limitations** - Can't reliably COPY local files
3. **Environment variable quirks** - Some variables get overridden by platform
4. **Network isolation issues** - Platform networking differs from docker-compose

## Current Workarounds
- GitHub download in Dockerfile (diabolical but functional)
- Custom PHP performance tuning
- Platform-specific volume handling

## Better Alternatives (for future projects)
- VPS + Docker Compose + Caddy
- Cost: $10/month vs platform fees
- Reliability: Standard Docker behavior
- Debugging: Full SSH access
- Scalability: Add domains by editing Caddyfile

## Migration Path (when politics allow)
1. Set up VPS with Docker + Caddy
2. Export WordPress database
3. rsync wp-content directory
4. Point DNS to new server
5. Decommission Coolify deployment

## Estimated time savings: 80% less debugging, 90% less platform-specific code