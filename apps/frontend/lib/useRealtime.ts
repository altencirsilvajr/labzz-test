"use client";

import React, { useEffect, useRef, useState } from "react";
import { http } from "@/lib/http";
import { RealtimeEvent } from "@/lib/types";

export function useRealtime(conversationId: string | null, onEvent: (event: RealtimeEvent) => void) {
  const [connected, setConnected] = useState(false);
  const socketRef = useRef<WebSocket | null>(null);
  const onEventRef = useRef(onEvent);

  useEffect(() => {
    onEventRef.current = onEvent;
  }, [onEvent]);

  useEffect(() => {
    if (!conversationId) {
      socketRef.current?.close();
      socketRef.current = null;
      return;
    }

    let cancelled = false;

    (async () => {
      const tokenResponse = await http.post<{ data: { token: string } }>("/v1/ws/token", {});
      if (cancelled) return;

      const wsUrl = `${process.env.NEXT_PUBLIC_WS_URL}?token=${tokenResponse.data.token}`;
      const socket = new WebSocket(wsUrl);
      socketRef.current = socket;

      socket.onopen = () => {
        setConnected(true);
        socket.send(JSON.stringify({ event: "subscribe", payload: { conversation_id: conversationId } }));
      };

      socket.onmessage = (message) => {
        const decoded = JSON.parse(message.data) as RealtimeEvent;
        onEventRef.current(decoded);
      };

      socket.onclose = () => setConnected(false);
      socket.onerror = () => setConnected(false);
    })().catch(() => setConnected(false));

    return () => {
      cancelled = true;
      socketRef.current?.close();
      socketRef.current = null;
    };
  }, [conversationId]);

  const sendEvent = (event: string, payload: Record<string, unknown>) => {
    if (!socketRef.current || !connected) return;
    socketRef.current.send(JSON.stringify({ event, payload }));
  };

  const sendTyping = (active: boolean) => {
    if (!conversationId) return;
    sendEvent(active ? "typing.start" : "typing.stop", { conversation_id: conversationId });
  };

  const notifyMessageSent = (conversationIdToNotify: string) => {
    sendEvent("message.send", { conversation_id: conversationIdToNotify });
  };

  return { connected, sendTyping, sendEvent, notifyMessageSent };
}
