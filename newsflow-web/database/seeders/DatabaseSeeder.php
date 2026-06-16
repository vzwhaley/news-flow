<?php

namespace Database\Seeders;

use App\Models\Topic;
use App\Models\User;
use App\Services\Articles\TopicRefresher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed a clickable demo: one Free user (capped at 2 topics) and one Pro
     * (Lifetime) user with several topics. Every topic is filled with a full
     * 12-article feed through the real refresh pipeline.
     */
    public function run(): void
    {
        $refresher = app(TopicRefresher::class);

        // --- Free demo user (2-topic cap) ---
        $free = User::factory()->create([
            'name'              => 'Demo Free',
            'email'             => 'free@newsflow.test',
            'password'          => Hash::make('password'),
            'email_verified_at' => Carbon::now(),
        ]);

        $this->seedTopics($free, ['World News', 'Technology'], $refresher);

        // --- Pro (Lifetime) demo user (unlimited) ---
        $pro = User::factory()->create([
            'name'                  => 'Demo Pro',
            'email'                 => 'pro@newsflow.test',
            'password'              => Hash::make('password'),
            'email_verified_at'     => Carbon::now(),
            'lifetime_purchased_at' => Carbon::now(),
        ]);

        $this->seedTopics($pro, [
            'World News',
            'Technology',
            'Indianapolis Colts',
            'Space Exploration',
            'Indiana Jones',
        ], $refresher);

        $this->command->info('Seeded demo users: free@newsflow.test / pro@newsflow.test (password: "password").');
    }

    private function seedTopics(User $user, array $names, TopicRefresher $refresher): void
    {
        foreach (array_values($names) as $i => $name) {
            $topic = $user->topics()->create([
                'name'     => $name,
                'position' => $i,
            ]);

            $refresher->refresh($topic);
        }
    }
}
