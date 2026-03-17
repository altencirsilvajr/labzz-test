import React from "react";
import { Conversation } from "@/lib/types";
import { ThemeToggle } from "@/components/ui/ThemeToggle";

type Props = {
  conversations: Conversation[];
  activeConversationId: string | null;
  onSelect: (conversation: Conversation) => void;
  onCreate: () => void;
};

export function ConversationList({ conversations, activeConversationId, onSelect, onCreate }: Props) {
  return (
    <aside className="flex h-full w-80 flex-col border-r border-border bg-panel">
      <div className="flex items-center justify-between border-b border-border p-4">
        <h1 className="text-xl font-bold">Labzz</h1>
        <div className="flex items-center gap-2">
          <ThemeToggle />
          <a
            className="rounded-full border border-border bg-canvas px-3 py-1 text-xs font-medium transition hover:bg-accentSoft"
            href="/auth/logout"
            title="Logout"
          >
            Logout
          </a>
        </div>
      </div>

      <div className="p-4">
        <button
          type="button"
          onClick={onCreate}
          className="w-full rounded-xl bg-accent px-4 py-3 text-sm font-semibold text-white transition hover:opacity-90 flex items-center justify-center gap-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            <path d="M5 12h14" />
            <path d="M12 5v14" />
          </svg>
          New Conversation
        </button>
      </div>

      <div className="flex-1 overflow-y-auto px-3 pb-4">
        {conversations.length === 0 ? (
          <div className="mx-2 mt-4 rounded-xl border border-dashed border-border bg-canvas p-4 text-sm text-ink/70">
            <p className="font-medium text-ink">No conversations yet.</p>
            <p className="mt-1">Create one to start your real-time test flow.</p>
            <button
              type="button"
              onClick={onCreate}
              className="mt-3 rounded-lg bg-accent px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90"
            >
              New Conversation
            </button>
          </div>
        ) : (
          <ul className="space-y-1">
            {conversations.map((conversation) => {
              const active = conversation.id === activeConversationId;
              const title = conversation.title ?? "Direct message";
              const initial = title.charAt(0).toUpperCase();

              return (
                <li key={conversation.id}>
                  <button
                    className={`flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition ${active ? "bg-accentSoft" : "hover:bg-canvas"}`}
                    onClick={() => onSelect(conversation)}
                    type="button"
                  >
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-accent text-white font-semibold">
                      {initial}
                    </div>
                    <div className="min-w-0 flex-1">
                      <p className="truncate font-medium text-sm">{title}</p>
                      <p className="truncate text-xs text-ink/60">{new Date(conversation.created_at).toLocaleDateString()} {new Date(conversation.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>
                    </div>
                  </button>
                </li>
              );
            })}
          </ul>
        )}
      </div>
    </aside>
  );
}
