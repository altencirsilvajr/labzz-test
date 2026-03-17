import { NextRequest, NextResponse } from "next/server";
import { auth0 } from "@/lib/auth0-server";

const SAFE_FALLBACK = "/pt-BR/chat";

function resolveSafeReturnTo(value: string | null): string {
  if (!value) return SAFE_FALLBACK;
  if (value.startsWith("/pt-BR/") || value.startsWith("/en/")) return value;
  return SAFE_FALLBACK;
}

export async function GET(request: NextRequest) {
  const requested = request.nextUrl.searchParams.get("returnTo");
  const safeReturnTo = resolveSafeReturnTo(requested);

  if (requested !== safeReturnTo) {
    const url = new URL(request.url);
    url.searchParams.set("returnTo", safeReturnTo);
    return NextResponse.redirect(url);
  }

  return auth0.middleware(request);
}
