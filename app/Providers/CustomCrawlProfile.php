<?php

namespace App\Providers;

use Spatie\Crawler\CrawlProfiles\CrawlProfile;
use Illuminate\Support\Str;

class CustomCrawlProfile implements CrawlProfile
{
    public function shouldCrawl(string $url): bool
    {
        $parsedAppUrl = parse_url(config('app.url'));

        $appUrlHost = $parsedAppUrl['host'];

        if (parse_url($url, PHP_URL_HOST) !== $appUrlHost) {
            return false;
        }

        return !Str::contains(parse_url($url, PHP_URL_PATH) ?? '', config('sitemap.url_parts_to_filter'));
    }
}
