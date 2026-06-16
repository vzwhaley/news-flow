<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * A single candidate article returned by an ArticleProvider, before it is
 * persisted into a topic's feed.
 */
class FetchedArticle
{
    public function __construct(
        public string $headline,
        public string $description,
        public string $url,
        public ?string $source = null,
        public ?string $imageUrl = null,
        public ?CarbonInterface $publishedAt = null,
        public float $popularityScore = 0.0,
    ) {
    }

    /**
     * Stable fingerprint used to dedupe the same story across refreshes and
     * across sources. Based on the canonical URL (host + path, no query),
     * falling back to a slug of the headline.
     */
    public function fingerprint(): string
    {
        $canonical = $this->canonicalUrl();

        if ($canonical !== '') {
            return hash('sha256', $canonical);
        }

        return hash('sha256', Str::slug($this->headline));
    }

    private function canonicalUrl(): string
    {
        $url = trim($this->url);

        if ($url === '') {
            return '';
        }

        $parts = parse_url($url);

        if ($parts === false || empty($parts['host'])) {
            return '';
        }

        $host = strtolower(preg_replace('/^www\./', '', $parts['host']));
        $path = rtrim($parts['path'] ?? '', '/');

        return $host.$path;
    }

    public function toArray(): array
    {
        return [
            'headline'         => $this->headline,
            'description'      => $this->description,
            'url'              => $this->url,
            'source'           => $this->source,
            'image_url'        => $this->imageUrl,
            'fingerprint'      => $this->fingerprint(),
            'popularity_score' => $this->popularityScore,
            'published_at'     => $this->publishedAt,
        ];
    }
}
