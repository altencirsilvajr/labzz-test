function readCsrfFromCookie(): string | null {
  if (typeof document === "undefined") return null;
  const match = document.cookie.match(/(?:^|; )csrf_token=([^;]+)/);
  return match ? decodeURIComponent(match[1]) : null;
}

export async function ensureCsrfToken(): Promise<string> {
  const existing = readCsrfFromCookie();
  if (existing) return existing;

  const response = await fetch("/api/csrf", {
    method: "GET",
    credentials: "include",
    cache: "no-store",
    headers: { "cache-control": "no-store" }
  });

  if (!response.ok) {
    throw new Error("Unable to initialize CSRF token.");
  }

  const payload = (await response.json()) as { token: string };
  return payload.token;
}
