"use client";

import React, { useCallback, useMemo, useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { ConversationList } from "@/components/chat/ConversationList";
import { ChatThread } from "@/components/chat/ChatThread";
import { MessageComposer } from "@/components/chat/MessageComposer";
import { TypingIndicator } from "@/components/chat/TypingIndicator";
import { HeroLottie } from "@/components/ui/HeroLottie";
import { http } from "@/lib/http";
import { Conversation, CursorResponse, Message, RealtimeEvent } from "@/lib/types";
import { useRealtime } from "@/lib/useRealtime";

export function ChatShell() {
  const [activeConversation, setActiveConversation] = useState<Conversation | null>(null);
  const [typingVisible, setTypingVisible] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const conversationsQuery = useQuery({
    queryKey: ["conversations"],
    queryFn: async () => {
      const response = await http.get<{ data: Conversation[] }>("/v1/conversations");
      return response.data;
    }
  });

  const messagesQuery = useQuery({
    queryKey: ["messages", activeConversation?.id],
    enabled: Boolean(activeConversation?.id),
    queryFn: async () => {
      const response = await http.get<CursorResponse<Message>>(
        `/v1/conversations/${activeConversation?.id}/messages?limit=30`
      );
      return response.data;
    }
  });

  const onRealtimeEvent = useCallback(
    (event: RealtimeEvent) => {
      if (event.event === "typing.updated") {
        setTypingVisible(true);
        window.setTimeout(() => setTypingVisible(false), 1500);
      }
      if (event.event === "message.created" || event.event === "llm.response") {
        messagesQuery.refetch();
      }
    },
    [messagesQuery.refetch]
  );

  const { connected, sendTyping, notifyMessageSent } = useRealtime(activeConversation?.id ?? null, onRealtimeEvent);

  const conversations = useMemo(() => {
    const base = conversationsQuery.data ?? [];
    if (!activeConversation) return base;
    return base.some((item) => item.id === activeConversation.id) ? base : [activeConversation, ...base];
  }, [conversationsQuery.data, activeConversation]);

  const messages = useMemo(() => (messagesQuery.data ? [...messagesQuery.data].reverse() : []), [messagesQuery.data]);

  const createConversation = async () => {
    setErrorMessage(null);
    const title = `General ${new Date().toLocaleString()}`;
    try {
      const response = await http.post<{ data: Conversation }>("/v1/conversations", {
        type: "group",
        title,
        members: []
      });

      const created = response.data;
      await conversationsQuery.refetch();
      setActiveConversation(created);
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "Unable to create conversation.");
    }
  };

  const sendMessage = async (text: string) => {
    if (!activeConversation) return;
    setErrorMessage(null);
    try {
      await http.post("/v1/messages", { conversation_id: activeConversation.id, body: text });
      notifyMessageSent(activeConversation.id);
      await messagesQuery.refetch();
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "Unable to send message.");
    }
  };

  const askAssistant = async (prompt: string) => {
    if (!activeConversation) return;
    setErrorMessage(null);
    try {
      await http.post("/v1/llm/reply", { conversation_id: activeConversation.id, prompt });
      await messagesQuery.refetch();
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "Ask AI failed.");
    }
  };

  const deleteConversation = async () => {
    if (!activeConversation) return;
    setErrorMessage(null);
    try {
      await http.delete(`/v1/conversations/${activeConversation.id}`);
      setActiveConversation(null);
      await conversationsQuery.refetch();
    } catch (error) {
      setErrorMessage(error instanceof Error ? error.message : "Unable to delete conversation.");
    }
  };

  return (
    <div className="flex h-full w-full overflow-hidden">
      <ConversationList
        conversations={conversations}
        activeConversationId={activeConversation?.id ?? null}
        onSelect={setActiveConversation}
        onCreate={createConversation}
      />

      <div className="relative flex h-full flex-1 flex-col bg-canvas">
        {activeConversation ? (
          <>
            <header className="flex items-center justify-between border-b border-border bg-panel px-6 py-4">
              <div>
                <h2 className="text-lg font-semibold">{activeConversation.title ?? "Direct message"}</h2>
                <div className="flex items-center gap-2 text-xs text-ink/60">
                  <span className={`h-2 w-2 rounded-full ${connected ? "bg-green-500" : "bg-red-500"}`}></span>
                  <span>{connected ? "Connected" : "Disconnected"}</span>
                </div>
              </div>
              <button
                type="button"
                onClick={deleteConversation}
                className="rounded-lg border border-red-300 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100"
              >
                Delete chat
              </button>
            </header>

            {errorMessage ? (
              <div className="mx-4 mt-3 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                {errorMessage}
              </div>
            ) : null}

            <ChatThread messages={messages} />

            <div className="border-t border-border bg-panel p-4">
              <TypingIndicator visible={typingVisible} />
              <MessageComposer disabled={!activeConversation} onTyping={sendTyping} onSend={sendMessage} onLlmAsk={askAssistant} />
            </div>
          </>
        ) : (
          <div className="flex h-full flex-col items-center justify-center px-6 text-center">
            <div className="w-64">
              <HeroLottie />
            </div>
            <h2 className="mt-6 text-2xl font-semibold">Labzz Real-time Chat</h2>
            <p className="mt-2 text-sm text-ink/70">Siga o checklist para validar o fluxo completo em duas abas.</p>
            <ol className="mt-6 max-w-xl list-decimal space-y-2 pl-5 text-left text-sm text-ink/80">
              <li>Clique em <strong>New Conversation</strong> para criar uma conversa.</li>
              <li>Abra uma segunda aba com a mesma conta e selecione a conversa.</li>
              <li>Digite em uma aba e veja o indicador de <strong>typing</strong> na outra.</li>
              <li>Envie uma mensagem e valide atualização automática entre abas.</li>
            </ol>
            <button
              type="button"
              onClick={createConversation}
              className="mt-6 rounded-xl bg-accent px-5 py-3 text-sm font-semibold text-white transition hover:opacity-90"
            >
              New Conversation
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
