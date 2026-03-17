import type { Config } from "tailwindcss";

const config: Config = {
  darkMode: ["class"],
  content: ["./app/**/*.{ts,tsx}", "./components/**/*.{ts,tsx}", "./lib/**/*.{ts,tsx}"],
  theme: {
    extend: {
      colors: {
        canvas: "var(--color-canvas)",
        panel: "var(--color-panel)",
        ink: "var(--color-ink)",
        accent: "var(--color-accent)",
        accentSoft: "var(--color-accent-soft)",
        border: "var(--color-border)"
      }
    }
  },
  plugins: []
};

export default config;
