import { Auth0Client } from "@auth0/nextjs-auth0/server";

function resolveAuth0Domain(): string | undefined {
  const direct = process.env.AUTH0_DOMAIN;
  if (direct) return direct;

  const issuer = process.env.AUTH0_ISSUER_BASE_URL;
  if (!issuer) return undefined;

  try {
    return new URL(issuer).host;
  } catch {
    return undefined;
  }
}

export const auth0 = new Auth0Client({
  domain: resolveAuth0Domain(),
  clientId: process.env.AUTH0_CLIENT_ID,
  clientSecret: process.env.AUTH0_CLIENT_SECRET,
  secret: process.env.AUTH0_SECRET,
  appBaseUrl: process.env.AUTH0_BASE_URL,
  authorizationParameters: {
    audience: process.env.AUTH0_AUDIENCE
  }
});

