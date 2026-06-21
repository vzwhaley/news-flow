<?php

namespace App\Http\Controllers;

use App\Contracts\ArticleProvider;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public, clickable "World News" demo — a live example of exactly what a topic
 * feed looks like for a signed-in user. Uses the same ArticleProvider the app
 * uses (real headlines when a news key is configured, the realistic stub feed
 * otherwise), cached so the marketing page never hammers the upstream API.
 */
class WorldNewsController extends Controller
{
    private const CACHE_KEY = 'demo:world-news';

    public function show(ArticleProvider $provider): Response
    {
        $articles = Cache::get(self::CACHE_KEY);

        if ($articles === null) {
            $articles = $this->fetch($provider);

            // Only cache a non-empty result, so a transient upstream failure
            // doesn't stick an empty feed for the next hour.
            if (! empty($articles)) {
                Cache::put(self::CACHE_KEY, $articles, now()->addHour());
            }
        }

        return Inertia::render('WorldNews', [
            'topic'    => 'World News',
            'articles' => $articles,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetch(ArticleProvider $provider): array
    {
        $count = (int) config('billing.articles_per_topic', 12);

        try {
            return collect($provider->fetch('World News', $count))
                ->take($count)
                ->map(fn ($a) => [
                    'headline'     => $a->headline,
                    'description'  => $a->description,
                    'url'          => $a->url,
                    'source'       => $a->source,
                    'published_at' => $a->publishedAt?->toIso8601String(),
                ])
                ->values()
                ->all();
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }
}
