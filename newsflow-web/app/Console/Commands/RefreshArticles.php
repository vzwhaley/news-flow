<?php

namespace App\Console\Commands;

use App\Models\Topic;
use App\Services\Articles\TopicRefresher;
use Illuminate\Console\Command;

/**
 * Daily article refresh. Scours configured sources for fresh, popular stories
 * for every topic and applies the "keep 12, prepend new, drop oldest" rule.
 *
 * Scheduled for 06:00 (see routes/console.php). Can also be run on demand:
 *
 *   php artisan newsflow:refresh                 # every topic
 *   php artisan newsflow:refresh --topic=42      # one topic by id
 *   php artisan newsflow:refresh --user=7        # all of one user's topics
 */
class RefreshArticles extends Command
{
    protected $signature = 'newsflow:refresh
        {--topic= : Refresh only this topic id}
        {--user= : Refresh only this user\'s topics}';

    protected $description = 'Fetch the latest popular articles for topics and keep each feed full.';

    public function handle(TopicRefresher $refresher): int
    {
        $query = Topic::query();

        if ($id = $this->option('topic')) {
            $query->whereKey($id);
        }

        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
        }

        $topics = $query->get();

        if ($topics->isEmpty()) {
            $this->info('No topics to refresh.');

            return self::SUCCESS;
        }

        $this->info("Refreshing {$topics->count()} topic(s)...");

        $totalAdded = 0;

        foreach ($topics as $topic) {
            try {
                $stats = $refresher->refresh($topic);
                $totalAdded += $stats['added'];

                $this->line(sprintf(
                    '  • %-28s +%d new, -%d old, %d total',
                    \Illuminate\Support\Str::limit($stats['topic'], 26),
                    $stats['added'],
                    $stats['dropped'],
                    $stats['total'],
                ));
            } catch (\Throwable $e) {
                $this->error("  • {$topic->name}: {$e->getMessage()}");
                report($e);
            }
        }

        $this->info("Done. {$totalAdded} new article(s) added across all topics.");

        return self::SUCCESS;
    }
}
