export type Conversation = { id: string; title: string | null };
export type Message = { id: string; body: string; created_at: string };

const API_URL = process.env.EXPO_PUBLIC_API_URL ?? "http://localhost:8080";

export async function fetchConversations(token: string): Promise<Conversation[]> {
  const response = await fetch(`${API_URL}/v1/conversations`, {
    headers: { authorization: `Bearer ${token}` }
  });
  if (!response.ok) return [];
  const payload = await response.json();
  return payload.data ?? [];
}

export async function fetchMessages(token: string, conversationId: string): Promise<Message[]> {
  const response = await fetch(`${API_URL}/v1/conversations/${conversationId}/messages?limit=30`, {
    headers: { authorization: `Bearer ${token}` }
  });
  if (!response.ok) return [];
  const payload = await response.json();
  return payload.data ?? [];
}

export async function sendMessage(token: string, conversationId: string, body: string): Promise<void> {
  await fetch(`${API_URL}/v1/messages`, {
    method: "POST",
    headers: { authorization: `Bearer ${token}`, "content-type": "application/json" },
    body: JSON.stringify({ conversation_id: conversationId, body })
  });
}
