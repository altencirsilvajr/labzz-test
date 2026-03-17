import "./globals.css";
import type { Metadata } from "next";

export const metadata: Metadata = {
  title: "Labzz Chat",
  description: "Secure real-time chat with multilingual UX"
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html suppressHydrationWarning>
      <body>{children}</body>
    </html>
  );
}
