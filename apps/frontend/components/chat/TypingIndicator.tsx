import React from "react";

export function TypingIndicator({ visible }: { visible: boolean }) {
  if (!visible) return null;
  return <p className="text-xs text-ink/70">Someone is typing...</p>;
}
