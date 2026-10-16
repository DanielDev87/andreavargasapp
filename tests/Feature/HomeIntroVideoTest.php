<?php

namespace Tests\Feature;

use App\Services\BunnyStreamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeIntroVideoTest extends TestCase
{
    use RefreshDatabase;

    private const VIDEO_ID = '0f3c5a2e-8b1d-4c7a-9e6f-2a4b8c1d3e5f';

    private function configureIntroVideo(?string $videoId): void
    {
        config([
            'services.bunny_stream.library_id' => '12345',
            'services.bunny_stream.intro_video_id' => $videoId,
        ]);

        $this->app->forgetInstance(BunnyStreamService::class);
    }

    public function test_meet_your_doctor_opens_the_intro_video_in_a_modal(): void
    {
        $this->configureIntroVideo(self::VIDEO_ID);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-modal-open="intro-video-dialog"', escape: false)
            ->assertSee('id="intro-video-dialog"', escape: false)
            ->assertSee('player.mediadelivery.net/embed/12345/'.self::VIDEO_ID.'?autoplay=true', escape: false);
    }

    public function test_without_an_intro_video_the_button_keeps_scrolling_to_the_treatments(): void
    {
        $this->configureIntroVideo(null);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="intro-video-dialog"', escape: false)
            ->assertSee(__('app.doctor_cta'));
    }
}
