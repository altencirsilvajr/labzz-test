"use client";

import React, { FormEvent, useState } from "react";

type Props = {
  disabled: boolean;
  onTyping: (active: boolean) => void;
  onSend: (text: string) => Promise<void>;
  onLlmAsk: (prompt: string) => Promise<void>;
};

export function MessageComposer({ disabled, onTyping, onSend, onLlmAsk }: Props) {
  const [value, setValue] = useState("");
  const [loading, setLoading] = useState(false);

  const submit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const trimmed = value.trim();
    if (!trimmed || loading) return;

    setLoading(true);
    try {
      await onSend(trimmed);
      setValue("");
      onTyping(false);
    } finally {
      setLoading(false);
    }
  };

  const askAssistant = async () => {
    const trimmed = value.trim();
    if (!trimmed || loading) return;

    setLoading(true);
    try {
      await onLlmAsk(trimmed);
      setValue("");
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={submit} className="flex items-center gap-2 mt-2">
      <label htmlFor="composer" className="sr-only">Message</label>
      <div className="flex-1 relative">
        <input
          id="composer"
          value={value}
          onChange={(event) => {
            setValue(event.target.value);
            onTyping(event.target.value.trim().length > 0);
          }}
          disabled={disabled || loading}
          className="w-full rounded-full border-none bg-canvas px-5 py-3 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-accent/50"
          placeholder="Type a message..."
          autoComplete="off"
        />
      </div>
      <button 
        type="button" 
        onClick={askAssistant} 
        disabled={disabled || loading} 
        className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-accentSoft text-accent transition hover:bg-accent hover:text-white disabled:opacity-50"
        title="Ask AI"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M12 2v20"/>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
      </button>
      <button 
        type="submit" 
        disabled={disabled || loading || !value.trim()} 
        className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-accent text-white transition hover:opacity-90 disabled:opacity-50"
        title="Send"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="translate-x-0.5">
          <path d="m22 2-7 20-4-9-9-4Z"/>
          <path d="M22 2 11 13"/>
        </svg>
      </button>
    </form>
  );
}
