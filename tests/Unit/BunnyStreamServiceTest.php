<?php

namespace Tests\Unit;

use App\Services\BunnyStreamService;
use PHPUnit\Framework\TestCase;

class BunnyStreamServiceTest extends TestCase
{
    private const VIDEO_ID = '0f3c5a2e-8b1d-4c7a-9e6f-2a4b8c1d3e5f';

    public function test_embed_url_is_signed_with_an_expiring_token_when_a_token_key_is_set(): void
    {
        $bunny = new BunnyStreamService(libraryId: '12345', hostname: 'vz-test.b-cdn.net', tokenKey: 'secret');

        $url = $bunny->embedUrl(self::VIDEO_ID, ttl: 600);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        $this->assertStringStartsWith('https://player.mediadelivery.net/embed/12345/'.self::VIDEO_ID.'?', $url);
        $this->assertEqualsWithDelta(time() + 600, (int) $query['expires'], 2);
        $this->assertSame(hash('sha256', 'secret'.self::VIDEO_ID.$query['expires']), $query['token']);
    }

    public function test_embed_url_is_unsigned_without_a_token_key(): void
    {
        $bunny = new BunnyStreamService(libraryId: '12345', hostname: '');

        $url = $bunny->embedUrl(self::VIDEO_ID);

        $this->assertStringNotContainsString('token=', $url);
        $this->assertStringNotContainsString('expires=', $url);
    }

    public function test_it_is_only_configured_when_the_library_id_is_set(): void
    {
        $this->assertFalse((new BunnyStreamService(libraryId: '', hostname: ''))->isConfigured());
        $this->assertTrue((new BunnyStreamService(libraryId: '12345', hostname: ''))->isConfigured());
    }

    public function test_it_extracts_the_video_id_from_a_bare_id_or_a_bunny_link(): void
    {
        $bunny = new BunnyStreamService(libraryId: '12345', hostname: '');

        $this->assertSame(self::VIDEO_ID, $bunny->extractVideoId(self::VIDEO_ID));
        $this->assertSame(self::VIDEO_ID, $bunny->extractVideoId('  '.strtoupper(self::VIDEO_ID).' '));
        $this->assertSame(self::VIDEO_ID, $bunny->extractVideoId('https://player.mediadelivery.net/embed/12345/'.self::VIDEO_ID.'?autoplay=true'));
        $this->assertSame(self::VIDEO_ID, $bunny->extractVideoId('https://player.mediadelivery.net/play/12345/'.self::VIDEO_ID));
    }

    public function test_youtube_ids_are_not_valid_bunny_video_ids(): void
    {
        $bunny = new BunnyStreamService(libraryId: '12345', hostname: '');

        $this->assertNull($bunny->extractVideoId('https://www.youtube.com/watch?v=2rcgewG9OII'));
        $this->assertFalse($bunny->isValidVideoId('2rcgewG9OII'));
        $this->assertFalse($bunny->isValidVideoId(null));
        $this->assertTrue($bunny->isValidVideoId(self::VIDEO_ID));
    }
}
