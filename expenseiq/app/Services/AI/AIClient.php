<?php

namespace App\Services\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use JsonException;
use Throwable;

/**
 * The only class in the app that talks to Gemini.
 * Feature classes call structured() (JSON answers) or chat() (plain-text conversation).
 *
 * NOTE: this file replaces app/Services/AI/AIClient.php. The class is named AiClient,
 * so the file name must match exactly (Linux/Docker autoloading is case-sensitive).
 */
class AiClient
{
    public const LITE  = 'lite';   // Flash-Lite: categorization, simple parsing (higher free quota)
    public const FLASH = 'flash';  // Flash: receipts, insights, chat (smarter, lower free quota)

    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = (string) config('services.gemini.key');
        $this->baseUrl = rtrim((string) config('services.gemini.base_url'), '/');
    }

    /**
     * Ask the model for JSON that matches $schema and return it as an array.
     *
     * @param string $system       Instructions (role, rules).
     * @param string $user         The actual input (keep it small, use fake data in testing).
     * @param array  $schema       JSON Schema the response must follow.
     * @param string $tier         self::LITE or self::FLASH.
     * @param float  $temperature  Low = consistent answers (good for categorization).
     * @param array  $images       Optional: [['mime_type' => 'image/jpeg', 'data' => '<base64>'], ...]
     * @param string $feature      Label for logs, e.g. 'categorize', 'receipt', 'insight'.
     *
     * @throws AiException
     */
    public function structured(
        string $system,
        string $user,
        array $schema,
        string $tier = self::LITE,
        float $temperature = 0.1,
        array $images = [],
        string $feature = 'unspecified',
    ): array {
        $model = $this->modelFor($tier);

        $parts = [['text' => $user]];
        foreach ($images as $image) {
            $parts[] = ['inlineData' => [
                'mimeType' => $image['mime_type'],
                'data'     => $image['data'],
            ]];
        }

        $json = $this->send($model, $feature, [
            'systemInstruction' => ['parts' => [['text' => $system]]],
            'contents'          => [['role' => 'user', 'parts' => $parts]],
            'generationConfig'  => [
                'temperature'        => $temperature,
                'responseMimeType'   => 'application/json',
                'responseJsonSchema' => $schema,
            ],
        ]);

        $text = $this->textFrom($json, $model, $feature);

        try {
            $data = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->fail('Gemini returned invalid JSON.', 'invalid_json', $model, $feature, $e);
        }

        if (! is_array($data)) {
            $this->fail('Gemini returned JSON that is not an object/array.', 'invalid_json', $model, $feature);
        }

        return $data;
    }

    /**
     * Plain-text conversation. Returns the assistant's reply.
     *
     * @param string $system    Instructions + any data the assistant may use.
     * @param array  $messages  [['role' => 'user'|'assistant', 'content' => '...'], ...] oldest first,
     *                          starting with a 'user' message.
     *
     * @throws AiException
     */
    public function chat(
        string $system,
        array $messages,
        string $tier = self::FLASH,
        float $temperature = 0.3,
        string $feature = 'chat',
    ): string {
        $model = $this->modelFor($tier);

        $contents = array_map(fn (array $m) => [
            'role'  => $m['role'] === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => (string) $m['content']]],
        ], array_values($messages));

        $json = $this->send($model, $feature, [
            'systemInstruction' => ['parts' => [['text' => $system]]],
            'contents'          => $contents,
            'generationConfig'  => ['temperature' => $temperature],
        ], timeout: 20, retryDelays: []); // worst case Flash 20s + Flash-Lite 20s, under PHP's 60s limit

        return trim($this->textFrom($json, $model, $feature));
    }

    /**
     * One HTTP call to generateContent with the app's error handling. Returns the decoded response.
     *
     * @throws AiException
     */
    private function send(
        string $model,
        string $feature,
        array $payload,
        int $timeout = 0,                 // 0 = use config('services.gemini.timeout')
        array $retryDelays = [500, 1500], // ms between attempts; [] = no retries
    ): array
    {
        if ($this->apiKey === '') {
            $this->fail('GEMINI_API_KEY is not set in .env.', 'config', $model, $feature);
        }

        $started = microtime(true);

        $request = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
            ->acceptJson()
            ->asJson()
            ->connectTimeout(5) // a dead/blocked connection fails in 5s instead of hanging
            ->timeout($timeout > 0 ? $timeout : (int) config('services.gemini.timeout', 30));

        // Retry only on network errors and temporary server errors.
        // 429 (quota) is NOT retried: on the free tier it usually means "come back later".
        if ($retryDelays !== []) {
            $request = $request->retry($retryDelays, 0, function ($exception) {
                if ($exception instanceof ConnectionException) {
                    return true;
                }

                return $exception instanceof RequestException
                    && in_array($exception->response->status(), [500, 502, 503, 504], true);
            }, throw: false);
        }

        try {
            $response = $request->post("{$this->baseUrl}/models/{$model}:generateContent", $payload);
        } catch (ConnectionException $e) {
            $this->fail('Could not reach Gemini: ' . $e->getMessage(), 'connection', $model, $feature, $e);
        }

        $json = $response->json() ?? [];

        if ($response->status() === 429) {
            $this->fail(
                'Gemini quota reached: ' . data_get($json, 'error.message', 'too many requests'),
                'rate_limited', $model, $feature
            );
        }

        if ($response->failed()) {
            $this->fail(
                'Gemini error ' . $response->status() . ': ' . data_get($json, 'error.message', 'unknown error'),
                'http_error', $model, $feature
            );
        }

        if ($block = data_get($json, 'promptFeedback.blockReason')) {
            $this->fail("Request blocked by Gemini ({$block}).", 'blocked', $model, $feature);
        }

        // Log usage, never the prompt or response text (expense data is private).
        Log::info('ai.request', [
            'feature'       => $feature,
            'model'         => $model,
            'latency_ms'    => (int) ((microtime(true) - $started) * 1000),
            'input_tokens'  => data_get($json, 'usageMetadata.promptTokenCount'),
            'output_tokens' => data_get($json, 'usageMetadata.candidatesTokenCount'),
        ]);

        return $json;
    }

    /** Join the text parts of the first candidate, skipping any "thinking" parts. */
    private function textFrom(array $json, string $model, string $feature): string
    {
        $text = collect(data_get($json, 'candidates.0.content.parts', []))
            ->reject(fn ($part) => ! empty($part['thought']))
            ->pluck('text')
            ->filter()
            ->implode('');

        if ($text === '') {
            $finish = data_get($json, 'candidates.0.finishReason', 'unknown');
            $this->fail("Gemini returned no text (finishReason: {$finish}).", 'empty', $model, $feature);
        }

        return $text;
    }

    private function modelFor(string $tier): string
    {
        return match ($tier) {
            self::FLASH => (string) config('services.gemini.model_flash'),
            default     => (string) config('services.gemini.model_lite'),
        };
    }

    private function fail(string $message, string $reason, string $model, string $feature, ?Throwable $previous = null): never
    {
        Log::warning('ai.failure', compact('feature', 'model', 'reason') + ['message' => $message]);

        throw new AiException($message, $reason, $previous);
    }
}