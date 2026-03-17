import { StatusBar } from "expo-status-bar";
import React from "react";
import { SafeAreaView, StyleSheet, Text, View } from "react-native";
import { ChatScreen } from "./src/screens/ChatScreen";

export default function App() {
  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>Labzz Mobile Chat</Text>
        <Text style={styles.subtitle}>Scaffold</Text>
      </View>
      <ChatScreen />
      <StatusBar style="auto" />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: "#f5f4ef", padding: 16 },
  header: { marginBottom: 12 },
  title: { fontSize: 24, fontWeight: "700", color: "#16211f" },
  subtitle: { fontSize: 12, color: "#4e5a58" }
});
