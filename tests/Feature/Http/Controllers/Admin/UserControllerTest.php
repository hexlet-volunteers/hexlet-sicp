<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\ControllerTestCase;

class UserControllerTest extends ControllerTestCase
{
    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->admin()->create();
        $this->regularUser = User::factory()->regular()->create();
    }

    public function testIndexAsAdmin(): void
    {
        $this->actingAs($this->adminUser);

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertInertia(fn(Assert $page) => $page
                ->component('Admin/User/Index')
                ->where('pagination.total', User::count())
                ->where('filterUrl', route('admin.users.index'))
                ->where('filter', ['name' => null, 'email' => null])
                ->has('menu', 4)
                ->where('menu.0.active', true)
                ->where('menu.3.href', route('admin.export.index'))
                ->has('items.0', fn(Assert $item) => $item
                    ->where('id', $this->regularUser->id)
                    ->where('name', $this->regularUser->name)
                    ->where('email', $this->regularUser->email)
                    ->where('isAdmin', false)
                    ->where('createdAt', $this->regularUser->created_at->format('d.m.Y H:i'))
                    ->where('showUrl', route('users.show', $this->regularUser))
                    ->where('editUrl', route('admin.users.edit', $this->regularUser))));
    }

    public function testIndexFiltersByName(): void
    {
        $this->actingAs($this->adminUser);
        $match = User::factory()->create(['name' => 'Zebediah Quux']);
        $filter = ['filter' => ['name' => 'ebediah q']];

        $this->get(route('admin.users.index', $filter))
            ->assertInertia(fn(Assert $page) => $page
                ->has('items', 1)
                ->where('items.0.id', $match->id)
                ->where('filter.name', 'ebediah q')
                // Раздел админки, открытый из меню, ищет того же пользователя.
                ->where('menu.1.href', route('admin.comments.index', $filter))
                ->etc());
    }

    public function testIndexIgnoresArrayFilterInForm(): void
    {
        $this->actingAs($this->adminUser);

        $this->get(route('admin.users.index', ['filter' => ['name' => ['x']]]))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->where('filter.name', null)->etc());
    }

    public function testPaginationKeepsFilter(): void
    {
        $this->actingAs($this->adminUser);
        User::factory()->count(51)->create(['email' => fn() => $this->faker->unique()->userName . '@paged.test']);
        $filter = ['filter' => ['email' => '@paged.test']];

        $this->get(route('admin.users.index', $filter))
            ->assertInertia(fn(Assert $page) => $page
                ->where('pagination.total', 51)
                ->where('pagination.lastPage', 2)
                ->where('pagination.links.2.url', route('admin.users.index', [...$filter, 'page' => 2]))
                ->etc());
    }

    public function testIndexAsRegularUserDenied(): void
    {
        $this->withExceptionHandling();
        $this->actingAs($this->regularUser);

        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    public function testIndexAsGuestDenied(): void
    {
        $this->withExceptionHandling();
        $response = $this->get(route('admin.users.index'));

        $response->assertRedirect(route('login'));
    }

    public function testUpdateAsAdmin(): void
    {
        $this->actingAs($this->adminUser);

        $user = User::factory()->create([
            'name' => $this->faker->name,
            'github_name' => $this->faker->userName,
            'is_admin' => false,
        ]);

        $newName = $this->faker->name;
        $newGithub = $this->faker->userName;

        $response = $this->put(route('admin.users.update', $user), [
            'name' => $newName,
            'github_name' => $newGithub,
            'is_admin' => true,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', __('layout.flash.success'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $newName,
            'github_name' => $newGithub,
            'is_admin' => true,
        ]);
    }

    public function testEdit(): void
    {
        $this->actingAs($this->adminUser);

        $this->get(route('admin.users.edit', $this->regularUser))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertInertia(fn(Assert $page) => $page
                ->component('Admin/User/Edit')
                ->where('name', $this->regularUser->name)
                ->where('githubName', $this->regularUser->github_name)
                ->where('isAdmin', false)
                ->where('updateUrl', route('admin.users.update', $this->regularUser))
                ->where('cancelUrl', route('admin.users.index'))
                ->has('menu', 4)
                // Раздел «Пользователи» подсвечен и на вложенной странице.
                ->where('menu.0.active', true)
                ->where('menu.3.active', false)
                ->where('menu.3.href', route('admin.export.index')));
    }

    public function testUpdateShowsFlashOnUserList(): void
    {
        $this->actingAs($this->adminUser);

        $this->followingRedirects()
            ->put(route('admin.users.update', $this->regularUser), ['name' => 'New Name'])
            ->assertInertia(fn(Assert $page) => $page
                ->component('Admin/User/Index')
                ->where('flash', ['message' => __('layout.flash.success'), 'level' => 'success'])
                ->etc());
    }

    public function testUpdateRevokesAdmin(): void
    {
        $this->actingAs($this->adminUser);
        $user = User::factory()->admin()->create();

        $this->put(route('admin.users.update', $user), ['name' => $user->name, 'is_admin' => false]);

        $this->assertFalse($user->fresh()->is_admin);
    }

    public function testUpdateRequiresName(): void
    {
        $this->withExceptionHandling();
        $this->actingAs($this->adminUser);
        $edit = route('admin.users.edit', $this->regularUser);

        $this->from($edit)
            ->put(route('admin.users.update', $this->regularUser), ['name' => ''])
            ->assertRedirect($edit)
            ->assertSessionHasErrors('name');
    }

    public function testUpdateAsRegularUserDenied(): void
    {
        $this->withExceptionHandling();
        $this->actingAs($this->regularUser);

        $user = User::factory()->create();

        $response = $this->put(route('admin.users.update', $user), [
            'name' => $this->faker->name,
            'github_name' => $this->faker->userName,
            'is_admin' => true,
        ]);

        $response->assertStatus(403);
    }

    public function testUpdateAsGuestDenied(): void
    {
        $this->withExceptionHandling();

        $user = User::factory()->create();

        $response = $this->put(route('admin.users.update', $user), [
            'name' => $this->faker->name,
            'github_name' => $this->faker->userName,
            'is_admin' => true,
        ]);

        $response->assertRedirect(route('login'));
    }
}
