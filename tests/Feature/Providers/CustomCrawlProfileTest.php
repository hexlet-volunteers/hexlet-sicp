<?php

namespace Tests\Feature\Providers;

use App\Providers\CustomCrawlProfile;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustomCrawlProfileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'https://sicp.hexlet.io']);
        config(['sitemap.url_parts_to_filter' => ['/solutions/', '/users/', '/en/']]);
    }

    #[DataProvider('urlsProvider')]
    public function testShouldCrawl(string $url, bool $expected): void
    {
        $profile = new CustomCrawlProfile();

        $this->assertSame($expected, $profile->shouldCrawl($url));
    }

    /** @return array<string, array{string, bool}> */
    public static function urlsProvider(): array
    {
        return [
            'root of the app host' => ['https://sicp.hexlet.io', true],
            'chapter page' => ['https://sicp.hexlet.io/chapters/1', true],
            'foreign host' => ['https://example.com/chapters/1', false],
            'filtered solutions path' => ['https://sicp.hexlet.io/solutions/1', false],
            'filtered users path' => ['https://sicp.hexlet.io/users/1', false],
            'filtered locale prefix' => ['https://sicp.hexlet.io/en/chapters', false],
        ];
    }
}
