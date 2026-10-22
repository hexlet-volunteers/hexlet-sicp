<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Exercise;
use Tests\TestCase;

class SitemapControllerTest extends TestCase
{
    public function testIndex(): void
    {
        $exercise = Exercise::factory()->create();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee(route('chapters.show', $exercise->chapter_id), false);
        $response->assertSee(route('exercises.show', $exercise), false);
        $response->assertSee(route('pages.show', 'about'), false);
        $response->assertDontSee('/en/', false);
    }
}
