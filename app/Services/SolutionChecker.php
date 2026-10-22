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

        $hashedUserExerciseSolutionId = hash('sha256', implode('', [time(), $exercise->id]));
        $solutionFile = sprintf('solutions/%s.rkt', $hashedUserExerciseSolutionId);
        $testsFile = sprintf('solutions/%s_tests.rkt', $hashedUserExerciseSolutionId);

        Storage::put($solutionFile, $solutionCode);
        Storage::put($testsFile, $exercise->getExerciseTests());
        // todo необходимо перейти на новые компоненты laravel 10
        // sandbox.rkt читает оба файла как данные и прогоняет тесты в песочнице
        // racket/sandbox, выставляя exit-код. Внешний `timeout` — подстраховка
        // на случай зависания вне eval-лимита песочницы.
        $command = new Command('timeout 20s racket');
        $command->addArg(resource_path('racket/sandbox.rkt'));
        $command->addArg(Storage::path($solutionFile));
        $command->addArg(Storage::path($testsFile));

        $command->execute();

        $exitCode = $command->getExitCode();
        $output = $command->getExecuted()
            ? $command->getOutput()
            : $command->getError();

        return new CheckResultData($exitCode, $output);
    }
}
