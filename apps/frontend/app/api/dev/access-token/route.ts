import { NextResponse } from "next/server";
import { auth0 } from "@/lib/auth0-server";

export async function GET() {
  if (process.env.NODE_ENV !== "development") {
    return NextResponse.json({ error: "Not found" }, { status: 404 });
  }

  try {
    const session = await auth0.getSession();
    if (!session) {
      return NextResponse.json({ error: "Not authenticated" }, { status: 401 });
    }

    const tokenResult = await auth0.getAccessToken();
    const accessToken = tokenResult?.token ?? "";

    if (!accessToken) {
      return NextResponse.json({ error: "Access token unavailable" }, { status: 401 });
    }

    return NextResponse.json({ access_token: accessToken });
  } catch (error) {
    return NextResponse.json({ error: error instanceof Error ? error.message : "Failed to get access token" }, { status: 500 });
  }
}
