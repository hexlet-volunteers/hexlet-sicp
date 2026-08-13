<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Solution;
use App\Models\User;
use Tests\ControllerTestCase;

class SolutionControllerTest extends ControllerTestCase
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

        $response = $this->get(route('admin.solutions.index'));

        $response->assertOk();
        $response->assertViewIs('admin.solutions');
        $response->assertViewHas('solutions');
    }

    public function testIndexAsRegularUserDenied(): void
    {
        $this->withExceptionHandling();
        $this->actingAs($this->regularUser);

        $response = $this->get(route('admin.solutions.index'));

        $response->assertStatus(403);
    }

    public function testIndexAsGuestDenied(): void
    {
        $this->withExceptionHandling();
        $response = $this->get(route('admin.solutions.index'));

        $response->assertRedirect(route('login'));
    }
}
