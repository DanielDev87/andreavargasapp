<?php

namespace App\Services;

class BunnyStreamService
{
    /**
     * A Bunny Stream video ID (GUID), e.g. 0f3c5a2e-8b1d-4c7a-9e6f-2a4b8c1d3e5f.
     */
    public const VIDEO_ID_PATTERN = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';

    public function __construct(
        private readonly string $libraryId,
        private readonly string $hostname,
        private readonly ?string $tokenKey = null,
    ) {}

    /**
     * Whether the Stream library is configured; without it every embed URL would be broken.
     */
    public function isConfigured(): bool
    {
        return $this->libraryId !== '';
    }

    public function isValidVideoId(?string $videoId): bool
    {
        return $videoId !== null && preg_match('/^'.self::VIDEO_ID_PATTERN.'$/i', $videoId) === 1;
    }

    /**
     * Pull the video ID out of whatever the admin pasted: the bare GUID or any
     * Bunny link that contains it (embed, play or direct CDN URL).
     */
    public function extractVideoId(?string $input): ?string
    {
        if ($input === null || preg_match('/'.self::VIDEO_ID_PATTERN.'/i', $input, $matches) !== 1) {
            return null;
        }

        return strtolower($matches[0]);
    }

    /**
     * Build a Bunny Stream embed iframe URL for a given video ID.
     *
     * If a token key is configured the URL is signed with an expiring hash
     * so it cannot be replayed outside the allowed referrer window.
     *
     * @param  string  $videoId  Bunny Stream video UUID
     * @param  int  $ttl  Seconds until the token expires (default 4 h)
     */
    public function embedUrl(string $videoId, int $ttl = 14400): string
    {
        $base = "https://iframe.mediadelivery.net/embed/{$this->libraryId}/{$videoId}";

        $params = [
            'autoplay' => 'false',
            'loop' => 'false',
            'muted' => 'false',
            'preload' => 'true',
            'responsive' => 'true',
        ];

        if ($this->tokenKey) {
            $expires = time() + $ttl;
            $hash = hash('sha256', $this->tokenKey.$videoId.$expires);

            $params['token'] = $hash;
            $params['expires'] = $expires;
        }

        return $base.'?'.http_build_query($params);
    }
}
