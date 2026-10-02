<?php

namespace Tests\Feature\Http\Controllers;

use App\DTO\Activity\ActivityItemData;
use App\Helpers\ChapterHelper;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Exercise;
use App\Models\Solution;
use App\Services\ActivityService;
use Database\Seeders\ChaptersTableSeeder;
use Database\Seeders\ExercisesTableSeeder;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\ControllerTestCase;

class ActivityControllerTest extends ControllerTestCase
{
    private Exercise $exercise;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            ChaptersTableSeeder::class,
            ExercisesTableSeeder::class,
        ]);

        $this->exercise = Exercise::first();
        $this->actingAs($this->user);
    }

    public function testIndexIsEmpty(): void
    {
        $this->get(route('log.index'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex">', false)
            ->assertInertia(fn(Assert $page) => $page
                ->component('Activity/Index')
                ->has('items', 0)
                ->where('pagination.total', 0)
                ->etc());
    }

    public function testIndexRendersEveryActivityKind(): void
    {
        $this->logEveryKind();
        $comment = Comment::firstOrFail();

        $this->get(route('log.index'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->component('Activity/Index')
                ->has('items', 5)
                ->where('pagination.total', 5)
                ->where('items.0.description', __('activitylog.action_unknown'))
                ->where('items.0.links', [])
                ->where('items.1.description', trans_choice('activitylog.action_added', 2, ['count' => 2]))
                ->where('items.1.links.1', [
                    'label' => ChapterHelper::fullChapterName('1.1.2'),
                    'href' => ChapterHelper::getChapterOriginLinkForNumber('1.1.2'),
                ])
                ->where('items.2.description', __('activitylog.action_commented') . '.')
                ->where('items.2.links.0', [
                    'label' => $comment->getCommentableName(),
                    'href' => $comment->present()->getLink(),
                ])
                ->where('items.3.links.0', [
                    'label' => $this->exercise->getFullTitle(),
                    'href' => route('exercises.show', $this->exercise),
                ])
                ->where('items.4.description', __('activitylog.action_add_solution'))
                ->where('items.4.links.0', [
                    'label' => $this->exercise->getFullTitle(),
                    'href' => route('exercises.show', $this->exercise),
                ])
                ->where('items.4.causerName', $this->user->name)
                ->where('items.4.causerUrl', route('users.show', $this->user))
                ->etc());
    }

    public function testIndexSurvivesDeletedSubject(): void
    {
        Solution::factory()->create(['exercise_id' => $this->exercise->id, 'user_id' => $this->user->id]);
        Solution::query()->delete();

        $this->get(route('log.index'))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page
                ->where('items.0.links.0.label', $this->exercise->getFullTitle())
                ->etc());
    }

    public function testDescriptionUsesRussianPluralForms(): void
    {
        app()->setLocale('ru');
        $activity = activity()
            ->withProperties(['chapters' => ['1.1.1', '1.1.2', '1.1.3', '1.1.4', '1.1.5'], 'count' => 5])
            ->log(ActivityService::ACTIVITY_CHAPTER_ADDED);

        $this->assertSame('Добавил 5 глав', ActivityItemData::fromModel(Activity::find($activity->id))->description);
    }

    public function testQueryCountDoesNotDependOnItemCount(): void
    {
        $this->logEveryKind();
        $queriesForOne = $this->countIndexQueries();

        $this->logEveryKind();
        $this->logEveryKind();

        $this->assertSame($queriesForOne, $this->countIndexQueries());
    }

    public function testPaginationKeepsQueryString(): void
    {
        for ($i = 0; $i < 16; $i += 1) {
            activity()->log('unknown');
        }

        $this->get(route('log.index', ['foo' => 'bar']))
            ->assertInertia(fn(Assert $page) => $page
                ->where('pagination.lastPage', 2)
                ->where('pagination.links.2.url', route('log.index', ['foo' => 'bar', 'page' => 2]))
                ->etc());
    }

    /** Свежие записи — первыми, поэтому в ленте порядок обратный. */
    private function logEveryKind(): void
    {
        $service = new ActivityService();

        Solution::factory()->create(['exercise_id' => $this->exercise->id, 'user_id' => $this->user->id]);
        $service->logCompletedExercise($this->user, $this->exercise);
        Comment::factory()->user($this->user)->commentable($this->exercise)->create();
        activity()
            ->causedBy($this->user)
            ->withProperties(['chapters' => ['1.1.1', '1.1.2'], 'count' => 2])
            ->log(ActivityService::ACTIVITY_CHAPTER_ADDED);
        activity()->causedBy($this->user)->log(ActivityService::ACTIVITY_CHAPTER_MEMBER_FINISHED);

        // Записи в одну секунду сортировались бы как попало.
        Activity::all()->each(fn(Activity $activity) => $activity->update([
            'created_at' => now()->addSeconds($activity->id),
        ]));
    }

    private function countIndexQueries(): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('log.index'))->assertOk();
        DB::disableQueryLog();

        return count(DB::getQueryLog());
    }
}
