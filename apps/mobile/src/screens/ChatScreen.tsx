import React, { useCallback, useEffect, useMemo, useState } from "react";
import { FlatList, Pressable, StyleSheet, Text, TextInput, View } from "react-native";
import { createConversation, fetchConversations, fetchMessages, sendMessage, type Conversation, type Message } from "@/lib/api";

export function ChatScreen() {
  const [token, setToken] = useState(process.env.EXPO_PUBLIC_AUTH_TOKEN ?? "");
  const [conversations, setConversations] = useState<Conversation[]>([]);
  const [activeConversation, setActiveConversation] = useState<Conversation | null>(null);
  const [messages, setMessages] = useState<Message[]>([]);
  const [text, setText] = useState("");
  const [error, setError] = useState<string | null>(null);

  const hasToken = token.trim().length > 0;

  const loadConversations = useCallback(async () => {
    if (!hasToken) {
      setError("Informe um Access Token Auth0 para testar no Expo Go.");
      setConversations([]);
      setActiveConversation(null);
      return;
    }

    setError(null);
    const items = await fetchConversations(token.trim());
    setConversations(items);
    setActiveConversation((prev) => (prev ? items.find((item) => item.id === prev.id) ?? (items[0] ?? null) : items[0] ?? null));
  }, [hasToken, token]);

  useEffect(() => {
    if (!hasToken) return;
    loadConversations();
  }, [hasToken, loadConversations]);

  useEffect(() => {
    if (!activeConversation || !hasToken) return;
    fetchMessages(token.trim(), activeConversation.id).then(setMessages);
  }, [activeConversation, hasToken, token]);

  const orderedMessages = useMemo(() => [...messages].reverse(), [messages]);

  const submit = async () => {
    if (!activeConversation || !text.trim() || !hasToken) return;
    await sendMessage(token.trim(), activeConversation.id, text.trim());
    setText("");
    setMessages(await fetchMessages(token.trim(), activeConversation.id));
  };

  const createFirstConversation = async () => {
    if (!hasToken) return;
    const created = await createConversation(token.trim(), `Mobile ${new Date().toLocaleString()}`);
    if (!created) {
      setError("Não foi possível criar conversa. Verifique token/API.");
      return;
    }
    await loadConversations();
    setActiveConversation(created);
  };

  return (
    <View style={styles.container}>
      <View style={styles.tokenBox}>
        <Text style={styles.tokenLabel}>Auth0 Access Token</Text>
        <TextInput
          style={styles.tokenInput}
          value={token}
          onChangeText={setToken}
          autoCapitalize="none"
          autoCorrect={false}
          placeholder="Cole aqui o bearer token da API"
          multiline
        />
        <Pressable onPress={loadConversations} style={styles.reloadButton}>
          <Text style={styles.reloadButtonText}>Atualizar conversas</Text>
        </Pressable>
      </View>

      {error ? <Text style={styles.errorText}>{error}</Text> : null}

      {!hasToken ? (
        <View style={styles.placeholderCard}>
          <Text style={styles.placeholderTitle}>Pronto para testar</Text>
          <Text style={styles.placeholderText}>
            Cole um token válido e toque em "Atualizar conversas". Depois disso, a lista de conversas e o composer serão habilitados.
          </Text>
        </View>
      ) : (
        <>
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
            ListEmptyComponent={
              <View style={styles.emptyWrap}>
                <Text style={styles.emptyText}>Nenhuma conversa disponível para este usuário.</Text>
                <Pressable onPress={createFirstConversation} style={styles.reloadButton}>
                  <Text style={styles.reloadButtonText}>Criar primeira conversa</Text>
                </Pressable>
              </View>
            }
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
            ListEmptyComponent={<Text style={styles.emptyText}>Sem mensagens nesta conversa.</Text>}
          />

          <View style={styles.composer}>
            <TextInput style={styles.input} value={text} onChangeText={setText} placeholder="Type a message" />
            <Pressable onPress={submit} style={styles.button}>
              <Text style={styles.buttonText}>Send</Text>
            </Pressable>
          </View>
        </>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, gap: 8 },
  tokenBox: { gap: 6, padding: 10, borderRadius: 12, borderWidth: 1, borderColor: "#d8d4c4", backgroundColor: "#fffbf0" },
  tokenLabel: { fontSize: 12, fontWeight: "600", color: "#4e5a58" },
  tokenInput: { borderWidth: 1, borderColor: "#d8d4c4", borderRadius: 10, paddingHorizontal: 10, paddingVertical: 8, backgroundColor: "#fff", fontSize: 12, minHeight: 44 },
  reloadButton: { alignSelf: "flex-start", borderRadius: 10, backgroundColor: "#0a8f70", paddingHorizontal: 10, paddingVertical: 8 },
  reloadButtonText: { color: "#fff", fontSize: 12, fontWeight: "600" },
  errorText: { fontSize: 12, color: "#b42318" },
  placeholderCard: { borderWidth: 1, borderColor: "#d8d4c4", borderRadius: 12, backgroundColor: "#fff", padding: 12 },
  placeholderTitle: { fontSize: 14, fontWeight: "700", color: "#16211f" },
  placeholderText: { marginTop: 6, fontSize: 12, color: "#4e5a58" },
  emptyWrap: { gap: 8, paddingVertical: 8 },
  emptyText: { fontSize: 12, color: "#5b6663" },
  conversationBar: { gap: 8, paddingVertical: 6 },
  conversation: { borderWidth: 1, borderColor: "#d8d4c4", borderRadius: 12, paddingHorizontal: 12, paddingVertical: 8, backgroundColor: "#fffbf0" },
  conversationActive: { borderColor: "#0a8f70", backgroundColor: "#d6f4eb" },
  messages: { gap: 8, paddingBottom: 12, flexGrow: 1 },
  messageBubble: { borderWidth: 1, borderColor: "#d8d4c4", borderRadius: 12, padding: 8, backgroundColor: "#fffbf0" },
  timestamp: { fontSize: 10, color: "#5b6663", marginTop: 4 },
  composer: { flexDirection: "row", gap: 8 },
  input: { flex: 1, borderWidth: 1, borderColor: "#d8d4c4", borderRadius: 12, paddingHorizontal: 10, paddingVertical: 8, backgroundColor: "#ffffff" },
  button: { borderRadius: 12, backgroundColor: "#0a8f70", paddingHorizontal: 14, justifyContent: "center" },
  buttonText: { color: "#ffffff", fontWeight: "600" }
});
