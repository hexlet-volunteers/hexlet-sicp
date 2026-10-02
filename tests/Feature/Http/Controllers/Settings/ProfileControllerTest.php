<?php

namespace Tests\Feature\Http\Controllers\Settings;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\ControllerTestCase;

class ProfileControllerTest extends ControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->user);
    }

    public function testIndex(): void
    {
        $this->get(route('settings.profile.index'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Settings/Profile/Index')
                ->where('name', $this->user->name)
                ->where('email', $this->user->email)
                ->where('github_name', $this->user->github_name)
                ->where('updateUrl', route('settings.profile.update', $this->user))
                ->has('profileImage')
                ->has('menu', 2)
                ->where('auth.user', ['id' => $this->user->id, 'name' => $this->user->name, 'isAdmin' => false])
                ->has('translations.account')
                ->has('translations.settings.profile')
                ->has('nav.main')
                ->where('colorScheme', 'light')
                ->where('scope', 'settings.profile')
                ->etc());
    }

    public function testIndexIsNotIndexedByRobots(): void
    {
        $this->get(route('settings.profile.index'))
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function testIndexTakesColorSchemeFromCookie(): void
    {
        $this->withUnencryptedCookie('mantine-color-scheme', 'dark')
            ->get(route('settings.profile.index'))
            ->assertInertia(fn(Assert $page) => $page->where('colorScheme', 'dark')->etc())
            ->assertSee('data-mantine-color-scheme="dark"', false);
    }

    public function testUpdateShowsFlashOnProfilePage(): void
    {
        $this->followingRedirects()
            ->patch(route('settings.profile.update', $this->user), ['name' => 'New Name'])
            ->assertInertia(fn(Assert $page) => $page
                ->component('Settings/Profile/Index')
                ->where('flash', ['message' => __('account.account_updated'), 'level' => 'success'])
                ->etc());
    }

    public function testUpdate(): void
    {
        $userParams = [
            'name' => $this->faker->name,
            'github_name' => $this->faker->userName,
        ];
        $response = $this->patch(route('settings.profile.update', $this->user), $userParams);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', array_merge($userParams, ['id' => $this->user->id]));
    }

    public function testUpdateSameName(): void
    {
        $response = $this->patch(route('settings.profile.update', $this->user), [
            'name' => $this->user->name,
        ]);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'name' => $this->user->name]);
    }

    #[DataProvider('dataInvalidNamesProvider')]
    public function testUpdateInvalidName(string $invalidName): void
    {
        $this->expectException(ValidationException::class);

        $this->patch(route('settings.profile.update', $this->user), [
                'name' => $invalidName,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('name');
    }

    public static function dataInvalidNamesProvider(): array
    {
        return [
            ['-'],
            [Str::random(256)],
        ];
    }
}
