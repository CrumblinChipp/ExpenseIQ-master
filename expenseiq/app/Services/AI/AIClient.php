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
 * Feature classes (ExpenseCategorizer, ReceiptParser, ...) call structured().
 */
class AiClient
{
    public const LITE  = 'lite';   // Flash-Lite: categorization, simple parsing (higher free quota)
    public const FLASH = 'flash';  // Flash: insights, Q&A (smarter, lower free quota)
    public const RECEIPT = 'receipt'; // Reading receipt photos.

    private string $apiKey;
    private string $baseUrl;
    private int $imageKb = 0; // size of the images in the current call, for the logs

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
     * @param ?string $thinkingLevel Gemini 3 models "think" before answering. Unset = deep thinking (slow).
     *                              'minimal' or 'low' makes extraction/classification much faster.
     * @param ?int   $timeout      Seconds to wait for Gemini (default: services.gemini.timeout).
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
        ?string $thinkingLevel = null,
        ?int $timeout = null,
    ): array {
        $model = $this->modelFor($tier);
        $this->imageKb = (int) round(collect($images)->sum(fn ($image) => strlen($image['data'])) / 1024);

        if ($this->apiKey === '') {
            $this->fail('GEMINI_API_KEY is not set in .env.', 'config', $model, $feature);
        }

        $parts = [['text' => $user]];
        foreach ($images as $image) {
            $parts[] = ['inlineData' => [
                'mimeType' => $image['mime_type'],
                'data'     => $image['data'],
            ]];
        }

        $payload = [
            'systemInstruction' => ['parts' => [['text' => $system]]],
            'contents'          => [['role' => 'user', 'parts' => $parts]],
            'generationConfig'  => [
                'temperature'        => $temperature,
                'responseMimeType'   => 'application/json',
                'responseJsonSchema' => $schema,
            ],
        ];

        if ($thinkingLevel !== null) {
            $payload['generationConfig']['thinkingConfig'] = ['thinkingLevel' => $thinkingLevel];
        }

        $started = microtime(true);

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
                ->acceptJson()
                ->asJson()
                ->connectTimeout(10)
                ->timeout($timeout ?? (int) config('services.gemini.timeout', 30))
                // Retry only on network errors and temporary server errors.
                // 429 (quota) is NOT retried: on the free tier it usually means "come back later".
                ->retry([500, 1500], 0, function ($exception) {
                    if ($exception instanceof ConnectionException) {
                        // Retry dropped connections, but never timeouts:
                        // retrying a slow call only makes the user wait longer.
                        return ! str_contains($exception->getMessage(), 'timed out');
                    }

                    return $exception instanceof RequestException
                        && in_array($exception->response->status(), [500, 502, 503, 504], true);
                }, throw: false)
                ->post("{$this->baseUrl}/models/{$model}:generateContent", $payload);
        } catch (ConnectionException $e) {
            $this->fail('Could not reach Gemini: ' . $e->getMessage(), 'connection', $model, $feature, $e);
        }

        $json = $response->json() ?? [];

        // Some models reject a thinking level. Retry once without it instead of failing.
        if (
            $response->status() === 400
            && $thinkingLevel !== null
            && str_contains(strtolower((string) data_get($json, 'error.message', '')), 'thinking')
        ) {
            Log::warning('ai.thinking_unsupported', [
                'model' => $model,
                'level' => $thinkingLevel,
                'message' => data_get($json, 'error.message'),
            ]);

            return $this->structured($system, $user, $schema, $tier, $temperature, $images, $feature, null, $timeout);
        }

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

        // Join the text parts, skipping any "thinking" parts the model may include.
        $text = collect(data_get($json, 'candidates.0.content.parts', []))
            ->reject(fn ($part) => ! empty($part['thought']))
            ->pluck('text')
            ->filter()
            ->implode('');

        if ($text === '') {
            $finish = data_get($json, 'candidates.0.finishReason', 'unknown');
            $this->fail("Gemini returned no text (finishReason: {$finish}).", 'empty', $model, $feature);
        }

        try {
            $data = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $this->fail('Gemini returned invalid JSON.', 'invalid_json', $model, $feature, $e);
        }

        if (! is_array($data)) {
            $this->fail('Gemini returned JSON that is not an object/array.', 'invalid_json', $model, $feature);
        }

        // Log usage, never the prompt or response text (expense data is private).
        Log::info('ai.request', [
            'feature'       => $feature,
            'model'         => $model,
            'latency_ms'    => (int) ((microtime(true) - $started) * 1000),
            'image_kb'      => $this->imageKb,
            'input_tokens'  => data_get($json, 'usageMetadata.promptTokenCount'),
            'output_tokens' => data_get($json, 'usageMetadata.candidatesTokenCount'),
        ]);

        return $data;
    }

    private function modelFor(string $tier): string
    {
        return match ($tier) {
            self::FLASH => (string) config('services.gemini.model_flash'),
            self::RECEIPT => (string) (config('services.gemini.model_receipt') ?: config('services.gemini.model_lite')),
            default     => (string) config('services.gemini.model_lite'),
        };
    }

    private function fail(string $message, string $reason, string $model, string $feature, ?Throwable $previous = null): never
    {
        Log::warning('ai.failure', compact('feature', 'model', 'reason') + ['image_kb' => $this->imageKb, 'message' => $message]);

        throw new AiException($message, $reason, $previous);
    }
}