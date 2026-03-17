export type Conversation = {
  id: string;
  type: "dm" | "group";
  title: string | null;
  created_at: string;
};

export type Message = {
  id: string;
  conversation_id: string;
  sender_id: string;
  body: string;
  created_at: string;
};

export type CursorResponse<T> = {
  data: T[];
  next_cursor: string | null;
};

export type RealtimeEvent = {
  event: "message.created" | "typing.updated" | "presence.updated" | "llm.response";
  payload: Record<string, unknown>;
};
