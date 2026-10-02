<?php

namespace Tests\Feature\Http\Controllers;

use Database\Seeders\ChaptersTableSeeder;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\ControllerTestCase;

class UserControllerTest extends ControllerTestCase
{
    public function testShow(): void
    {
        $response = $this->get(route('users.show', $this->user));

        $response->assertOk();
    }

    #[TestWith(['1 Building Abstractions with Procedures'], 'chapter with children')]
    #[TestWith(['1.1.1 Expressions'], 'leaf chapter')]
    public function testShowRendersChapterNameWithoutTrailingDot(string $chapterName): void
    {
        $this->seed(ChaptersTableSeeder::class);

        $response = $this->get(route('users.show', $this->user));

        $response->assertSee($chapterName);
        $response->assertDontSee("{$chapterName}.");
    }
}
