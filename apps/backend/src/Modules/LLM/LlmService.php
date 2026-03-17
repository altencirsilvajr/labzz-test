<?php

declare(strict_types=1);

namespace App\Modules\LLM;

use GuzzleHttp\Client;
use RuntimeException;

final class LlmService
{
    /** @param array<string, mixed> $settings */
    public function __construct(private readonly array $settings)
    {
    }

    public function isEnabled(): bool
    {
        return (bool) ($this->settings['enabled'] ?? false);
    }

    public function generateReply(string $prompt): string
    {
        if (!$this->isEnabled()) {
            throw new RuntimeException('LLM integration is disabled.');
        }

        $client = new Client(['base_uri' => $this->settings['api_url'], 'timeout' => 20]);

        $response = $client->post('', [
            'headers' => [
                'authorization' => 'Bearer ' . $this->settings['api_key'],
                'content-type' => 'application/json',
            ],
            'json' => [
                'model' => $this->settings['model'],
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant inside a chat app. Keep responses concise and safe.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.4,
            ],
        ]);

        $decoded = json_decode((string) $response->getBody(), true);
        $content = $decoded['choices'][0]['message']['content'] ?? null;

        if (!is_string($content) || trim($content) === '') {
            throw new RuntimeException('LLM provider returned an invalid response.');
        }

        return trim($content);
    }
}
