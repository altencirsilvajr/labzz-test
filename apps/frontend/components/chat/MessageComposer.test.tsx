import React from "react";
import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { MessageComposer } from "@/components/chat/MessageComposer";

describe("MessageComposer", () => {
  it("sends message when form is submitted", async () => {
    const onSend = vi.fn(async () => undefined);
    const onTyping = vi.fn();

    render(<MessageComposer disabled={false} onTyping={onTyping} onSend={onSend} />);

    const input = screen.getByLabelText("Message");
    fireEvent.change(input, { target: { value: "hello" } });
    fireEvent.submit(input.closest("form") as HTMLFormElement);

    await waitFor(() => expect(onSend).toHaveBeenCalledWith("hello"));
  });

  it("ignores empty input", async () => {
    const onSend = vi.fn(async () => undefined);
    const onTyping = vi.fn();

    render(<MessageComposer disabled={false} onTyping={onTyping} onSend={onSend} />);

    const input = screen.getByLabelText("Message");
    fireEvent.change(input, { target: { value: "   " } });
    fireEvent.submit(input.closest("form") as HTMLFormElement);

    await waitFor(() => {
      expect(onSend).not.toHaveBeenCalled();
    });
  });
});
