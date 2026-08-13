<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->admin()->create();
    }

    public function testExportUsersCsv(): void
    {
        Storage::disk('local')->deleteDirectory('export');

        $user = User::factory()->create([
            'points' => 120,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.export.store'), [
            'type' => 'users',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-disposition');

        $filePath = storage_path('app/export/users.csv');
        $this->assertFileExists($filePath);

        $content = file_get_contents($filePath);
        $lines = explode("\n", trim($content));

        $this->assertEquals(
            ['id', 'points', 'created_at'],
            str_getcsv($lines[0])
        );

        $rows = array_map(str_getcsv(...), array_slice($lines, 1));
        $row = collect($rows)->firstWhere(fn(array $row): bool => (int) $row[0] === $user->id);

        $this->assertNotNull($row);
        $this->assertEquals($user->points, (int) $row[1]);
        $this->assertNotEmpty($row[2]);
    }

    public function testExportInvalidTypeThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->actingAs($this->adminUser)->post(route('admin.export.store'), [
            'type' => 'invalid_type',
        ]);
    }

    public function testStoreAsGuestDenied(): void
    {
        $this->withExceptionHandling();

        $response = $this->post(route('admin.export.store'), [
            'type' => 'users',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function testStoreAsRegularUserDenied(): void
    {
        $this->withExceptionHandling();

        $response = $this->actingAs(User::factory()->regular()->create())
            ->post(route('admin.export.store'), [
                'type' => 'users',
            ]);

        $response->assertStatus(403);
    }

    public function testIndexAsGuestDenied(): void
    {
        $this->withExceptionHandling();

        $response = $this->get(route('admin.export.index'));

        $response->assertRedirect(route('login'));
    }

    public function testIndexAsRegularUserDenied(): void
    {
        $this->withExceptionHandling();

        $response = $this->actingAs(User::factory()->regular()->create())
            ->get(route('admin.export.index'));

        $response->assertStatus(403);
    }

    public function testIndexAsAdmin(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.export.index'));

        $response->assertOk();
    }
}
