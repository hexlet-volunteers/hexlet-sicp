<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Comment;
use App\Models\User;
use Tests\ControllerTestCase;

class CommentControllerTest extends ControllerTestCase
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

        $response = $this->get(route('admin.comments.index'));

        $response->assertOk();
        $response->assertViewIs('admin.comments');
        $response->assertViewHas('comments');
    }

    public function testIndexAsRegularUserDenied(): void
    {
        $this->withExceptionHandling();
        $this->actingAs($this->regularUser);

        $response = $this->get(route('admin.comments.index'));

        $response->assertStatus(403);
    }

    public function testIndexAsGuestDenied(): void
    {
        $this->withExceptionHandling();
        $response = $this->get(route('admin.comments.index'));

        $response->assertRedirect(route('login'));
    }
}
