import { cookies } from "next/headers";
import { NextResponse } from "next/server";
import { randomUUID } from "crypto";

export async function GET() {
  const cookieStore = await cookies();
  const existing = cookieStore.get("csrf_token")?.value;

  if (existing) {
    return NextResponse.json(
      { token: existing },
      { headers: { "cache-control": "no-store, no-cache, must-revalidate" } }
    );
  }

  const token = `${randomUUID()}-${randomUUID()}`;
  const response = NextResponse.json(
    { token },
    { headers: { "cache-control": "no-store, no-cache, must-revalidate" } }
  );

  response.cookies.set("csrf_token", token, {
    httpOnly: false,
    sameSite: "lax",
    secure: process.env.NODE_ENV === "production",
    path: "/",
    maxAge: 60 * 60
  });

  return response;
}
