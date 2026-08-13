<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

class ExerciseLocaleFilesTest extends TestCase
{
    private const LOCALES = ['en', 'ru'];

    private static function exercisesPath(): string
    {
        return realpath(__DIR__ . '/../..') . '/resources/exercises';
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function localeDirProvider(): array
    {
        $dataset = [];

        foreach (glob(self::exercisesPath() . '/*/') as $exerciseDir) {
            $key = basename($exerciseDir);

            foreach (self::LOCALES as $locale) {
                $label = "{$key} [{$locale}]";

                $dataset[$label] = ["{$exerciseDir}{$locale}", $key, $locale];
            }
        }

        return $dataset;
    }

    #[DataProvider('localeDirProvider')]
    public function testDataYmlHasRequiredFields(
        string $localeDir,
        string $key,
        string $locale
    ): void {
        $ymlPath = "{$localeDir}/data.yml";

        $this->assertFileExists(
            $ymlPath,
            "Missing data.yml for exercise {$key} [{$locale}]"
        );

        $data = Yaml::parseFile($ymlPath);

        $this->assertArrayHasKey(
            'name',
            $data,
            "Missing 'name' field in {$ymlPath}"
        );

        $this->assertNotEmpty(
            $data['name'],
            "Empty 'name' field in {$ymlPath}"
        );
    }

    #[DataProvider('localeDirProvider')]
    public function testDescriptionIsNotEmpty(
        string $localeDir,
        string $key,
        string $locale
    ): void {
        $readmePath = "{$localeDir}/README.md";

        $this->assertFileExists(
            $readmePath,
            "Missing README.md for exercise {$key} [{$locale}]"
        );

        $this->assertNotEmpty(
            trim(file_get_contents($readmePath)),
            "Empty README.md in {$readmePath}"
        );
    }
}
