<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Tests\TestCase;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginControllerTest extends TestCase
{
    protected string $email = 'test@example.com';
    protected string $password = 'password';

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create([
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);
    }

    public function testLoginAndRedirectToPreviousUrlWhenSessionHasPreviousUrl(): void
    {
        $previousUrl = '/ru/chapters/6';

        $response = $this
            ->withSession(['url.intended' => $previousUrl])
            ->post(route('login'), [
                'email' => $this->email,
                'password' => $this->password,
            ]);

        $this->assertAuthenticated();

        $response->assertRedirect($previousUrl);
    }

    public function testLoginAndRedirectToProgressPageWhenNoPreviousUrlInSession(): void
    {
        $progressUrl = route('my.show');

        $response = $this
            ->withSession([])
            ->post(route('login'), [
                'email' => $this->email,
                'password' => $this->password,
            ]);

        $this->assertAuthenticated();

        $response->assertRedirect($progressUrl);
    }

    public function testDevLoginIsNotAvailableOutsideLocal(): void
    {
        $this->withExceptionHandling();

        User::factory()->admin()->create();

        $response = $this->post(route('auth.dev-login'));

        $response->assertNotFound();
        $this->assertGuest();
    }

    public function testDevLoginAuthenticatesAdminInLocal(): void
    {
        // Подмена окружения выключает и пропуск CSRF, завязанный на env=testing,
        // поэтому middleware отключается явно.
        $this->app['env'] = 'local';
        $this->withoutMiddleware(VerifyCsrfToken::class);

        $admin = User::factory()->admin()->create();

        $response = $this->from(route('home'))->post(route('auth.dev-login'));

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($admin);
    }
}
