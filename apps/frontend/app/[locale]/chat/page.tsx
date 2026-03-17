import { redirect } from "next/navigation";
import { setRequestLocale } from "next-intl/server";
import { ChatShell } from "@/components/chat/ChatShell";
import { auth0 } from "@/lib/auth0-server";

export default async function ChatPage({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;

  const session = await auth0.getSession();
  if (!session) {
    redirect(`/login?returnTo=/${locale}/chat`);
  }

  setRequestLocale(locale);
  return <ChatShell />;
}
