<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Article Provider
    |--------------------------------------------------------------------------
    |
    | Which implementation of App\Contracts\ArticleProvider is used to
    | scour the internet for a topic's top stories. Options:
    |
    |   'hybrid' — News API for fresh coverage + social engagement signals
    |              for popularity ranking + an LLM to summarize, dedupe and
    |              guarantee a full set of articles. (Recommended.)
    |   'stub'   — Generates realistic placeholder articles with no network
    |              calls. Used for local development and tests so the whole
    |              app is clickable before any API keys are configured.
    |
    | The hybrid provider automatically falls back to the stub provider for
    | any source that is not yet configured, so the app always returns a
    | full feed.
    |
    */

    'provider' => env('NEWSFLOW_PROVIDER', 'hybrid'),

    /*
    |--------------------------------------------------------------------------
    | News aggregator APIs (fresh-coverage layer)
    |--------------------------------------------------------------------------
    |
    | The hybrid provider queries whichever of these has a key configured,
    | in order, merging and de-duplicating the results.
    |
    */

    'sources' => [
        'newsapi' => [
            'key'      => env('NEWSAPI_KEY'),
            'endpoint' => 'https://newsapi.org/v2/everything',
        ],
        'gnews' => [
            'key'      => env('GNEWS_KEY'),
            'endpoint' => 'https://gnews.io/api/v4/search',
        ],
        'newsdata' => [
            'key'      => env('NEWSDATA_KEY'),
            'endpoint' => 'https://newsdata.io/api/1/news',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Popularity signals (ranking layer)
    |--------------------------------------------------------------------------
    |
    | Public engagement signals used to approximate "most read / most
    | popular". True page-view counts are private to publishers, so we
    | blend these proxies into a popularity score.
    |
    */

    'signals' => [
        'reddit'      => env('NEWSFLOW_SIGNAL_REDDIT', true),
        'hacker_news' => env('NEWSFLOW_SIGNAL_HN', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | LLM summarizer (summarize / dedupe / fill layer)
    |--------------------------------------------------------------------------
    |
    | Claude writes the headline + brief description for each article,
    | removes near-duplicate stories, and keeps widening the search until
    | a full set of articles is found (important for niche topics that may
    | not have 12 fresh stories on any given day).
    |
    */

    'llm' => [
        'enabled' => (bool) env('ANTHROPIC_API_KEY'),
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model'   => env('NEWSFLOW_LLM_MODEL', 'claude-sonnet-4-6'),
        'endpoint' => 'https://api.anthropic.com/v1/messages',
    ],

    /*
    |--------------------------------------------------------------------------
    | Refresh behaviour
    |--------------------------------------------------------------------------
    */

    // How many fresh candidates to gather before ranking down to the final
    // set. Higher = better "most popular" accuracy, more API cost.
    'candidate_pool' => 40,

    // Daily refresh hour (local app timezone). The scheduled command runs
    // at this time; users on Pro can also trigger an on-demand refresh.
    'refresh_hour' => 6,
];
