<?php

namespace Tests\Feature\Http\Controllers\Api\Exercise;

use App\DTO\CheckResultData;
use App\Models\Exercise;
use App\Models\User;
use Database\Seeders\ChaptersTableSeeder;
use Database\Seeders\ExercisesTableSeeder;
use Tests\ControllerTestCase;

class CheckControllerTest extends ControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->seed([
            ChaptersTableSeeder::class,
            ExercisesTableSeeder::class,
        ]);
    }

    public function testCheck(): void
    {
        $exercise = Exercise::wherePath('1.3')->first();
        $path = route('api.exercises.check.store', [$exercise]);

        $underscoredPath = $exercise->present()->underscorePath;
        $solutionCode = view("exercise.solution_stub.{$underscoredPath}_solution")->render();

        $data = [
            'solution_code' => $solutionCode,
        ];
        $response = $this->actingAs($this->user)->postJson($path, $data);

        $response->assertCreated();

        $responseBody = $response->decodeResponseJson();

        $this->assertTrue(data_get($responseBody, 'check_result.exit_code') === CheckResultData::SUCCESS_EXIT_CODE);

        $this->assertDatabaseHas('activity_log', [
            'causer_id' => $this->user->id,
            'subject_id' => $exercise->id,
            'subject_type' => $exercise::class,
        ]);
        $this->assertDatabaseHas('exercise_members', [
            'user_id' => $this->user->id,
            'exercise_id' => $exercise->id,
        ]);
    }

    public function testCheckByGuest(): void
    {
        $exercise = Exercise::wherePath('1.3')->first();
        $path = route('api.exercises.check.store', [$exercise]);

        $underscoredPath = $exercise->present()->underscorePath;
        $solutionCode = view("exercise.solution_stub.{$underscoredPath}_solution")->render();

        $data = [
            'user_id' => null,
            'solution_code' => $solutionCode,
        ];
        $response = $this->postJson($path, $data);

        $response->assertCreated();
        $responseBody = $response->decodeResponseJson();

        $this->assertTrue(data_get($responseBody, 'check_result.exit_code') === CheckResultData::SUCCESS_EXIT_CODE);
    }

    public function testGuestCannotCheckOnBehalfOfUser(): void
    {
        $exercise = Exercise::wherePath('1.3')->first();
        $path = route('api.exercises.check.store', [$exercise]);
        $otherUser = User::factory()->create();

        $underscoredPath = $exercise->present()->underscorePath;
        $solutionCode = view("exercise.solution_stub.{$underscoredPath}_solution")->render();

        $data = [
            'user_id' => $otherUser->id,
            'solution_code' => $solutionCode,
        ];
        $response = $this->postJson($path, $data);

        $response->assertCreated();
        $this->assertDatabaseMissing('exercise_members', [
            'user_id' => $otherUser->id,
            'exercise_id' => $exercise->id,
        ]);
    }
}
