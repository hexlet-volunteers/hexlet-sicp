<?php

namespace Tests\Feature\Http\Controllers;

use App\Helpers\ChapterHelper;
use App\Models\Chapter;
use App\Models\ChapterMember;
use App\Models\Exercise;
use App\Models\ExerciseMember;
use Database\Seeders\ChaptersTableSeeder;
use Database\Seeders\ExercisesTableSeeder;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\ControllerTestCase;

class MyControllerTest extends ControllerTestCase
{
    private Chapter $root;
    private Chapter $section;
    private Chapter $leaf;
    private Exercise $exercise;

    public function setUp(): void
    {
        parent::setUp();

        $this->root = Chapter::factory()->create(['path' => '1']);
        $this->section = Chapter::factory()->create(['path' => '1.1', 'parent_id' => $this->root->id]);
        $this->leaf = Chapter::factory()->create(['path' => '1.1.1', 'parent_id' => $this->section->id]);
        $this->exercise = Exercise::factory()->create(['chapter_id' => $this->leaf->id, 'path' => '1.1']);

        $this->actingAs($this->user);
    }

    public function testShowForUserWithoutProgress(): void
    {
        $this->get(route('my.show'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertSee(__('my.description'))
            ->assertInertia(fn(Assert $page) => $page
                ->component('My/Show')
                ->where('userName', $this->user->name)
                ->where('userUrl', route('users.show', $this->user))
                ->where('solutionsUrl', route('my.solutions.index'))
                ->has('chapters', 1)
                ->where('chapters.0.title', ChapterHelper::fullChapterName('1'))
                ->where('chapters.0.isCompleted', false)
                ->where('chapters.0.completedChildrenCount', 0)
                ->where('chapters.0.totalChildrenCount', 1)
                ->where('chapters.0.children.0.children.0', [
                    'id' => $this->leaf->id,
                    'title' => ChapterHelper::fullChapterName('1.1.1'),
                    'url' => route('chapters.show', $this->leaf),
                    'isCompleted' => false,
                    'completedChildrenCount' => 0,
                    'totalChildrenCount' => 0,
                    'children' => [],
                    'exercises' => [[
                        'id' => $this->exercise->id,
                        'title' => $this->exercise->getFullTitle(),
                        'url' => route('exercises.show', $this->exercise),
                        'isCompleted' => false,
                        'isInProgress' => false,
                    ]],
                ]));
    }

    public function testShowMarksFinishedChaptersAndExercises(): void
    {
        ChapterMember::factory()->user($this->user)->chapter($this->leaf)->create([
            'state' => ChapterMember::STATE_FINISHED,
        ]);
        ExerciseMember::factory()->user($this->user)->exercise($this->exercise)->create();

        $this->get(route('my.show'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('My/Show')
                ->where('chapters.0.isCompleted', true)
                ->where('chapters.0.completedChildrenCount', 1)
                ->where('chapters.0.children.0.children.0.isCompleted', true)
                ->where('chapters.0.children.0.children.0.exercises.0.isCompleted', true)
                ->where('chapters.0.children.0.children.0.exercises.0.isInProgress', false)
                ->etc());
    }

    public function testShowMarksStartedExerciseAsInProgress(): void
    {
        ExerciseMember::create([
            'user_id' => $this->user->id,
            'exercise_id' => $this->exercise->id,
        ]);

        $this->get(route('my.show'))
            ->assertInertia(fn(Assert $page) => $page
                ->where('chapters.0.children.0.children.0.exercises.0.isCompleted', false)
                ->where('chapters.0.children.0.children.0.exercises.0.isInProgress', true)
                ->etc());
    }

    public function testRootChaptersAreSortedByPath(): void
    {
        Chapter::factory()->create(['path' => '2']);

        $this->get(route('my.show'))
            ->assertInertia(fn(Assert $page) => $page
                ->where('chapters.0.title', ChapterHelper::fullChapterName('1'))
                ->where('chapters.1.title', ChapterHelper::fullChapterName('2'))
                ->etc());
    }

    public function testQueryCountDoesNotGrowWithChapters(): void
    {
        $before = $this->countShowQueries();

        $root = Chapter::factory()->create(['path' => '2']);
        $section = Chapter::factory()->create(['path' => '2.1', 'parent_id' => $root->id]);
        $leaf = Chapter::factory()->create(['path' => '2.1.1', 'parent_id' => $section->id]);
        Chapter::factory()->create(['path' => '2.1.2', 'parent_id' => $section->id]);
        Exercise::factory()->create(['chapter_id' => $leaf->id, 'path' => '2.1']);
        $sibling = Chapter::factory()->create(['path' => '1.2', 'parent_id' => $this->root->id]);
        Chapter::factory()->create(['path' => '1.2.1', 'parent_id' => $sibling->id]);

        $this->assertSame($before, $this->countShowQueries());
    }

    public function testShowRendersSeededBook(): void
    {
        Exercise::query()->delete();
        Chapter::query()->delete();
        $this->seed([ChaptersTableSeeder::class, ExercisesTableSeeder::class]);

        $this->get(route('my.show'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('My/Show')
                ->has('chapters', Chapter::whereNull('parent_id')->count())
                ->where('chapters.0.title', ChapterHelper::fullChapterName('1'))
                ->etc());
    }

    public function testGuestIsRedirectedToLogin(): void
    {
        $this->withExceptionHandling();
        auth()->logout();

        $this->get(route('my.show'))->assertRedirect(route('login'));
    }

    private function countShowQueries(): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('my.show'))->assertOk();
        DB::disableQueryLog();

        return count(DB::getQueryLog());
    }
}
