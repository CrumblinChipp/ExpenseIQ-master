<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Services\AI\ExpenseCategorizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExpenseCategorizerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.gemini.key' => 'test-key']);
        Cache::flush();
    }

    private function categories()
    {
        return collect([
            (new Category)->forceFill(['id' => 1, 'category_name' => 'Food']),
            (new Category)->forceFill(['id' => 2, 'category_name' => 'Transport']),
            (new Category)->forceFill(['id' => 3, 'category_name' => 'Bills']),
        ]);
    }

    /** Builds a response shaped like Gemini's generateContent reply. */
    private function geminiReply(array $payload): array
    {
        return [
            'candidates'    => [['content' => ['parts' => [['text' => json_encode($payload)]]]]],
            'usageMetadata' => ['promptTokenCount' => 100, 'candidatesTokenCount' => 10],
        ];
    }

    public function test_it_suggests_a_category_and_caches_the_answer(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(
                $this->geminiReply(['category' => 'Food', 'confidence' => 0.93])
            ),
        ]);

        $categorizer = app(ExpenseCategorizer::class);

        $first  = $categorizer->suggest(1, 'Jollibee lunch', $this->categories());
        $second = $categorizer->suggest(1, '  JOLLIBEE   lunch ', $this->categories()); // same text after normalizing

        $this->assertSame(1, $first['category_id']);
        $this->assertSame('ai', $first['source']);
        $this->assertSame($first, $second);
        Http::assertSentCount(1); // second call came from cache
    }

    public function test_low_confidence_returns_null(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(
                $this->geminiReply(['category' => 'Bills', 'confidence' => 0.3])
            ),
        ]);

        $this->assertNull(app(ExpenseCategorizer::class)->suggest(1, 'misc stuff', $this->categories()));
    }

    public function test_quota_error_returns_null_instead_of_crashing(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'quota exceeded']], 429),
        ]);

        $this->assertNull(app(ExpenseCategorizer::class)->suggest(1, 'Grab ride to work', $this->categories()));
    }
}