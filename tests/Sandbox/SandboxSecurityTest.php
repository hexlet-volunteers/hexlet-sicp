<?php

namespace Tests\Sandbox;

use App\Models\Exercise;
use App\Services\SolutionChecker;
use Database\Seeders\ChaptersTableSeeder;
use Database\Seeders\ExercisesTableSeeder;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Проверяет, что код студента исполняется в изолированной песочнице racket/sandbox:
 * нет доступа к сети, запуску процессов, файловой системе, а превышение лимитов
 * по времени/памяти убивает выполнение, а не роняет сам сервис проверки.
 *
 * К каждому вредоносному сниппету дописывается корректное эталонное решение, чтобы
 * модуль скомпилировался и опасная операция реально выполнилась. Если песочница её
 * НЕ заблокирует — тесты упражнения пройдут (exit 0) и проверка `isFailedTests()`
 * упадёт, сигнализируя о регрессии изоляции.
 */
class SandboxSecurityTest extends TestCase
{
    private SolutionChecker $solutionChecker;
    private Exercise $exercise;
    private string $teacherSolution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ChaptersTableSeeder::class);
        $this->seed(ExercisesTableSeeder::class);

        $this->solutionChecker = new SolutionChecker();
        $this->exercise = Exercise::all()
            ->first(fn(Exercise $exercise) => $exercise->hasTests() && $exercise->hasTeacherSolution());
        $this->teacherSolution = $this->exercise->getExerciseTeacherSolution();
    }

    private function attack(string $maliciousCode): string
    {
        return "{$maliciousCode}\n{$this->teacherSolution}";
    }

    /**
     * @return array<string, array{string}>
     */
    public static function maliciousSolutionsProvider(): array
    {
        return [
            'network access' => ['(require racket/tcp) (tcp-connect "example.com" 80)'],
            'system command' => ['(require racket/system) (system "id")'],
            'subprocess spawn' => ['(require racket/system) (subprocess #f #f #f "/bin/ls")'],
            'infinite loop (cpu)' => ['(let loop () (loop))'],
            'memory exhaustion' => ['(define __v (make-vector 1000000000 0)) (vector-ref __v 0)'],
            'read arbitrary file' => ['(require racket/file) (file->string "/etc/passwd")'],
        ];
    }

    #[DataProvider('maliciousSolutionsProvider')]
    public function testMaliciousSolutionIsBlocked(string $maliciousCode): void
    {
        $checkResult = $this->solutionChecker->check($this->exercise, $this->attack($maliciousCode));

        $this->assertTrue(
            $checkResult->isFailedTests(),
            "Вредоносный код должен быть заблокирован песочницей (exit 1), а не уронить чек.\n"
            . "Статус: {$checkResult->getResultStatus()}\nВывод: {$checkResult->getOutput()}"
        );
    }

    public function testFileWriteIsBlockedAndNoFileCreated(): void
    {
        $target = sys_get_temp_dir() . '/sicp-sandbox-pwned-' . uniqid();
        $maliciousCode = sprintf(
            '(call-with-output-file "%s" (lambda (out) (display "pwned" out)) #:exists (quote replace))',
            $target
        );

        $checkResult = $this->solutionChecker->check($this->exercise, $this->attack($maliciousCode));

        $this->assertTrue($checkResult->isFailedTests(), 'Запись файла должна быть заблокирована.');
        $this->assertFileDoesNotExist($target, 'Студент не должен мочь создать файл на диске.');
    }
}
