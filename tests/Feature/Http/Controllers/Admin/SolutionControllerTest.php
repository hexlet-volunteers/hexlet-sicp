<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Exercise;
use App\Models\Solution;
use App\Models\User;
use Database\Seeders\ChaptersTableSeeder;
use Database\Seeders\ExercisesTableSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\ControllerTestCase;

class SolutionControllerTest extends ControllerTestCase
{
    protected User $adminUser;
    protected User $regularUser;
    protected Exercise $exercise;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([ChaptersTableSeeder::class, ExercisesTableSeeder::class]);
        $this->exercise = Exercise::firstOrFail();
        $this->adminUser = User::factory()->admin()->create();
        $this->regularUser = User::factory()->regular()->create();
    }

    private function solutionOf(User $user, int $count = 1): void
    {
        Solution::factory()->count($count)->create([
            'user_id' => $user->id,
            'exercise_id' => $this->exercise->id,
        ]);
    }

    public function testIndexAsAdmin(): void
    {
        $this->actingAs($this->adminUser);
        $solution = Solution::factory()->create([
            'user_id' => $this->regularUser->id,
            'exercise_id' => $this->exercise->id,
            'content' => str_repeat('a', 100) . 'TAIL',
        ]);

        $this->get(route('admin.solutions.index'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertInertia(fn(Assert $page) => $page
                ->component('Admin/Solution/Index')
                ->where('pagination.total', 1)
                ->where('filterUrl', route('admin.solutions.index'))
                ->where('filter', ['name' => null, 'email' => null])
                ->has('menu', 4)
                ->where('menu.2.active', true)
                ->has('items.0', fn(Assert $item) => $item
                    ->where('id', $solution->id)
                    ->where('userName', $this->regularUser->name)
                    ->where('userUrl', route('users.show', $this->regularUser))
                    ->where('exercisePath', $this->exercise->path)
                    ->where('exerciseTitle', $this->exercise->getTitle())
                    ->where('exerciseUrl', route('exercises.show', $this->exercise))
                    ->where('content', str_repeat('a', 100))
                    ->where('url', route('users.solutions.show', [$this->regularUser, $solution]))
                    ->where('createdAt', $solution->created_at->format('d.m.Y H:i'))));
    }

    public function testIndexFiltersByAuthorName(): void
    {
        $this->actingAs($this->adminUser);
        $author = User::factory()->create(['name' => 'Zebediah Quux']);
        $this->solutionOf($author);
        $this->solutionOf($this->regularUser);
        $filter = ['filter' => ['name' => 'ebediah q']];

        $this->get(route('admin.solutions.index', $filter))
            ->assertInertia(fn(Assert $page) => $page
                ->has('items', 1)
                ->where('items.0.userName', 'Zebediah Quux')
                ->where('filter.name', 'ebediah q')
                ->where('menu.1.href', route('admin.comments.index', $filter))
                ->etc());
    }

    public function testPaginationKeepsFilter(): void
    {
        $this->actingAs($this->adminUser);
        $this->solutionOf(User::factory()->create(['email' => 'author@paged.test']), 51);
        $this->solutionOf($this->regularUser);
        $filter = ['filter' => ['email' => '@paged.test']];

        $this->get(route('admin.solutions.index', $filter))
            ->assertInertia(fn(Assert $page) => $page
                ->where('pagination.total', 51)
                ->where('pagination.lastPage', 2)
                ->where('pagination.links.2.url', route('admin.solutions.index', [...$filter, 'page' => 2]))
                ->etc());
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
