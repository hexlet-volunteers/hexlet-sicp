<?php

namespace Tests\Feature\Http\Controllers\User;

use App\Models\Solution;
use App\Models\User;
use Database\Seeders\ChaptersTableSeeder;
use Database\Seeders\ExercisesTableSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\ControllerTestCase;

class SolutionControllerTest extends ControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            ChaptersTableSeeder::class,
            ExercisesTableSeeder::class,
        ]);

        $this->user->solutions()->saveMany(
            Solution::factory()->count(5)->make()
        );

        $this->actingAs($this->user);
    }

    public function testShow(): void
    {
        $solution = $this->user->solutions()->first();

        $exercise = $solution->exercise;
        $versions = $exercise->solutions()->where('user_id', $this->user->id)->orderBy('id')->get();

        $this->get(route('users.solutions.show', [$this->user, $solution]))
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

    public function testShowOfForeignSolutionIsForbidden(): void
    {
        $this->withExceptionHandling();
        $owner = User::factory()->create();
        $solution = Solution::factory()->for($owner)->create();

        $response = $this->get(route('users.solutions.show', [$owner, $solution]));

        $response->assertForbidden();
    }

    public function testShowOfForeignSolutionIsAllowedForAdmin(): void
    {
        $owner = User::factory()->create();
        $solution = Solution::factory()->for($owner)->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('users.solutions.show', [$owner, $solution]));

        $response->assertOk();
    }
}
