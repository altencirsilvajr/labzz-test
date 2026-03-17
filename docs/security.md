# Security Design

## Authentication

- OAuth2 Authorization Code + PKCE handled with Auth0.
- MFA enforced by Auth0 tenant policy (TOTP + recovery codes).
- Backend validates JWT via Auth0 JWKS and audience checks.

## API Hardening

- SQL: parameterized statements via Doctrine DBAL.
- CSRF: double-submit token in Next.js BFF routes.
- XSS: no HTML rendering from message payloads, CSP and safe text rendering.
- Rate limiting: Redis per identity minute bucket.

## Encryption

- Sensitive field (`phone`) encrypted with libsodium secretbox.
- Encryption key loaded from `ENCRYPTION_KEY_BASE64`.

## Logging and Privacy

- Structured JSON logs with request correlation IDs.
- Avoid logging secrets and full bearer tokens.
