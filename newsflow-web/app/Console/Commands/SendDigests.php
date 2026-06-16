<?php

namespace App\Console\Commands;

use App\Mail\DailyDigest;
use App\Models\User;
use App\Services\RefreshWindow;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the "Your NewsFlow is ready" daily digest to users who opted in and
 * are due this hour (in their own timezone). Scheduled hourly, a few minutes
 * after the refresh so the digest contains the morning's fresh stories.
 *
 *   php artisan newsflow:digest --due     # scheduled
 *   php artisan newsflow:digest --user=7  # one user, ignoring schedule
 */
class SendDigests extends Command
{
    protected $signature = 'newsflow:digest
        {--due : Only send to users due this hour (their timezone)}
        {--user= : Send to a single user id regardless of schedule}
        {--articles=4 : How many articles per topic to include}';

    protected $description = 'Email the daily digest to opted-in users.';

    public function handle(): int
    {
        $perTopic = max(1, (int) $this->option('articles'));

        $users = $this->recipients();

        if ($users->isEmpty()) {
            $this->info('No digest recipients this run.');

            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($users as $user) {
            $topics = $user->topics()
                ->with(['articles' => fn ($q) => $q->orderBy('position')->limit($perTopic)])
                ->orderBy('position')
                ->get()
                ->filter(fn ($t) => $t->articles->isNotEmpty())
                ->map(fn ($t) => ['name' => $t->name, 'articles' => $t->articles])
                ->values()
                ->all();

            if (empty($topics)) {
                continue; // nothing to send yet
            }

            try {
                Mail::to($user->email)->send(new DailyDigest($user, $topics));
                $user->forceFill(['digest_sent_at' => Carbon::now()])->save();
                $sent++;
                $this->line("  • sent to {$user->email}");
            } catch (\Throwable $e) {
                $this->error("  • {$user->email}: {$e->getMessage()}");
                report($e);
            }
        }

        $this->info("Done. {$sent} digest(s) sent.");

        return self::SUCCESS;
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    private function recipients()
    {
        $query = User::query()->where('digest_enabled', true)->whereHas('topics');

        if ($userId = $this->option('user')) {
            return $query->whereKey($userId)->get();
        }

        if ($this->option('due')) {
            return $query->whereIn('id', RefreshWindow::dueUserIds())->get();
        }

        return $query->get();
    }
}
