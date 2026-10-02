<?php

namespace Tests\Feature\Support\Inertia;

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laracasts\Flash\Message;
use Tests\TestCase;

/** Flash доходит при редиректе в обе стороны: Blade ↔ Inertia. */
class FlashBagTest extends TestCase
{
    public function testBladeFlashReachesInertiaPage(): void
    {
        // так пишет flash()->error() в Blade-экшенах
        $this->actingAs(User::factory()->regular()->create())
            ->withSession(['flash_notification' => collect([new Message(['message' => 'Oops', 'level' => 'danger'])])])
            ->get(route('settings.profile.index'))
            ->assertInertia(fn(Assert $page) => $page
                ->where('flash', ['message' => 'Oops', 'level' => 'error'])
                ->etc());
    }

    public function testInertiaFlashReachesBladePage(): void
    {
        // так пишут ->with('success', ...) Inertia-экшены
        $this->withSession(['success' => 'Saved', 'error' => 'Failed'])
            ->get(route('chapters.index'))
            ->assertSeeInOrder(['alert-success', 'Saved', 'alert-danger', 'Failed'], false);
    }

    public function testMessageIsShownOnce(): void
    {
        $this->withSession(['success' => 'Saved'])->get(route('chapters.index'));

        $this->get(route('chapters.index'))->assertDontSee('Saved');
    }
}
