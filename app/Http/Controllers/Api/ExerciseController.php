<?php

namespace App\Http\Controllers\Api;

use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class ExerciseController extends Controller
{
    /**
     * Get exercise
     */
    public function show(Exercise $exercise): JsonResponse
    {
        $hasTests = $exercise->hasTests();
        $testCode = $hasTests
            ? $exercise->getExerciseTests()
            : '';
        $hasTeacherSolution = $exercise->hasTeacherSolution();
        $teacherSolutionCode = $hasTeacherSolution
            ? $exercise->getExerciseTeacherSolution()
            : '';
        return response()->json([
            'exercise' => [
                /** @var int */
                'id' => $exercise->id,
                'prepared_code' => '',
                'original_code' => '',
                'test_code' => $testCode,
                /** @var bool */
                'has_tests' => $hasTests,
                /** @var bool */
                'has_teacher_solution' => $hasTeacherSolution,
                'teacher_solution_code' => $teacherSolutionCode,
            ],
        ]);
    }
}
