import React, { useEffect, useMemo, useState } from "react";
import { FlatList, Pressable, StyleSheet, Text, TextInput, View } from "react-native";
import { fetchConversations, fetchMessages, sendMessage, type Conversation, type Message } from "@/lib/api";

const TOKEN_PLACEHOLDER = "replace-with-auth0-access-token";

export function ChatScreen() {
  const [conversations, setConversations] = useState<Conversation[]>([]);
  const [activeConversation, setActiveConversation] = useState<Conversation | null>(null);
  const [messages, setMessages] = useState<Message[]>([]);
  const [text, setText] = useState("");

  useEffect(() => {
    fetchConversations(TOKEN_PLACEHOLDER).then((items) => {
      setConversations(items);
      if (items.length > 0) setActiveConversation(items[0]);
    });
  }, []);

  useEffect(() => {
    if (!activeConversation) return;
    fetchMessages(TOKEN_PLACEHOLDER, activeConversation.id).then(setMessages);
  }, [activeConversation]);

  const orderedMessages = useMemo(() => [...messages].reverse(), [messages]);

  const submit = async () => {
    if (!activeConversation || !text.trim()) return;
    await sendMessage(TOKEN_PLACEHOLDER, activeConversation.id, text.trim());
    setText("");
    setMessages(await fetchMessages(TOKEN_PLACEHOLDER, activeConversation.id));
  };

  return (
    <View style={styles.container}>
      <FlatList
        horizontal
        data={conversations}
        keyExtractor={(item) => item.id}
        contentContainerStyle={styles.conversationBar}
        renderItem={({ item }) => (
          <Pressable
            onPress={() => setActiveConversation(item)}
            style={[styles.conversation, activeConversation?.id === item.id ? styles.conversationActive : null]}
          >
            <Text>{item.title ?? "DM"}</Text>
          </Pressable>
        )}
      />

      <FlatList
        data={orderedMessages}
        keyExtractor={(item) => item.id}
        contentContainerStyle={styles.messages}
        renderItem={({ item }) => (
          <View style={styles.messageBubble}>
            <Text>{item.body}</Text>
            <Text style={styles.timestamp}>{new Date(item.created_at).toLocaleString()}</Text>
          </View>
        )}
      />

      <View style={styles.composer}>
        <TextInput style={styles.input} value={text} onChangeText={setText} placeholder="Type a message" />
        <Pressable onPress={submit} style={styles.button}>
          <Text style={styles.buttonText}>Send</Text>
        </Pressable>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, gap: 8 },
  conversationBar: { gap: 8, paddingVertical: 6 },
  conversation: { borderWidth: 1, borderColor: "#d8d4c4", borderRadius: 12, paddingHorizontal: 12, paddingVertical: 8, backgroundColor: "#fffbf0" },
  conversationActive: { borderColor: "#0a8f70", backgroundColor: "#d6f4eb" },
  messages: { gap: 8, paddingBottom: 12 },
  messageBubble: { borderWidth: 1, borderColor: "#d8d4c4", borderRadius: 12, padding: 8, backgroundColor: "#fffbf0" },
  timestamp: { fontSize: 10, color: "#5b6663", marginTop: 4 },
  composer: { flexDirection: "row", gap: 8 },
  input: { flex: 1, borderWidth: 1, borderColor: "#d8d4c4", borderRadius: 12, paddingHorizontal: 10, paddingVertical: 8, backgroundColor: "#ffffff" },
  button: { borderRadius: 12, backgroundColor: "#0a8f70", paddingHorizontal: 14, justifyContent: "center" },
  buttonText: { color: "#ffffff", fontWeight: "600" }
});
