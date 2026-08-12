<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\User;
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

        $response = $this->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertViewIs('admin.users');
        $response->assertViewHas('users');
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
        $response->assertSessionHas('success', 'User updated');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $newName,
            'github_name' => $newGithub,
            'is_admin' => true,
        ]);
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
