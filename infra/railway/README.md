# Railway Deployment Layout

Create services from this monorepo:

1. `backend-api`
   - Root directory: `apps/backend`
   - Start: `php -S 0.0.0.0:$PORT -t public public/index.php`
2. `backend-ws`
   - Root directory: `apps/backend`
   - Start: `php bin/ws-server.php`
3. `backend-worker`
   - Root directory: `apps/backend`
   - Start: `php bin/worker-search-index.php`
4. `frontend`
   - Root directory: `apps/frontend`
   - Start: `npm run start`

Provision managed MySQL and Redis in Railway, and add an Elasticsearch service using the Elastic Docker image.
