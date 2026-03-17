import Link from "next/link";

const ptHref = "/auth/login?returnTo=/pt-BR/chat";
const enHref = "/auth/login?returnTo=/en/chat";

export default function LoginPage() {
  return (
    <main className="mx-auto flex min-h-screen w-full max-w-3xl flex-col justify-center gap-8 px-6 py-10 text-ink">
      <section className="rounded-2xl border border-border bg-panel p-8 shadow-sm">
        <h1 className="text-3xl font-bold">Labzz Chat</h1>
        <p className="mt-3 text-sm text-ink/70">Faça login para continuar e testar o fluxo real-time.</p>

        <div className="mt-6 grid gap-3 sm:grid-cols-2">
          <Link
            href={ptHref}
            className="rounded-xl bg-accent px-4 py-3 text-center text-sm font-semibold text-white transition hover:opacity-90"
          >
            Entrar e continuar em Português
          </Link>
          <Link
            href={enHref}
            className="rounded-xl border border-border bg-canvas px-4 py-3 text-center text-sm font-semibold transition hover:bg-accentSoft"
          >
            Sign in and continue in English
          </Link>
        </div>
      </section>

      <section className="rounded-2xl border border-border bg-panel p-8">
        <h2 className="text-xl font-semibold">Como testar o chat em tempo real</h2>
        <ol className="mt-4 list-decimal space-y-2 pl-5 text-sm text-ink/80">
          <li>Faça login usando um dos botões acima.</li>
          <li>Clique em <strong>New Conversation</strong> para criar uma conversa.</li>
          <li>Abra uma segunda aba com a mesma conta em <strong>/pt-BR/chat</strong> ou <strong>/en/chat</strong>.</li>
          <li>Selecione a mesma conversa nas duas abas.</li>
          <li>Digite em uma aba para ver o indicador de typing na outra.</li>
          <li>Envie uma mensagem e valide atualização automática.</li>
        </ol>
      </section>
    </main>
  );
}
