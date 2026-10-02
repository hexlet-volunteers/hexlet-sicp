<?php

namespace Tests\Feature\Http\Controllers\My;

use App\Models\Chapter;
use App\Models\Exercise;
use App\Models\Solution;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\ControllerTestCase;

class SolutionControllerTest extends ControllerTestCase
{
    private Exercise $exercise;

    public function setUp(): void
    {
        parent::setUp();

        $chapter = Chapter::factory()->create(['path' => '1.1.1']);
        $this->exercise = Exercise::factory()->create(['chapter_id' => $chapter->id, 'path' => '1.1']);

        $this->actingAs($this->user);
    }

    public function testIndexWithoutSolutions(): void
    {
        $this->get(route('my.solutions.index'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee(__('my.description'))
            ->assertInertia(fn(Assert $page) => $page
                ->component('My/Solution/Index')
                ->where('userName', $this->user->name)
                ->where('userUrl', route('users.show', $this->user))
                ->where('progressUrl', route('my.show'))
                ->has('items', 0)
                ->where('pagination.total', 0));
    }

    public function testIndexListsOnlyOwnSolutions(): void
    {
        $solution = Solution::factory()->for($this->user)->for($this->exercise)->create();
        Solution::factory()->for(User::factory())->for($this->exercise)->create();

        $this->get(route('my.solutions.index'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('My/Solution/Index')
                ->has('items', 1)
                ->where('items.0', [
                    'id' => $solution->id,
                    'exerciseTitle' => $this->exercise->getFullTitle(),
                    'chapterPath' => '1.1.1',
                    'showUrl' => route('users.solutions.show', [$this->user, $solution]),
                ])
                ->etc());
    }

    public function testPaginationKeepsQueryString(): void
    {
        // versioned() оставляет одну строку на упражнение — нужны разные упражнения.
        Exercise::factory()->count(11)->for($this->exercise->chapter)->create()
            ->each(fn(Exercise $exercise) => Solution::factory()->for($this->user)->for($exercise)->create());

        $this->get(route('my.solutions.index', ['foo' => 'bar']))
            ->assertInertia(fn(Assert $page) => $page
                ->has('items', 10)
                ->where('pagination.lastPage', 2)
                ->where('pagination.links.2.url', route('my.solutions.index', ['foo' => 'bar', 'page' => 2]))
                ->etc());
    }

    public function testGuestIsRedirectedToLogin(): void
    {
        $this->withExceptionHandling();
        auth()->logout();

        $this->get(route('my.solutions.index'))->assertRedirect(route('login'));
    }
}
