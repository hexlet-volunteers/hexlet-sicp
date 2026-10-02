<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Models\Chapter;
use App\Models\Comment;
use App\Models\User;
use Database\Seeders\ChaptersTableSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\ControllerTestCase;

class CommentControllerTest extends ControllerTestCase
{
    protected User $adminUser;
    protected User $regularUser;
    protected Chapter $chapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ChaptersTableSeeder::class);
        $this->chapter = Chapter::firstOrFail();
        $this->adminUser = User::factory()->admin()->create();
        $this->regularUser = User::factory()->regular()->create();
    }

    public function testIndexAsAdmin(): void
    {
        $this->actingAs($this->adminUser);
        $comment = Comment::factory()
            ->user($this->regularUser)
            ->commentable($this->chapter)
            ->create(['content' => '**bold** <script>alert(1)</script>' . str_repeat('x', 200)]);

        $this->get(route('admin.comments.index'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertInertia(fn(Assert $page) => $page
                ->component('Admin/Comment/Index')
                ->where('pagination.total', 1)
                ->where('filterUrl', route('admin.comments.index'))
                ->where('filter', ['name' => null, 'email' => null])
                ->has('menu', 4)
                ->where('menu.1.active', true)
                ->has('items.0', fn(Assert $item) => $item
                    ->where('id', $comment->id)
                    ->where('userName', $this->regularUser->name)
                    ->where('userUrl', route('users.show', $this->regularUser))
                    ->where('commentableName', $comment->getCommentableName())
                    ->where('commentableUrl', route('chapters.show', $this->chapter))
                    ->where('contentHtml', fn(string $html) => str_contains($html, '<strong>bold</strong>')
                        && !str_contains($html, '<script>')
                        && mb_strlen(strip_tags($html)) < 110)
                    ->where('url', route('comments.show', $comment))
                    ->where('createdAt', $comment->created_at->format('d.m.Y H:i'))));
    }

    public function testIndexFiltersByAuthorName(): void
    {
        $this->actingAs($this->adminUser);
        $author = User::factory()->create(['name' => 'Zebediah Quux']);
        $match = Comment::factory()->user($author)->commentable($this->chapter)->create();
        Comment::factory()->user($this->regularUser)->commentable($this->chapter)->create();
        $filter = ['filter' => ['name' => 'ebediah q']];

        $this->get(route('admin.comments.index', $filter))
            ->assertInertia(fn(Assert $page) => $page
                ->has('items', 1)
                ->where('items.0.id', $match->id)
                ->where('filter.name', 'ebediah q')
                ->where('menu.2.href', route('admin.solutions.index', $filter))
                ->etc());
    }

    public function testPaginationKeepsFilter(): void
    {
        $this->actingAs($this->adminUser);
        $author = User::factory()->create(['email' => 'author@paged.test']);
        Comment::factory()->count(51)->user($author)->commentable($this->chapter)->create();
        Comment::factory()->user($this->regularUser)->commentable($this->chapter)->create();
        $filter = ['filter' => ['email' => '@paged.test']];

        $this->get(route('admin.comments.index', $filter))
            ->assertInertia(fn(Assert $page) => $page
                ->where('pagination.total', 51)
                ->where('pagination.lastPage', 2)
                ->where('pagination.links.2.url', route('admin.comments.index', [...$filter, 'page' => 2]))
                ->etc());
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
