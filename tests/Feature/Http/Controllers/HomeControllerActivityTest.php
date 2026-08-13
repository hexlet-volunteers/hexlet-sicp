<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Exercise;
use App\Services\ActivityService;
use Database\Seeders\ChaptersTableSeeder;
use Database\Seeders\ExercisesTableSeeder;
use Tests\ControllerTestCase;

class HomeControllerActivityTest extends ControllerTestCase
{
    public function testHomeRendersWithCompletedExerciseActivity(): void
    {
        $this->seed([
            ChaptersTableSeeder::class,
            ExercisesTableSeeder::class,
        ]);

        $exercise = Exercise::first();

        $activityService = new ActivityService();
        $activityService->logCompletedExercise($this->user, $exercise);

        $response = $this->actingAs($this->user)->get(route('home'));

        $response->assertOk();
    }
}
