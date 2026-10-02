<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Exercise;
use App\Models\Solution;
use App\Models\User;
use Database\Seeders\ChaptersTableSeeder;
use Database\Seeders\ExercisesTableSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\ControllerTestCase;

class SolutionControllerTest extends ControllerTestCase
{
    private Exercise $exercise;
    private Solution $solution;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            ChaptersTableSeeder::class,
            ExercisesTableSeeder::class,
        ]);
        // Разные упражнения: versioned() схлопывает версии одной пары упражнение+автор,
        // а случайный выбор фабрики иногда давал совпадение.
        $exerciseIds = Exercise::orderBy('id')->limit(5)->pluck('id');
        $solutions = Solution::factory()
            ->count(5)
            ->sequence(fn(Sequence $sequence) => ['exercise_id' => $exerciseIds[$sequence->index]])
            ->create();
        $this->user->solutions()->saveMany($solutions);

        $this->exercise = Exercise::first();
        $this->solution = Solution::first();

        $this->actingAs($this->user);
    }

    public function testIndex(): void
    {
        $latest = Solution::factory()->for($this->user)->create([
            'exercise_id' => Exercise::orderBy('id')->skip(5)->value('id'),
            'created_at' => now()->addMinute(),
        ]);

        $this->get(route('solutions.index'))
            ->assertOk()
            // Публичная страница индексируется, SSR нет — принятый SEO-долг (ADR 0004).
            ->assertDontSee('noindex', false)
            ->assertInertia(fn(Assert $page) => $page
                ->component('Solution/Index')
                ->where('pagination.total', 6)
                ->where('filterUrl', route('solutions.index'))
                ->where('filter', ['userName' => null, 'exerciseId' => null])
                ->has('exercises', Exercise::count())
                ->where('exercises.0', [
                    'value' => (string) Exercise::orderBy('id')->first()->id,
                    'label' => Exercise::orderBy('id')->first()->present()->fullTitle,
                ])
                ->has('tabs', 2)
                ->where('tabs.0.href', route('exercises.index'))
                ->where('tabs.0.inertia', false)
                ->where('tabs.1.href', route('solutions.index'))
                ->where('tabs.1.active', true)
                ->where('tabs.1.inertia', true)
                ->has('items', 6)
                ->has('items.0', fn(Assert $item) => $item
                    ->where('id', $latest->id)
                    ->where('userName', $this->user->name)
                    ->where('userUrl', route('users.show', $this->user))
                    ->where('userAvatarUrl', $this->user->present()->getProfileImageLink())
                    ->where('exerciseTitle', $latest->exercise->getFullTitle())
                    ->where('exerciseUrl', route('exercises.show', $latest->exercise))
                    ->where('createdAt', $latest->created_at->format('d.m.Y H:i'))
                    ->where('showUrl', route('solutions.show', $latest))));
    }

    public function testIndexFiltersByUserName(): void
    {
        $author = User::factory()->create(['name' => 'Zebediah Quux']);
        $solution = Solution::factory()->for($author)->create();

        $this->get(route('solutions.index', ['filter' => ['user.name' => 'ebediah q']]))
            ->assertInertia(fn(Assert $page) => $page
                ->has('items', 1)
                ->where('items.0.id', $solution->id)
                ->where('filter.userName', 'ebediah q')
                ->etc());
    }

    public function testIndexFiltersByExercise(): void
    {
        $exercise = Exercise::orderByDesc('id')->first();
        $solution = Solution::factory()->for($exercise)->create();

        $this->get(route('solutions.index', ['filter' => ['exercise_id' => $exercise->id]]))
            ->assertInertia(fn(Assert $page) => $page
                ->has('items', 1)
                ->where('items.0.id', $solution->id)
                ->where('filter.exerciseId', (string) $exercise->id)
                ->etc());
    }

    public function testIndexIgnoresEmptyAndArrayFilter(): void
    {
        // Очищенный Select отправляет пустую строку — bigint = '' ронял бы PostgreSQL.
        $this->get(route('solutions.index', ['filter' => ['user.name' => '', 'exercise_id' => '']]))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->where('pagination.total', 5)->etc());

        $this->get(route('solutions.index', ['filter' => ['user.name' => ['x']]]))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->where('filter.userName', null)->etc());
    }

    public function testPaginationKeepsFilter(): void
    {
        $exercise = Exercise::orderByDesc('id')->first();
        // versioned() оставляет одну версию на пару автор–упражнение, поэтому авторы разные.
        Solution::factory()->count(51)->for($exercise)->state(fn() => ['user_id' => User::factory()])->create();
        $filter = ['filter' => ['exercise_id' => (string) $exercise->id]];

        $this->get(route('solutions.index', $filter))
            ->assertInertia(fn(Assert $page) => $page
                ->where('pagination.total', 51)
                ->where('pagination.lastPage', 2)
                ->where('pagination.links.2.url', route('solutions.index', [...$filter, 'page' => 2]))
                ->etc());
    }

    public function testShow(): void
    {
        $exercise = $this->solution->exercise;
        $versions = $exercise->solutions()->where('user_id', $this->user->id)->orderBy('id')->get();

        $this->get(route('solutions.show', $this->solution))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertInertia(fn(Assert $page) => $page
                ->component('Solution/Show')
                ->where('title', __('solution.solution_for_title', ['exercise' => $exercise->getFullTitle()]))
                ->where('exerciseTitle', $exercise->getFullTitle())
                ->where('exerciseUrl', route('exercises.show', $exercise))
                ->where('userName', $this->user->name)
                ->where('userUrl', route('users.show', $this->user))
                ->has('versions', $versions->count())
                ->where('versions.0.id', $versions->first()->id)
                ->where('versions.0.content', $versions->first()->content));
    }

    public function testShowComparesEveryVersionOfExercise(): void
    {
        $exercise = $this->solution->exercise;
        $this->user->solutions()->saveMany(Solution::factory()->count(2)->for($exercise)->make());
        // Чужое решение того же упражнения в сравнение не попадает.
        Solution::factory()->for($exercise)->for(User::factory())->create();

        $ids = $exercise->solutions()->where('user_id', $this->user->id)->orderBy('id')->pluck('id')->all();

        $this->get(route('solutions.show', $this->solution))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Solution/Show')
                ->where('versions', fn($versions) => collect($versions)->pluck('id')->all() === $ids)
                ->etc());
    }

    public function testShowSolutionOfTrashedUser(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->solution->user->delete();

        $route = route('solutions.show', $this->solution);

        $response = $this->get($route);

        $response->assertNotFound();
    }
}
