<?php

namespace App\Services;

use App\DTO\CheckResultData;
use App\Models\Exercise;
use Illuminate\Support\Facades\Storage;
use mikehaertl\shellcommand\Command;

class SolutionChecker
{
    public function check(Exercise $exercise, string $solutionCode): CheckResultData
    {
        if (!$exercise->hasTests()) {
            return new CheckResultData(CheckResultData::SUCCESS_EXIT_CODE);
        }

        $tests = $exercise->getExerciseTests();
        $contents = view(
            'exercise.solution_sandbox_wrapper',
            [
                'solution' => $this->prettifyCode($solutionCode),
                'tests' => $this->prettifyCode($tests),
            ]
        )->render();

        $hashedUserExerciseSolutionId = hash('sha256', implode('', [time(), $exercise->id]));
        $userSolutionPath = sprintf('solutions/%s.rkt', $hashedUserExerciseSolutionId);

        Storage::put($userSolutionPath, $contents);
        $solutionPath = Storage::path($userSolutionPath);
        // todo необходимо перейти на новые компоненты laravel 10
        // Файл сам по себе является harness'ом: строит песочницу racket/sandbox,
        // прогоняет тесты внутри неё и выставляет exit-код. Внешний `timeout` —
        // подстраховка на случай зависания вне eval-лимита песочницы.
        $command = new Command("timeout 20s racket {$solutionPath}");

        $command->execute();

        $exitCode = $command->getExitCode();
        $output = $command->getExecuted()
            ? $command->getOutput()
            : $command->getError();

        return new CheckResultData($exitCode, $output);
    }

    private function prettifyCode(string $code): string
    {
        $indent = str_repeat(' ', 28);
        $lines = explode("\n", $code);
        $indentedLines = array_map(fn($line) => "{$indent}$line", $lines);

        return implode("\n", $indentedLines);
    }
}
