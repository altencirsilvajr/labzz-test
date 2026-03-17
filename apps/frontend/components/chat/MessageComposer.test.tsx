import React from "react";
import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { MessageComposer } from "@/components/chat/MessageComposer";

describe("MessageComposer", () => {
  it("sends message when form is submitted", async () => {
    const onSend = vi.fn(async () => undefined);
    const onTyping = vi.fn();
    const onLlmAsk = vi.fn(async () => undefined);

    render(<MessageComposer disabled={false} onTyping={onTyping} onSend={onSend} onLlmAsk={onLlmAsk} />);

    const input = screen.getByLabelText("Message");
    fireEvent.change(input, { target: { value: "hello" } });
    fireEvent.submit(input.closest("form") as HTMLFormElement);

    await waitFor(() => expect(onSend).toHaveBeenCalledWith("hello"));
  });

  it("asks assistant and clears input", async () => {
    const onSend = vi.fn(async () => undefined);
    const onTyping = vi.fn();
    const onLlmAsk = vi.fn(async () => undefined);

    render(<MessageComposer disabled={false} onTyping={onTyping} onSend={onSend} onLlmAsk={onLlmAsk} />);

    const input = screen.getByLabelText("Message") as HTMLInputElement;
    fireEvent.change(input, { target: { value: "summarize" } });
    fireEvent.click(screen.getByRole("button", { name: "Ask AI" }));

    await waitFor(() => expect(onLlmAsk).toHaveBeenCalledWith("summarize"));
    expect(input.value).toBe("");
  });

  it("ignores empty input", async () => {
    const onSend = vi.fn(async () => undefined);
    const onTyping = vi.fn();
    const onLlmAsk = vi.fn(async () => undefined);

    render(<MessageComposer disabled={false} onTyping={onTyping} onSend={onSend} onLlmAsk={onLlmAsk} />);

    const input = screen.getByLabelText("Message");
    fireEvent.change(input, { target: { value: "   " } });
    fireEvent.submit(input.closest("form") as HTMLFormElement);
    fireEvent.click(screen.getByRole("button", { name: "Ask AI" }));

    await waitFor(() => {
      expect(onSend).not.toHaveBeenCalled();
      expect(onLlmAsk).not.toHaveBeenCalled();
    });
  });
});
