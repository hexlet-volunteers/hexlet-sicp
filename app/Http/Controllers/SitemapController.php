<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Exercise;
use Cache;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;

class SitemapController extends Controller
{
    private const array STATIC_ROUTES = [
        'home',
        'chapters.index',
        'exercises.index',
        'solutions.index',
        'top.index',
        'comments_top.index',
        'comments.index',
        'log.index',
    ];

    private const array PAGES = ['about', 'how-to-learn-sicp'];

    public function index(): Response
    {
        // Маршрут живёт вне локализованной группы, поэтому route() отдаёт URL
        // локали по умолчанию без префикса — так же, как прежний краулер (он отсекал /en/).
        $xml = Cache::remember('sitemap', now()->addDay(), fn(): string => Sitemap::create()
            ->add(array_map(fn(string $name): string => route($name), self::STATIC_ROUTES))
            ->add(array_map(fn(string $page): string => route('pages.show', $page), self::PAGES))
            ->add(Chapter::pluck('id')->map(fn(int $id): string => route('chapters.show', $id))->all())
            ->add(Exercise::pluck('id')->map(fn(int $id): string => route('exercises.show', $id))->all())
            ->render());

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
