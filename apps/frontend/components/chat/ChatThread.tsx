import React, { useEffect, useRef } from "react";
import { Message } from "@/lib/types";

export function ChatThread({ messages }: { messages: Message[] }) {
  const endRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    endRef.current?.scrollIntoView({ behavior: "smooth" });
  }, [messages]);

  return (
    <section className="flex-1 overflow-y-auto p-6" aria-live="polite">
      <ul className="space-y-6">
        {messages.map((message) => {
          const isAssistant = message.sender_id === "assistant-bot";
          
          return (
            <li 
              key={message.id} 
              className={`flex ${isAssistant ? "justify-start" : "justify-end"}`}
            >
              <div 
                className={`max-w-[80%] rounded-2xl px-4 py-3 shadow-sm ${
                  isAssistant 
                    ? "rounded-tl-sm bg-panel border border-border text-ink" 
                    : "rounded-tr-sm bg-accent text-white"
                }`}
              >
                <p className="text-sm leading-relaxed whitespace-pre-wrap">{message.body}</p>
                <p className={`mt-1 text-[10px] ${isAssistant ? "text-ink/50" : "text-white/70"} text-right`}>
                  {new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                </p>
              </div>
            </li>
          );
        })}
        <div ref={endRef} />
      </ul>
    </section>
  );
}
