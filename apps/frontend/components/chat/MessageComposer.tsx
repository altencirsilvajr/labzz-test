"use client";

import React, { FormEvent, useState } from "react";

type Props = {
  disabled: boolean;
  onTyping: (active: boolean) => void;
  onSend: (text: string) => Promise<void>;
};

export function MessageComposer({ disabled, onTyping, onSend }: Props) {
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
