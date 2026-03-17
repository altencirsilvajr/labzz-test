import { redirect } from "next/navigation";
import { setRequestLocale } from "next-intl/server";
import { auth0 } from "@/lib/auth0-server";

export default async function LocaleHome({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);

  const session = await auth0.getSession();
  if (!session) {
    redirect(`/login?returnTo=/${locale}/chat`);
  }

  redirect(`/${locale}/chat`);
}
