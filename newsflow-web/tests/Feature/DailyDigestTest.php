<?php

namespace Tests\Feature;

use App\Mail\DailyDigest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DailyDigestTest extends TestCase
{
    use RefreshDatabase;

    private function userWithFeed(array $attrs = []): User
    {
        $user = User::factory()->create($attrs);
        $topic = $user->topics()->create(['name' => 'World News', 'position' => 0]);
        $topic->articles()->create([
            'headline'    => 'Big story today',
            'description' => 'Something happened.',
            'url'         => 'https://example.test/story',
            'fingerprint' => 'fp1',
            'position'    => 0,
        ]);

        return $user;
    }

    public function test_digest_mailable_renders_without_errors(): void
    {
        $user = $this->userWithFeed(['digest_enabled' => true]);
        $topic = $user->topics()->first();

        $mailable = new DailyDigest($user, [
            ['name' => $topic->name, 'articles' => $topic->articles],
        ]);

        $html = $mailable->render();

        $this->assertStringContainsString('Big story today', $html);
        $this->assertStringContainsString('World News', $html);
        $this->assertStringContainsString('Open my NewsFlow', $html);
    }

    public function test_digest_is_sent_to_opted_in_user(): void
    {
        Mail::fake();
        $user = $this->userWithFeed(['digest_enabled' => true]);

        $this->artisan('newsflow:digest', ['--user' => $user->id])->assertSuccessful();

        Mail::assertSent(DailyDigest::class, fn ($mail) => $mail->hasTo($user->email));
        $this->assertNotNull($user->fresh()->digest_sent_at);
    }

    public function test_digest_skips_users_who_opted_out(): void
    {
        Mail::fake();
        $user = $this->userWithFeed(['digest_enabled' => false]);

        $this->artisan('newsflow:digest')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_due_digest_only_sends_to_users_due_this_hour(): void
    {
        Mail::fake();
        Carbon::setTestNow(Carbon::create(2026, 6, 16, 13, 0, 0, 'UTC'));

        $due = $this->userWithFeed(['digest_enabled' => true, 'timezone' => 'UTC', 'refresh_hour' => 13]);
        $notDue = $this->userWithFeed(['digest_enabled' => true, 'timezone' => 'UTC', 'refresh_hour' => 8]);

        $this->artisan('newsflow:digest', ['--due' => true])->assertSuccessful();

        Mail::assertSent(DailyDigest::class, fn ($mail) => $mail->hasTo($due->email));
        Mail::assertNotSent(DailyDigest::class, fn ($mail) => $mail->hasTo($notDue->email));

        Carbon::setTestNow();
    }
}
