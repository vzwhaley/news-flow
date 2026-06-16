<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_refresh_hour_and_timezone(): void
    {
        $user = User::factory()->create(['email_verified_at' => Carbon::now()]);

        $this->actingAs($user)
            ->patch(route('preferences.update'), [
                'refresh_hour' => 7,
                'timezone'     => 'America/Chicago',
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertSame(7, $user->refresh_hour);
        $this->assertSame('America/Chicago', $user->timezone);
    }

    public function test_invalid_hour_is_rejected(): void
    {
        $user = User::factory()->create(['email_verified_at' => Carbon::now()]);

        $this->actingAs($user)
            ->patch(route('preferences.update'), ['refresh_hour' => 99, 'timezone' => 'UTC'])
            ->assertSessionHasErrors('refresh_hour');
    }

    public function test_invalid_timezone_is_rejected(): void
    {
        $user = User::factory()->create(['email_verified_at' => Carbon::now()]);

        $this->actingAs($user)
            ->patch(route('preferences.update'), ['refresh_hour' => 6, 'timezone' => 'Mars/Olympus'])
            ->assertSessionHasErrors('timezone');
    }
}
