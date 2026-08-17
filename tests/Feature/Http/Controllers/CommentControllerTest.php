<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Chapter;
use App\Models\Comment;
use App\Models\Exercise;
use App\Models\User;
use Database\Seeders\ChaptersTableSeeder;
use Database\Seeders\ExercisesTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\ControllerTestCase;

class CommentControllerTest extends ControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            ChaptersTableSeeder::class,
            ExercisesTableSeeder::class,
            UsersTableSeeder::class,
        ]);

        $this->actingAs($this->user);
    }

    public function testIndex(): void
    {
        $response = $this->get(route('comments.index'));

        $response->assertOk();
    }

    #[DataProvider('dataCommentable')]
    public function testShow(string $commentableClass): void
    {
        /** @var Exercise|Chapter $commentableClass */
        $commentable = $commentableClass::first();
        $this->createComment($this->user, $commentable);

        $route = $this->getModelActionRoute('show', $commentable);

        $response = $this->get($route);
        $response->assertOk();
    }

    #[DataProvider('dataCommentable')]
    public function testStore(string $commentableClass): void
    {
        /** @var Exercise|Chapter $commentableClass */
        $commentable = $commentableClass::first();
        $user = $this->user;

        $commentData = [
            'content' => $this->faker->text,
            'user_id' => $user->id,
            'commentable_id' => $commentable->id,
            'commentable_type' => $commentable::class,
        ];
        $response = $this->post(route('comments.store'), $commentData);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('comments', $commentData);
    }

    #[DataProvider('dataCommentable')]
    public function testUpdate(string $commentableClass): void
    {
        /** @var Exercise|Chapter $commentableClass */
        $commentable = $commentableClass::first();

        $comment = $this->createComment($this->user, $commentable);

        $commentData = [
            'content' => $this->faker->text,
            'user_id' => $this->user->id,
            'commentable_id' => $commentable->id,
            'commentable_type' => $commentable::class,
        ];
        $response = $this->put(
            route('comments.update', ['comment' => $comment]),
            $commentData
        );

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('comments', array_merge($commentData, ['id' => $comment->id]));

        $this->assertDatabaseHas('activity_log', [
            'properties->comment->content' => $commentData['content'],
        ]);
    }

    #[DataProvider('dataCommentable')]
    public function testDestroy(string $commentableClass): void
    {
        /** @var Exercise|Chapter $commentableClass */
        $commentable = $commentableClass::first();

        $comment = $this->createComment($this->user, $commentable);
        $commentData = $comment->only('id', 'user_id', 'content', 'deleted_at');

        $response = $this->delete(
            route('comments.destroy', compact('comment'))
        );

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirect();

        $this->assertDatabaseMissing('comments', $commentData);
    }

    public static function dataCommentable(): array
    {
        return [
            'test with chapter'  => [Chapter::class],
            'test with exercise' => [Exercise::class],
        ];
    }

    private function getModelActionRoute(string $action, Model $model): string
    {
        $routesGroup = $model->getTable();
        return route("{$routesGroup}.{$action}", [
            Str::singular($routesGroup) => $model,
        ]);
    }

    private function createComment(User $user, Model $commentable): Comment
    {
        return Comment::factory()->create([
            'user_id' => $user,
            'commentable_id' => $commentable,
            'commentable_type' => $commentable::class,
        ]);
    }
}
