<?php

namespace Tests\Feature;

use App\Contracts\ArticleProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakeArticleProvider;
use Tests\TestCase;

class WorldNewsDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_world_news_demo_is_public_and_lists_articles(): void
    {
        $this->app->instance(ArticleProvider::class, new FakeArticleProvider(12));

        $this->get('/world-news')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('WorldNews')
                ->where('topic', 'World News')
                ->has('articles', 12)
                ->has('articles.0', fn ($a) => $a
                    ->has('headline')
                    ->has('url')
                    ->has('source')
                    ->etc()
                )
            );
    }

    public function test_world_news_demo_caches_the_feed(): void
    {
        // First request populates the cache; a subsequent request must not hit
        // the provider again (so the marketing page can't hammer the API).
        $provider = new FakeArticleProvider(12);
        $this->app->instance(ArticleProvider::class, $provider);

        $this->get('/world-news')->assertOk()->assertInertia(fn ($page) => $page->has('articles', 12));

        // Swap in a provider that would throw if called — the cache should serve.
        $this->app->instance(ArticleProvider::class, new class implements ArticleProvider {
            public function fetch(string $topic, int $count, array $excludeFingerprints = []): array
            {
                throw new \RuntimeException('provider should not be called when cached');
            }
        });

        $this->get('/world-news')->assertOk()->assertInertia(fn ($page) => $page->has('articles', 12));
    }
}
