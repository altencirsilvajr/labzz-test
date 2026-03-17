import { expect, test } from "@playwright/test";

test("chat page renders core shell", async ({ page }) => {
  await page.goto("/pt-BR/chat");
  await expect(page.getByText("Labzz Real-time Chat")).toBeVisible();
  await expect(page.getByRole("button", { name: /New Conversation/i })).toBeVisible();
});
