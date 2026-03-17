import { NextIntlClientProvider } from "next-intl";
import { getMessages, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { AppProviders } from "@/components/ui/AppProviders";

const locales = ["pt-BR", "en"];

export default async function LocaleLayout({
  children,
  params
}: {
  children: React.ReactNode;
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;

  if (!locales.includes(locale)) {
    notFound();
  }

  setRequestLocale(locale);

  const messages = await getMessages();

  return (
    <NextIntlClientProvider messages={messages}>
      <AppProviders>
        <main className="flex h-screen w-screen flex-col overflow-hidden bg-canvas text-ink">
          {children}
        </main>
      </AppProviders>
    </NextIntlClientProvider>
  );
}
