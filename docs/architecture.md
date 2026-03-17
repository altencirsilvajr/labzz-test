# Architecture Overview

## Backend Monolith (PHP 8.3)

- HTTP entrypoint: `public/index.php`
- Kernel: FastRoute dispatch + PSR request/response + middleware chain
- Modules:
  - `Auth`: JWT verification, current-user resolution
  - `Users`: CRUD + encrypted profile field
  - `Conversations`: DM/group membership and listing
  - `Messages`: message create/list with keyset cursor
  - `Search`: Elasticsearch query with SQL fallback
  - `Realtime`: WebSocket token issuance
  - `LLM`: opt-in AI response flow

## Data Flow

1. User authenticates via Auth0 on frontend.
2. Frontend BFF routes proxy requests to backend with bearer token.
3. Backend validates JWT (JWKS), applies RBAC and rate limits.
4. Message writes persist in MySQL.
5. Message event is pushed to Redis stream (`search-index`) and pub/sub channel.
6. Worker consumes stream and indexes documents into Elasticsearch.
7. WebSocket server distributes realtime typing/message events.

## Storage

- MySQL: source of truth
- Redis: rate limiting, ephemeral typing, queues/pubsub
- Elasticsearch: advanced search index (`users_v1`, `messages_v1`)

## Frontend

- Next.js App Router
- i18n with `next-intl` (PT-BR + EN)
- React Query for data caching
- Lottie animations with reduced-motion fallback
- Dark mode via CSS variables and local preference

## Mobile (Bonus)

- Expo scaffold for chat list/thread composer
- API reuse with bearer token
- Realtime hook path prepared for WS integration
