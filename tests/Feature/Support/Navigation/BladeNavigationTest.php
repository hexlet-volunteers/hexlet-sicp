<?php

namespace Tests\Feature\Support\Navigation;

use App\Models\User;
use Tests\TestCase;

/** Blade-шапка и футер рендерят NavigationBuilder через ViewServiceProvider. */
class BladeNavigationTest extends TestCase
{
    public function testGuestSeesLoginAndFooter(): void
    {
        $this->get(route('chapters.index'))
            ->assertOk()
            ->assertSee('href="' . route('login') . '"', false)
            ->assertDontSee(route('logout'))
            ->assertDontSee(route('admin.users.index'))
            ->assertSee(__('layout.footer.source_code'));
    }

    public function testUserSeesAccountMenu(): void
    {
        $this->actingAs(User::factory()->regular()->create())
            ->get(route('chapters.index'))
            ->assertSee('href="' . route('logout') . '" data-method="post"', false)
            ->assertSee('href="' . route('my.show') . '"', false)
            ->assertDontSee(route('admin.users.index'));
    }

    public function testAdminSeesAdminMenuWithExport(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('chapters.index'))
            ->assertSeeInOrder([
                'class="bi bi-people"',
                route('admin.comments.index'),
                route('admin.solutions.index'),
                'href="' . route('admin.export.index') . '"',
                'class="bi bi-download"',
            ], false);
    }
}
