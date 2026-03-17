import { NextRequest } from "next/server";
import { auth0 } from "@/lib/auth0-server";

export async function GET(request: NextRequest) {
  return auth0.middleware(request);
}
